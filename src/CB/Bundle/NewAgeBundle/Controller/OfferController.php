<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 14/Nov/16
 * Time: 14:51
 */
namespace CB\Bundle\NewAgeBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use Oro\Bundle\ImportExportBundle\Formatter\FormatterProvider;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;

use CB\Bundle\NewAgeBundle\Entity\Offer;
use CB\Bundle\NewAgeBundle\Entity\OfferItem;
use CB\Bundle\NewAgeBundle\Entity\PanelView;
use CB\Bundle\NewAgeBundle\Entity\Repository\PanelViewRepository;
use CB\Bundle\NewAgeBundle\Entity\Repository\OfferRepository;
use CB\Bundle\NewAgeBundle\Entity\Repository\ReservationItemRepository;

use CB\Bundle\NewAgeBundle\Entity\ReservationItem;
use CB\Bundle\SchedulerBundle\Entity\SchedulerEvent;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use WebDriver\Exception;

/**
 * @Route("/offer")
 */
class OfferController extends Controller
{
    const EXPORT_BATCH_SIZE = 200;

    /**
     * @Route("/index", name="cb_newage_offer_index")
     * @Template()
     * @AclAncestor("cb_newage_offer_view")
     */
    public function indexAction()
    {
        return array(
            'entity_class' => $this->container->getParameter('cb_newage_offer.entity.class')
        );
    }

    /**
     * @Route("/view/{id}", name="cb_newage_offer_view", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("cb_newage_offer_view")
     *
     * @param Offer $offer
     *
     * @return array
     */
    public function viewAction(Offer $offer)
    {
        return [
            'entity' => $offer
        ];
    }

    /**
     * @Route("/unconfirmed", name="cb_newage_offer_unconfirmed")
     * @Template()
     * @AclAncestor("cb_newage_offer_view")
     */
    public function unconfirmedAction()
    {
        return array(
            'entity_class' => $this->container->getParameter('cb_newage_offer.entity.class')
        );
    }

    /**
     * @Route("/create", name="cb_newage_offer_create")
     * @AclAncestor("cb_newage_offer_create")
     * @Template("CBNewAgeBundle:Offer:update.html.twig")
     *
     * @return array
     */
    public function createAction()
    {
        $offer = $this->get('cb_newage_offer.manager')->createOffer();

        return $this->update($offer);
    }

    /**
     * @Route("/update/{id}", name="cb_newage_offer_update", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("cb_newage_offer_update")
     *
     * @param Offer $offer
     *
     * @return array
     */
    public function updateAction(Offer $offer)
    {
        return $this->update($offer);
    }

    /**
     * @Route("/select/{id}", name="cb_newage_offer_select", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("cb_newage_offer_select")
     *
     * @param Offer $offer
     * @return array
     */
    public function selectAction(Offer $offer)
    {
        return [
            'entity' => $offer
        ];
    }


    /**
     * @param Offer $offer
     * @return array
     */
    protected function update(Offer $offer)
    {
        if ($this->get('cb_newage_offer.form.handler.entity')->process($offer)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('cb.newage.offer.message.saved')
            );

            return $this->get('oro_ui.router')->redirectAfterSave(
                ['route' => 'cb_newage_offer_update', 'parameters' => ['id' => $offer->getId()]],
                ['route' => 'cb_newage_offer_view', 'parameters' => ['id' => $offer->getId()]]
            );
        }

        return array(
            'entity' => $offer,
            'form' => $this->get('cb_newage_offer.form.entity')->createView()
        );
    }

    /**
     * @Route("/{gridName}/offerMassAction/{actionName}", name="cb_offer_massaction")
     * @AclAncestor("cb_newage_offer_create")
     * @Template("CBNewAgeBundle:Offer:view.html.twig")
     *
     * @param string $gridName
     * @param string $actionName
     *
     * @return RedirectResponse
     *
     * @throws \Exception
     */
    public function offerMassActionAction($gridName, $actionName)
    {
        /** @var MassActionDispatcher $massActionDispatcher */
        $massActionDispatcher = $this->get('oro_datagrid.mass_action.dispatcher');

        $response = $massActionDispatcher->dispatchByRequest($gridName, $actionName, $this->container->get('request_stack')->getCurrentRequest());

        $offer = $response->getOption('offer');
        $isAllSelected = $response->getOption('isAllSelected');
        $values = $response->getOption('values');
        $filters = $response->getOption('filters');

        /** A trebuit sa folosesc native sql pentru ca $querybuilder intorcea doar o parte din inregistrari */
        /** @var \Doctrine\DBAL\Connection $conn */
        $conn = $this->getDoctrine()->getManager()->getConnection();
        $query = '
              SELECT 
                  pv.id AS id
              FROM 
                  cb_newage_panel_view pv 
              LEFT JOIN 
                  cb_newage_panel p ON p.id=pv.panel_id
              LEFT JOIN 
                  cb_newage_panel_address a ON a.owner_id = p.id AND (a.is_primary = 1)
        ';

        $hasWhere = false;

        if ($isAllSelected) {
            /** @var PanelViewRepository $panelViewRepository */
            $panelViewRepository = $this->getDoctrine()->getRepository('CBNewAgeBundle:PanelView');
            $forbiddenPanelViewIds = $panelViewRepository->getForbiddenPanelViewIds($offer->getStart(), $offer->getEnd());

            $offeredPanelViewIds = [];
            /** @var OfferItem $offerItem */
            foreach ($offer->getOfferItems() as $offerItem) {
                $offeredPanelViewIds[] = $offerItem->getPanelView()->getId();
            }

            $combinedForbiddenPanelViewIds = array_merge($forbiddenPanelViewIds, $offeredPanelViewIds);
            $forbiddenPanelViewIds = array_unique($combinedForbiddenPanelViewIds);

            if (count($forbiddenPanelViewIds) > 0) {
                $query .= ' WHERE pv.id NOT IN (' . implode(",", $forbiddenPanelViewIds) . ')';
                $hasWhere = true;
            }

            if (isset($filters['city']['value'])) {
                if ($hasWhere) {
                    $query .= ' AND';
                } else {
                    $query .= ' WHERE';
                    $hasWhere = true;
                }
                $query .= ' a.city_id IN (' . implode(",", $filters['city']['value']) . ')';
            }

            if (isset($filters['support']['value'])) {
                if ($hasWhere) {
                    $query .= ' AND';
                } else {
                    $query .= ' WHERE';
                    $hasWhere = true;
                }
                $query .= ' p.support_type_id IN (' . implode(",", $filters['support']['value']) . ')';
            }

            if (isset($filters['lighting']['value'])) {
                if ($hasWhere) {
                    $query .= ' AND';
                } else {
                    $query .= ' WHERE';
                    $hasWhere = true;
                }
                $query .= ' p.lighting_type_id IN (' . implode(",", $filters['lighting']['value']) . ')';
            }


            if (isset($filters['dimensions']['value'])) {
                if ($hasWhere) {
                    $query .= ' AND';
                } else {
                    $query .= ' WHERE';
                }
                $query .= ' p.dimensions LIKE \'%' . $filters['dimensions']['value'] . '%\'';
            }
        } else {
            if ($hasWhere) {
                $query .= ' AND';
            } else {
                $query .= ' WHERE';
            }

            $query .= ' pv.id IN (' . $values . ')';
        }
        /** @var \Doctrine\DBAL\Driver\Statement $stmt */
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $em = $this->getDoctrine()->getManager();
        $em->beginTransaction();

        try {
            $entitiesCount = 0;

            /** @var PanelView $panelView */
            foreach ($results as $row) {
                $panelView = $this->getDoctrine()->getRepository('CBNewAgeBundle:PanelView')->findOneBy(['id' => $row['id']]);

                $confirmedEvents = $panelView->getConfirmedEvents($offer->getStart(), $offer->getEnd());
                if (count($confirmedEvents) > 0) { // Daca are evenimente confirmate, cautam intervale libere
                    $freeIntervals = $panelView->getFreeIntervals(
                        $confirmedEvents,
                        $offer->getStart(),
                        $offer->getEnd()
                    );

                    /** @var array $freeInterval */
                    foreach ($freeIntervals as $freeInterval) {
                        $interval = $freeInterval['end']->diff($freeInterval['start']);

                        /** @var \DateInterval $interval */
                        if ($interval->format('%a') >= 7) {
                            $offerItem = new OfferItem();

                            $offerItem->setOffer($offer);
                            $offerItem->setPanelView($panelView);
                            $offerItem->setStart($freeInterval['start']);
                            $offerItem->setEnd($freeInterval['end']);
                            $offerItem->setOwner($this->get('oro_security.security_facade')->getLoggedUser());
                            $offerItem->setOrganization($this->get('oro_security.security_facade')->getOrganization());

                            $em->persist($offerItem);
                            $entitiesCount++;
                        }
                    }
                } else { // In caz contrar fata este libera pentru toata perioada ofertei
                    $offerItem = new OfferItem();

                    $offerItem->setOffer($offer);
                    $offerItem->setPanelView($panelView);
                    $offerItem->setStart($offer->getStart());
                    $offerItem->setEnd($offer->getEnd());
                    $offerItem->setOwner($this->get('oro_security.security_facade')->getLoggedUser());
                    $offerItem->setOrganization($this->get('oro_security.security_facade')->getOrganization());

                    $em->persist($offerItem);
                    $entitiesCount++;
                }
            }

            $em->flush();
            $em->commit();

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->transChoice(
                    'cb.newage.offer.message.items.offered',
                    $entitiesCount,
                    ['%count%' => $entitiesCount]
                )
            );
        } catch (\Exception $e) {
            $em->rollback();
            throw $e;
        }

        return $this->redirect($this->get('router')->generate('cb_newage_offer_view', ['id' => $offer->getId()]));
    }

    /**
     * @Route("/{gridName}/reserveMassAction/{actionName}", name="cb_reserve_massaction")
     * @AclAncestor("cb_newage_offer_create")
     * @Template("CBNewAgeBundle:Offer:view.html.twig")
     *
     * @param string $gridName
     * @param string $actionName
     *
     * @return RedirectResponse
     *
     * @throws \Exception
     */
    public function reserveMassActionAction($gridName, $actionName)
    {
        /** @var MassActionDispatcher $massActionDispatcher */
        $massActionDispatcher = $this->get('oro_datagrid.mass_action.dispatcher');

        $response = $massActionDispatcher->dispatchByRequest($gridName, $actionName, $this->container->get('request_stack')->getCurrentRequest());

        /** @var Offer $offer */
        $offer = $response->getOption('offer');
        $isAllSelected = $response->getOption('isAllSelected');
        $values = $response->getOption('values');
        $filters = $response->getOption('filters');

        $em = $this->getDoctrine()->getManager();
        $em->beginTransaction();

        /** @var PanelViewRepository $panelViewRepository */
        $panelViewRepository = $this->getDoctrine()->getRepository('CBNewAgeBundle:PanelView');

        try {
            $entitiesCount = 0;

            /** @var OfferItem $offerItem */
            foreach ($offer->getOfferItems() as $offerItem) {
                $forbiddenPanelViewIds = $panelViewRepository->getForbiddenPanelViewIds($offerItem->getStart(), $offerItem->getEnd());

                $panelView = $offerItem->getPanelView();

                // Verificam daca fata se gaseste printre cele confirmate
                if (in_array($panelView->getId(), $forbiddenPanelViewIds)) {
                    continue;
                }

                if ($isAllSelected) { // toate mai putin cele care nu satisfac filtrele
                    if (isset($filters['city']['value'])) {
                        if (!in_array($panelView->getPanel()->getAddresses()->first()->getCity()->getId(), $filters['city']['value'])) {
                            continue;
                        }
                    }

                    if (isset($filters['support']['value'])) {
                        if (!in_array($panelView->getPanel()->getSupportType()->getId(), $filters['support']['value']))
                            continue;
                    }

                    if (isset($filters['lighting']['value'])) {
                        if (!in_array($panelView->getPanel()->getLightingType()->getId(), $filters['lighting']['value']))
                            continue;
                    }

                    if (isset($filters['dimensions']['value'])) {
                        if (stripos($panelView->getPanel()->getDimensions(), $filters['dimensions']['value']) === false) {
                            continue;
                        }
                    }
                } else { // doar ce a fost selectat
                    if (!in_array($offerItem->getId(), explode(',', $values))) { // daca NU se regaseste printre cele selectate
                        continue;
                    }
                }

                if (count($offerItem->getReservationItems()) == 0) { // daca nu are rezervari - nu a mai fost adaugat.
                    $confirmedEvents = $panelView->getConfirmedEvents($offerItem->getStart(), $offerItem->getEnd());
                    if (count($confirmedEvents) > 0) { // Daca are evenimente confirmate, cautam intervale libere
                        $freeIntervals = $panelView->getFreeIntervals(
                            $confirmedEvents,
                            $offer->getStart(),
                            $offer->getEnd()
                        );

                        /** @var array $freeInterval */
                        foreach ($freeIntervals as $freeInterval) {
                            $interval = $freeInterval['end']->diff($freeInterval['start']);

                            /** @var \DateInterval $interval */
                            if ($interval->format('%a') >= 7) {
                                $item = $this->createReservationItemObject($offerItem, $freeInterval['start'], $freeInterval['end']);

                                $em->persist($item);
                                $entitiesCount++;
                            }
                        }
                    } else { // In caz contrar fata este libera pentru toata perioada ofertei
                        $item = $this->createReservationItemObject($offerItem);
                        $em->persist($item);
                        $entitiesCount++;
                    }
                }
            }

            if ($entitiesCount > 0) {
                $em->flush();
                $em->commit();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->transChoice(
                        'cb.newage.offer.message.items.reserved',
                        $entitiesCount,
                        ['%count%' => $entitiesCount]
                    )
                );
            } else {
                $this->get('session')->getFlashBag()->add(
                    'warning',
                    $this->get('translator')->transChoice(
                        'No Panel Views where added.',
                        $entitiesCount,
                        ['%count%' => $entitiesCount]
                    )
                );

                $em->rollback();
            }
        } catch (\Exception $e) {
            $em->rollback();
            throw $e;
        }

        return $this->redirect($this->get('router')->generate('cb_newage_offer_view', ['id' => $offer->getId()]));
    }

    /**
     * @Route("/{gridName}/confirmMassAction/{actionName}", name="cb_confirm_massaction")
     * @AclAncestor("cb_newage_offer_create")
     * @Template("CBNewAgeBundle:Offer:view.html.twig")
     *
     * @param string $gridName
     * @param string $actionName
     *
     * @return RedirectResponse
     * @throws \Exception
     */
    public function confirmMassActionAction($gridName, $actionName)
    {
        /** @var MassActionDispatcher $massActionDispatcher */
        $massActionDispatcher = $this->get('oro_datagrid.mass_action.dispatcher');

        $response = $massActionDispatcher->dispatchByRequest($gridName, $actionName, $this->container->get('request_stack')->getCurrentRequest());

        /** @var Offer $offer */
        $offer = $response->getOption('offer');
        $isAllSelected = $response->getOption('isAllSelected');
        $values = $response->getOption('values');
        $filters = $response->getOption('filters');

        $em = $this->getDoctrine()->getManager();
        $em->beginTransaction();

        /** @var PanelViewRepository $panelViewRepository */
        $panelViewRepository = $this->getDoctrine()->getRepository('CBNewAgeBundle:PanelView');

        $affectedOffers = [];
        try {
            $entitiesCount = 0;

            /** @var OfferItem $offerItem */
            foreach ($offer->getOfferItems() as $offerItem) {
                $panelView = $offerItem->getPanelView();

                /** @var ReservationItem $reservationItem */
                foreach ($offerItem->getReservationItems() as $reservationItem) {
                    $forbiddenPanelViewIds = $panelViewRepository->getForbiddenPanelViewIds($reservationItem->getStart(), $reservationItem->getEnd());

                    // Verificam daca fata se gaseste printre cele confirmate
                    if (in_array($panelView->getId(), $forbiddenPanelViewIds)) {
                        continue;
                    }

                    if ($isAllSelected) { // toate mai putin cele care nu satisfac filtrele
                        if (isset($filters['city']['value'])) {
                            if (!in_array($panelView->getPanel()->getAddresses()->first()->getCity()->getId(), $filters['city']['value'])) {
                                continue;
                            }
                        }

                        if (isset($filters['support']['value'])) {
                            if (!in_array($panelView->getPanel()->getSupportType()->getId(), $filters['support']['value']))
                                continue;
                        }

                        if (isset($filters['lighting']['value'])) {
                            if (!in_array($panelView->getPanel()->getLightingType()->getId(), $filters['lighting']['value']))
                                continue;
                        }

                        if (isset($filters['dimensions']['value'])) {
                            if (stripos($panelView->getPanel()->getDimensions(), $filters['dimensions']['value']) === false) {
                                continue;
                            }
                        }
                    } else { // doar ce a fost selectat

                        if (!in_array($reservationItem->getId(), explode(',', $values))) { // daca NU se regaseste printre cele selectate
                            continue;
                        }
                    }

                    if (count($reservationItem->getEvents()) > 0) { // daca are evenimente le stergem pentru a adauga altele noi
                        $reservationItem->removeAllEvents();
                    }

                    $confirmedEvents = $panelView->getConfirmedEvents($reservationItem->getStart(), $reservationItem->getEnd());
                    if (count($confirmedEvents) > 0) { // Daca are evenimente confirmate, cautam intervale libere
                        $freeIntervals = $panelView->getFreeIntervals(
                            $confirmedEvents,
                            $reservationItem->getStart(),
                            $reservationItem->getEnd()
                        );

                        /** @var array $freeInterval */
                        foreach ($freeIntervals as $freeInterval) {
                            /** @var \DateInterval $interval */
                            $interval = $freeInterval['end']->diff($freeInterval['start']);
                            if ($interval->format('%a') >= 7) {
                                $event = $this->newEvent($reservationItem,
                                    $freeInterval['start'],
                                    $freeInterval['end']
                                );
                                $reservationItem->addEvent($event);

                                $em->persist($event);
                                $entitiesCount++;

                                /**
                                 * Cautam rezervari in aceeasi perioda cu intervalul.
                                 */
                                $aff = $this->processOverlapReservations($em, $reservationItem, $panelView, $freeInterval['start'], $freeInterval['end']);
                                $affectedOffers[] = ['panel_view' => $panelView, 'offers' => $aff];
                            }
                        }
                    } else { // In caz contrar fata este libera pentru toata perioada ofertei
                        $event = $this->newEvent($reservationItem,
                            $reservationItem->getStart(),
                            $reservationItem->getEnd()
                        );
                        $reservationItem->addEvent($event);

                        $em->persist($event);
                        $entitiesCount++;

                        /**
                         * Cautam rezervari in aceeasi perioda cu intervalul.
                         */
                        $aff = $this->processOverlapReservations($em, $reservationItem, $panelView, clone $reservationItem->getStart(), clone  $reservationItem->getEnd());
                        $affectedOffers[] = ['panel_view' => $panelView, 'offers' => $aff];
                    }
                }
            }

            if ($entitiesCount > 0) {
                $em->flush();
                $em->commit();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->transChoice(
                        'cb.newage.offer.message.items.confirmed',
                        $entitiesCount,
                        ['%count%' => $entitiesCount]
                    )
                );

                $results = [];
                foreach ($affectedOffers as $item) {
                    $currentPanelView = $item['panel_view'];
                    $currentOffers = $item['offers'];

                    /** @var Offer $currentOffer */
                    foreach ($currentOffers as $currentOffer)
                        $results[$currentOffer->getId()][] = $currentPanelView;
                }

                foreach ($results as $affectedOfferId => $affectedPanelViews) {
                    $affectedOffer = $this->getDoctrine()->getRepository('CBNewAgeBundle:Offer')->findOneBy(['id' => $affectedOfferId]);

                    try {
                        $this->get('cb_newage.mailer.processor')->sendReservationChangeEmail($offer, $affectedOffer, $affectedPanelViews);
                    } catch (\Exception $e) {
                        throw $e;
                    }
                }
            } else {
                $this->get('session')->getFlashBag()->add(
                    'warning',
                    $this->get('translator')->transChoice(
                        'No Panel Views where added.',
                        $entitiesCount,
                        ['%count%' => $entitiesCount]
                    )
                );

                $em->rollback();
            }
        } catch (\Exception $e) {
            $em->rollback();
            throw $e;
        }

        return $this->redirect($this->get('router')->generate('cb_newage_offer_view', ['id' => $offer->getId()]));
    }

    /**
     * @Route("/{gridName}/cancelMassAction/{actionName}", name="cb_cancel_massaction")
     * @AclAncestor("cb_newage_offer_update")
     * @Template("CBNewAgeBundle:Offer:view.html.twig")
     *
     * @param string $gridName
     * @param string $actionName
     *
     * @return RedirectResponse
     * @throws \Exception
     */
    public function cancelMassActionAction($gridName, $actionName)
    {
        /** @var MassActionDispatcher $massActionDispatcher */
        $massActionDispatcher = $this->get('oro_datagrid.mass_action.dispatcher');

        $response = $massActionDispatcher->dispatchByRequest($gridName, $actionName, $this->container->get('request_stack')->getCurrentRequest());

        /** @var Offer $offer */
        $offer = $response->getOption('offer');
        $isAllSelected = $response->getOption('isAllSelected');
        $values = $response->getOption('values');
        $filters = $response->getOption('filters');

        $em = $this->getDoctrine()->getManager();
        $em->beginTransaction();

        error_log("Intra in functie.\n", 3, '/var/www/html/newage/app/logs/catalin');
        try {
            $entitiesCount = 0;

            /** @var OfferItem $offerItem */
            foreach ($offer->getOfferItems() as $offerItem) {
                $panelView = $offerItem->getPanelView();

                error_log("Panel view: " . $panelView->getId() . "\n", 3, '/var/www/html/newage/app/logs/catalin');

                /** @var ReservationItem $reservationItem */
                foreach ($offerItem->getReservationItems() as $reservationItem) {
                    error_log("Reservation item: " . $reservationItem->getId() . "\n", 3, '/var/www/html/newage/app/logs/catalin');

                    /** @var SchedulerEvent $event */
                    foreach ($reservationItem->getEvents() as $event) {
                        error_log("Event: " . $event->getId() . "\n", 3, '/var/www/html/newage/app/logs/catalin');
                        if ($isAllSelected) { // toate mai putin cele care nu satisfac filtrele
                            if (isset($filters['city']['value'])) {
                                if (!in_array($panelView->getPanel()->getAddresses()->first()->getCity()->getId(), $filters['city']['value'])) {
                                    continue;
                                }
                            }

                            if (isset($filters['support']['value'])) {
                                if (!in_array($panelView->getPanel()->getSupportType()->getId(), $filters['support']['value']))
                                    continue;
                            }

                            if (isset($filters['lighting']['value'])) {
                                if (!in_array($panelView->getPanel()->getLightingType()->getId(), $filters['lighting']['value']))
                                    continue;
                            }

                            if (isset($filters['dimensions']['value'])) {
                                if (stripos($panelView->getPanel()->getDimensions(), $filters['dimensions']['value']) === false) {
                                    continue;
                                }
                            }
                        } else { // doar ce a fost selectat

                            if (!in_array($event->getId(), explode(',', $values))) { // daca NU se regaseste printre cele selectate
                                continue;
                            }
                        }

                        $event->setStatus(SchedulerEvent::RESERVED);
                        $entitiesCount++;
                    }
                }
            }

            if ($entitiesCount > 0) {
                $em->flush();
                $em->commit();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    $this->get('translator')->transChoice(
                        'cb.newage.offer.message.items.canceled',
                        $entitiesCount,
                        ['%count%' => $entitiesCount]
                    )
                );
            } else {
                $this->get('session')->getFlashBag()->add(
                    'warning',
                    $this->get('translator')->transChoice(
                        'No Panel Views where added.',
                        $entitiesCount,
                        ['%count%' => $entitiesCount]
                    )
                );

                $em->rollback();
            }
        } catch (\Exception $e) {
            $em->rollback();
            throw $e;
        }

        return $this->redirect($this->get('router')->generate('cb_newage_offer_view', ['id' => $offer->getId()]));
    }


    /**
     * @Route("/{gridName}/removeMassAction/{actionName}", name="cb_remove_mass_action")
     * @AclAncestor("cb_newage_offer_view")
     * @Template("CBNewAgeBundle:Offer:view.html.twig")
     *
     * @param string $gridName
     * @param string $actionName
     *
     * @return RedirectResponse
     * @throws \Exception
     */
    public function removeMassActionAction($gridName, $actionName) {
        /** @var MassActionDispatcher $massActionDispatcher */
        $massActionDispatcher = $this->get('oro_datagrid.mass_action.dispatcher');

        $response = $massActionDispatcher->dispatchByRequest($gridName, $actionName, $this->container->get('request_stack')->getCurrentRequest());

        /** @var Offer $offer */
        $offer = $response->getOption('offer');
        $count = $response->getOption('count');

        if ($count>0) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->transChoice(
                    'cb.newage.offer.message.items.deleted',
                    $count,
                    ['%count%' => $count]
                )
            );
        }

        return $this->redirect($this->get('router')->generate('cb_newage_offer_view', ['id' => $offer->getId()]));
    }

    /**
     * @param ReservationItem $reservationItem
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * @return SchedulerEvent
     */
    private function newEvent(ReservationItem $reservationItem, \DateTime $start, \DateTime $end)
    {
        $event = new SchedulerEvent();

        $event->setReservationItem($reservationItem);
        $event->setStart($start);
        $event->setEnd($end);
        $event->setStatus(SchedulerEvent::CONFIRMED);

        return $event;
    }

    /**
     * @param ObjectManager $em
     * @param ReservationItem $reservationItem
     * @param PanelView $panelView
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * @return array
     */
    private function processOverlapReservations(ObjectManager $em, ReservationItem $reservationItem, PanelView $panelView, \DateTime $start, \DateTime $end)
    {
        $affectedOffers = [];

        /** @var ReservationItemRepository $reservationItemRepository */
        $reservationItemRepository = $this->getDoctrine()->getRepository('CBNewAgeBundle:ReservationItem');
        $ris = $reservationItemRepository->getReservationItemsFromInterval(
            $panelView,
            $start,
            $end
        );

        /** @var ReservationItem $ri */
        foreach ($ris as $ri) {
            if ($ri->getId() != $reservationItem->getId()) { // sa nu fie cea curenta
                $localStart = clone $start;
                $localEnd = clone $end;

                if ($ri->getStart() >= $localStart) { // Incepe in cadrul intervalului
                    if ($ri->getEnd() <= $localEnd) // Incepe si se termina in cadrul intervalului (suprapunere). STERGEM!
                    {
                        $em->remove($ri);
                    } else { // Depaseste intervalul. TRIM la inceput!
                        /** @var \DateInterval $interval */
                        $interval = $ri->getEnd()->diff($localStart); // Diferenta dupa micsorare.

                        if ($interval->format('%a') >= 7) { // Mai mare sau egala cu 7 zile, TRIM!
                            $ri->removeAllEvents();

                            $ri->setStart($localEnd->modify('+1 day'));
                            $ri->addDefaultEvent();
                        } else { // Mai mica, STERGEM!
                            $em->remove($ri);
                        }
                    }
                } else { // Incepe inaintea intervalului
                    if ($ri->getEnd() <= $localEnd) // Se termina in cadrul intervalului. TRIM la sfarsit!
                    {
                        /** @var \DateInterval $interval */
                        $interval = $localStart->diff($ri->getStart()); // Diferenta dupa micsorare.

                        if ($interval->format('%a') >= 7) { // Mai mare sau egala cu 7 zile, TRIM!
                            $ri->removeAllEvents();

                            $ri->setEnd($localStart->modify('-1 day'));
                            $ri->addDefaultEvent();
                        } else { // Mai mica, STERGEM!
                            $em->remove($ri);
                        }
                    } else { // Depaseste intervalul. SPLIT!
                        /** @var \DateInterval $interval */
                        $firstInterval = $localStart->diff($ri->getStart()); // Diferenta dupa micsorare.
                        $secondInterval = $ri->getEnd()->diff($localStart); // Diferenta dupa micsorare.

                        $ri->removeAllEvents();

                        $oldReservationItem = new ReservationItem();
                        $oldReservationItem->setOfferItem($ri->getOfferItem());
                        $oldReservationItem->setStart($ri->getStart());
                        $oldReservationItem->setEnd($ri->getEnd());
                        $oldReservationItem->setOwner($this->get('oro_security.security_facade')->getLoggedUser());
                        $oldReservationItem->setOrganization($this->get('oro_security.security_facade')->getOrganization());

                        if ($firstInterval->format('%a') >= 7) { // Daca primul interval este valid, i-l adaugam.
                            $ri->setEnd($localStart->modify('-1 day'));
                            $ri->addDefaultEvent();
                        } else { // Altfel stergem rezervarea
                            $em->remove($ri);
                        }

                        if ($secondInterval->format('%a') >= 7) { // Daca al doilea este valid, adaugam o rezervare noua.
                            $oldReservationItem->setStart($localEnd->modify("+1 day"));
                            $oldReservationItem->addDefaultEvent();

                            $reservationItem->getOfferItem()->addReservationItem($oldReservationItem);
                            $em->persist($oldReservationItem);
                        }
                    }
                }
                $affectedOffers[] = $ri->getOfferItem()->getOffer();
            }
        }

        return $affectedOffers;
    }

    /**
     * @Route(
     *     "/{id}",
     *     name="cb_newage_offer_reservations",
     *     requirements={"id"="\d+"}
     * )
     * @Template
     * @AclAncestor("cb_newage_offer_view")
     *
     * @param OfferItem $offerItem
     *
     * @return array
     */
    public function reservationsAction(OfferItem $offerItem)
    {
        /** @var OfferRepository $offerRepository */
        $offerRepository = $this->getDoctrine()->getRepository('CBNewAgeBundle:Offer');

        return [
            'rows' => $offerRepository->getOfferItemOverlapsInfo($offerItem)
        ];
    }

    /**
     * @Route(
     *      "/{gridName}/export/{offerId}",
     *      name="cb_datagrid_export_action",
     *      requirements={
     *          "gridName"="[\w\:-]+",
     *          "offerId"="\d+"
     *      }
     * )
     *
     * @param string $gridName
     * @param integer $offerId
     *
     * @return BinaryFileResponse
     */
    public function exportAction($gridName, $offerId)
    {
        // Export time execution depends on a size of data
        ignore_user_abort(false);
        set_time_limit(0);

        $request = $this->container->get('request_stack')->getCurrentRequest();
        $format = 'xlsx';
        $csvWriterId = 'oro_importexport.writer.csv';
        $xlsWriterId = 'oro_importexport.writer.xlsx';


        $gridParameters['originalRoute'] = 'cb_newage_offer_view';
        $gridParameters['offer'] = $offerId;
        $gridParameters['pager']['_page'] = 1;
        $gridParameters['pager']['_per_page'] = 25;
        $gridParameters['parameters']['view'] = '__all__';
        $gridParameters['appearance']['_type'] = 'grid';
        $gridParameters['sort_by']['city'] = 'ASC';
        $gridParameters['sort_by']['panel'] = 'ASC';
        $gridParameters['sort_by']['panelView'] = 'ASC';
        $gridParameters['sort_by']['start'] = 'ASC';
        $gridParameters['columns'] = 'url1.city1.address1.panel1.support1.dimensions1.lighting1.panelView1.sketch1.start1.end1';

        /** @var ItemWriterInterface $writer */
        $writer = $this->has($xlsWriterId) ? $this->get($xlsWriterId) : $this->get($csvWriterId);

        $response = $this->get('cb_datagrid.handler.export')->handle(
            $this->get('oro_datagrid.importexport.export_connector'),
            $this->get('cb_datagrid.importexport.processor.export'),
            $writer,
            [
                'gridName' => $gridName,
                'gridParameters' => $gridParameters,
                FormatterProvider::FORMAT_TYPE => $request->query->get('format_type', 'excel')
            ],
            self::EXPORT_BATCH_SIZE,
            $format
        );

        return $response;
    }

    /**
     * @param OfferItem $offerItem
     * @param \DateTime $start
     * @param \\DateTime $end
     *
     * @return ReservationItem
     */
    public function createReservationItemObject(OfferItem $offerItem, $start = null, $end = null)
    {
        $item = new ReservationItem();

        $item->setOfferItem($offerItem);
        if ($start == null) {
            $item->setStart($offerItem->getStart());
        } else {
            $item->setStart($start);
        }
        if ($end == null) {
            $item->setEnd($offerItem->getEnd());
        } else {
            $item->setEnd($end);
        }

        $item->setOwner($this->get('oro_security.security_facade')->getLoggedUser());
        $item->setOrganization($this->get('oro_security.security_facade')->getOrganization());

        $item->addDefaultEvent();

        return $item;
    }
}