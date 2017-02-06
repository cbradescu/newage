<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 14/Nov/16
 * Time: 14:51
 */
namespace CB\Bundle\NewAgeBundle\Controller;

use CB\Bundle\NewAgeBundle\Entity\Campaign;
use CB\Bundle\NewAgeBundle\Entity\Offer;
use CB\Bundle\NewAgeBundle\Entity\OfferItem;
use CB\Bundle\NewAgeBundle\Entity\PanelView;
use CB\Bundle\NewAgeBundle\Entity\Repository\OfferRepository;
use CB\Bundle\NewAgeBundle\Entity\Repository\PanelViewRepository;
use CB\Bundle\NewAgeBundle\Entity\Repository\ReservationItemRepository;

use CB\Bundle\NewAgeBundle\Entity\ReservationItem;
use CB\Bundle\SchedulerBundle\Entity\SchedulerEvent;

use Doctrine\ORM\EntityManager;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * @Route("/offer")
 */
class OfferController extends Controller
{
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
     */
    public function updateAction(Offer $offer)
    {
        return $this->update($offer);
    }

    /**
     * @Route("/select/{id}", name="cb_newage_offer_select", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("cb_newage_offer_select")
     */
    public function selectAction(Offer $offer)
    {
        return [
            'entity' => $offer,
            'forbidden' => $this->getForbiddenPanelViews($offer) ?: 0
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
     * @Route(
     *      "/widget/panel_views/{id}",
     *      name="cb_offer_widget_panel_views_info",
     *      requirements={"id"="\d+"},
     *      defaults={"id"=0}
     * )
     * @AclAncestor("cb_newage_panel_view_view")
     * @Template()
     *
     * @param Offer $offer
     * @return array
     */
    public function panelViewsInfoAction(Offer $offer = null)
    {
        return [
            'offer' => $offer
        ];
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
     */
    public function offerMassActionAction($gridName, $actionName)
    {
        /** @var MassActionDispatcher $massActionDispatcher */
        $massActionDispatcher = $this->get('oro_datagrid.mass_action.dispatcher');

        $response = $massActionDispatcher->dispatchByRequest($gridName, $actionName, $this->getRequest());

        $offer = $response->getOption('offer');
        $isAllSelected = $response->getOption('isAllSelected');
        $values = $response->getOption('values');
        $filters = $response->getOption('filters');

        /** A trebuit sa folosesc native sql pentru ca $querybuilder intorcea doar o parte din inregistrari */
        $conn = $this->getDoctrine()->getManager()->getConnection();
        $query = '
              SELECT 
                  pv.id as id
              FROM 
                  cb_newage_panel_view pv 
              LEFT JOIN 
                  cb_newage_panel p ON p.id=pv.panel_id
              LEFT JOIN 
                  cb_newage_panel_address a ON a.owner_id = p.id AND (a.is_primary = 1)
        ';

        $hasWhere = false;

        if ($isAllSelected) {
            $forbiddenPanelViewIds = $this->getForbiddenPanelViews($offer);
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
                $query .= ' a.city_id IN (' . $filters['city']['value'] . ')';
            }

            if (isset($filters['support']['value'])) {
                if ($hasWhere) {
                    $query .= ' AND';
                } else {
                    $query .= ' WHERE';
                    $hasWhere = true;
                }
                $query .= ' p.support_type_id IN (' . $filters['support']['value'] . ')';
            }

            if (isset($filters['lighting']['value'])) {
                if ($hasWhere) {
                    $query .= ' AND';
                } else {
                    $query .= ' WHERE';
                    $hasWhere = true;
                }
                $query .= ' p.lighting_type_id IN (' . $filters['lighting']['value'] . ')';
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
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $em = $this->getDoctrine()->getEntityManager();
        $em->beginTransaction();

        try {
            foreach ($offer->getOfferItems() as $item) {
                $offer->removeOfferItem($item);
            }

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

                    foreach ($freeIntervals as $freeInterval) {
                        $interval = $freeInterval['end']->diff($freeInterval['start']);
                        if ($interval->format('%a') >= 7) {
                            $item = new OfferItem();

                            $item->setOffer($offer);
                            $item->setPanelView($panelView);
                            $item->setStart($freeInterval['start']);
                            $item->setEnd($freeInterval['end']);
                            $item->setOwner($this->get('oro_security.security_facade')->getLoggedUser());
                            $item->setOrganization($this->get('oro_security.security_facade')->getOrganization());

                            $em->persist($item);
                            $entitiesCount++;
                        }
                    }
                } else { // In caz contrar fata este libera pentru toata perioada ofertei
                    $item = new OfferItem();

                    $item->setOffer($offer);
                    $item->setPanelView($panelView);
                    $item->setStart($offer->getStart());
                    $item->setEnd($offer->getEnd());
                    $item->setOwner($this->get('oro_security.security_facade')->getLoggedUser());
                    $item->setOrganization($this->get('oro_security.security_facade')->getOrganization());

                    $em->persist($item);
                    $entitiesCount++;
                }
            }

            $em->flush();
            $em->commit();

            $this->get('session')->getFlashBag()->add(
                'warning',
                $this->get('translator')->trans(
                    'cb.newage.offer.message.offer_items.removed'
                )
            );

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->transChoice(
                    'cb.newage.offer.message.items.added',
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
     */
    public function reserveMassActionAction($gridName, $actionName)
    {
        /** @var MassActionDispatcher $massActionDispatcher */
        $massActionDispatcher = $this->get('oro_datagrid.mass_action.dispatcher');

        $response = $massActionDispatcher->dispatchByRequest($gridName, $actionName, $this->getRequest());

        $offer = $response->getOption('offer');
        $isAllSelected = $response->getOption('isAllSelected');
        $values = $response->getOption('values');
        $filters = $response->getOption('filters');

        /** A trebuit sa folosesc native sql pentru ca $querybuilder intorcea doar o parte din inregistrari */
        $conn = $this->getDoctrine()->getManager()->getConnection();
        $query = '
              SELECT 
                  DISTINCT pv.id as id
              FROM 
                  cb_newage_offer_item oi
              LEFT JOIN
                  cb_newage_panel_view pv ON pv.id=oi.panel_view_id 
              LEFT JOIN 
                  cb_newage_panel p ON p.id=pv.panel_id
              LEFT JOIN 
                  cb_newage_panel_address a ON a.owner_id = p.id AND (a.is_primary = 1)
        ';

        $hasWhere = false;

        if ($isAllSelected) {
            $forbiddenPanelViewIds = $this->getForbiddenPanelViews($offer);
            if (count($forbiddenPanelViewIds) > 0) {
                $query .= ' WHERE oi.id NOT IN (' . implode(",", $forbiddenPanelViewIds) . ')';
                $hasWhere = true;
            }

            if (isset($filters['city']['value'])) {
                if ($hasWhere) {
                    $query .= ' AND';
                } else {
                    $query .= ' WHERE';
                    $hasWhere = true;
                }
                $query .= ' a.city_id IN (' . $filters['city']['value'] . ')';
            }

            if (isset($filters['support']['value'])) {
                if ($hasWhere) {
                    $query .= ' AND';
                } else {
                    $query .= ' WHERE';
                    $hasWhere = true;
                }
                $query .= ' p.support_type_id IN (' . $filters['support']['value'] . ')';
            }

            if (isset($filters['lighting']['value'])) {
                if ($hasWhere) {
                    $query .= ' AND';
                } else {
                    $query .= ' WHERE';
                    $hasWhere = true;
                }
                $query .= ' p.lighting_type_id IN (' . $filters['lighting']['value'] . ')';
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

            $query .= ' oi.id IN (' . $values . ')';
        }
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $em = $this->getDoctrine()->getEntityManager();
        $em->beginTransaction();

        try {
            // delete old reservation items
            foreach ($offer->getReservationItems() as $item) {
                $offer->removeReservationItem($item);
            }
            $em->flush();

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

                    foreach ($freeIntervals as $freeInterval) {
                        $interval = $freeInterval['end']->diff($freeInterval['start']);
                        if ($interval->format('%a') >= 7) {
                            $item = new ReservationItem();

                            $item->setOffer($offer);
                            $item->setPanelView($panelView);
                            $item->setStart($freeInterval['start']);
                            $item->setEnd($freeInterval['end']);
                            $item->setOwner($this->get('oro_security.security_facade')->getLoggedUser());
                            $item->setOrganization($this->get('oro_security.security_facade')->getOrganization());

                            $item->addDefaultEvent();

                            $em->persist($item);
                            $entitiesCount++;
                        }
                    }
                } else { // In caz contrar fata este libera pentru toata perioada ofertei
                    $item = new ReservationItem();

                    $item->setOffer($offer);
                    $item->setPanelView($panelView);
                    $item->setStart($offer->getStart());
                    $item->setEnd($offer->getEnd());
                    $item->setOwner($this->get('oro_security.security_facade')->getLoggedUser());
                    $item->setOrganization($this->get('oro_security.security_facade')->getOrganization());

                    $item->addDefaultEvent();

                    $em->persist($item);
                    $entitiesCount++;
                }
            }

            $em->flush();
            $em->commit();

            $this->get('session')->getFlashBag()->add(
                'warning',
                $this->get('translator')->trans(
                    'cb.newage.offer.message.reservation_items.removed'
                )
            );

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->transChoice(
                    'cb.newage.offer.message.items.added',
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
     * @Route("/{gridName}/confirmMassAction/{actionName}", name="cb_confirm_massaction")
     * @AclAncestor("cb_newage_offer_create")
     * @Template("CBNewAgeBundle:Offer:view.html.twig")
     *
     * @param string $gridName
     * @param string $actionName
     *
     * @return RedirectResponse
     */
    public function confirmMassActionAction($gridName, $actionName)
    {
        /** @var MassActionDispatcher $massActionDispatcher */
        $massActionDispatcher = $this->get('oro_datagrid.mass_action.dispatcher');

        $response = $massActionDispatcher->dispatchByRequest($gridName, $actionName, $this->getRequest());

        /** @var Offer $offer */
        $offer = $response->getOption('offer');
        $isAllSelected = $response->getOption('isAllSelected');
        $values = $response->getOption('values');
        $filters = $response->getOption('filters');

        $em = $this->getDoctrine()->getEntityManager();
        $em->beginTransaction();

        /**
         * Removing all events attached to current offer reservation items.
         */
        foreach ($offer->getReservationItems() as $ri) {
            /** @var ReservationItem $ri */
            foreach ($ri->getEvents() as $event)
                $ri->removeEvent($event);
        }

        $em->flush();


        /** A trebuit sa folosesc native sql pentru ca $querybuilder intorcea doar o parte din inregistrari */
        $conn = $this->getDoctrine()->getManager()->getConnection();
        $query = '
              SELECT 
                  DISTINCT ri.id as id
              FROM 
                  cb_newage_reservation_item ri
              LEFT JOIN
                  cb_newage_panel_view pv ON pv.id=ri.panel_view_id 
              LEFT JOIN 
                  cb_newage_panel p ON p.id=pv.panel_id
              LEFT JOIN 
                  cb_newage_panel_address a ON a.owner_id = p.id AND (a.is_primary = 1)
        ';

        $hasWhere = false;

        if ($isAllSelected) {
            $forbiddenPanelViewIds = $this->getForbiddenPanelViews($offer);
            if (count($forbiddenPanelViewIds) > 0) {
                $query .= ' WHERE ri.id NOT IN (' . implode(",", $forbiddenPanelViewIds) . ')';
                $hasWhere = true;
            }

            if (isset($filters['city']['value'])) {
                if ($hasWhere) {
                    $query .= ' AND';
                } else {
                    $query .= ' WHERE';
                    $hasWhere = true;
                }
                $query .= ' a.city_id IN (' . $filters['city']['value'] . ')';
            }

            if (isset($filters['support']['value'])) {
                if ($hasWhere) {
                    $query .= ' AND';
                } else {
                    $query .= ' WHERE';
                    $hasWhere = true;
                }
                $query .= ' p.support_type_id IN (' . $filters['support']['value'] . ')';
            }

            if (isset($filters['lighting']['value'])) {
                if ($hasWhere) {
                    $query .= ' AND';
                } else {
                    $query .= ' WHERE';
                    $hasWhere = true;
                }
                $query .= ' p.lighting_type_id IN (' . $filters['lighting']['value'] . ')';
            }

            if (isset($filters['dimensions']['value'])) {
                if ($hasWhere) {
                    $query .= ' AND';
                } else {
                    $query .= ' WHERE';
                    $hasWhere = true;
                }
                $query .= ' p.dimensions LIKE \'%' . $filters['dimensions']['value'] . '%\'';
            }

            if ($hasWhere) {
                $query .= ' AND';
            } else {
                $query .= ' WHERE';
                $hasWhere = true;
            }
            $query .= ' ri.offer_id = ' . $offer->getId();
        } else {
            if ($hasWhere) {
                $query .= ' AND';
            } else {
                $query .= ' WHERE';
            }

            $query .= ' ri.id IN (' . $values . ')';
        }

        $stmt = $conn->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        try {
            $entitiesCount = 0;
            $affectedOffers = [];

            /** @var ReservationItem $reservationItem */
            foreach ($results as $row) {
                $reservationItem = $this->getDoctrine()->getRepository('CBNewAgeBundle:ReservationItem')->findOneBy(['id' => $row['id']]);

                /** @var PanelView $panelView */
                $panelView = $reservationItem->getPanelView();

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
                                $reservationItem->getOffer()->getCampaign(),
                                $panelView,
                                $freeInterval['start'],
                                $freeInterval['end']
                            );
                            $reservationItem->addEvent($event);

                            $em->persist($event);
                            $entitiesCount++;

                            /**
                             * Cautam rezervari in aceeasi perioda cu intervalul.
                             */
                            $aff = $this->processOverlapReservations($em, $offer, $panelView, $freeInterval['start'], $freeInterval['end']);
                            $affectedOffers = array_merge($affectedOffers, $aff);
                        }
                    }
                } else { // In caz contrar fata este libera pentru toata perioada ofertei
                    $event = $this->newEvent($reservationItem,
                        $reservationItem->getOffer()->getCampaign(),
                        $panelView,
                        $reservationItem->getStart(),
                        $reservationItem->getEnd()
                    );
                    $reservationItem->addEvent($event);

                    $em->persist($event);
                    $entitiesCount++;

                    /**
                     * Cautam rezervari in aceeasi perioda cu intervalul.
                     */
                    $aff = $this->processOverlapReservations($em, $offer, $panelView, clone $reservationItem->getStart(),clone  $reservationItem->getEnd());
                    $affectedOffers = array_merge($affectedOffers, $aff);
                }
            }

            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->transChoice(
                    'cb.newage.offer.message.items.confirmed',
                    $entitiesCount,
                    ['%count%' => $entitiesCount]
                )
            );
        } catch (\Exception $e) {
            $em->rollback();
            throw $e;
        }

        $em->flush();
        $em->commit();

        $affectedOffers = array_unique($affectedOffers);
        if (count($affectedOffers)>0) {

            foreach ($affectedOffers as $aff) {
                try {
                    $this->get('cb_newage.mailer.processor')->sendReservationChangeEmail($offer, $aff);
                } catch (\Exception $e) {
                    throw $e;
                }
            }
        }

        return $this->redirect($this->get('router')->generate('cb_newage_offer_view', ['id' => $offer->getId()]));
    }

    /**
     * Return Panel View ids that are not allow to be offered.
     *
     * @param Offer $offer
     *
     * @return array
     */
    protected function getForbiddenPanelViews(Offer $offer)
    {
        $forbiddenPanelViewsIds = [];

        /** @var PanelViewRepository $panelViewRepository */
        $panelViewRepository = $this->getDoctrine()->getRepository('CBNewAgeBundle:PanelView');
        $results = $panelViewRepository->getConfirmedPanelViews($offer->getStart(), $offer->getEnd())->getQuery()->getResult();

        $confirmedPanelViews = [];
        foreach ($results as $row) {

            $confirmedPanelViews[$row['panelView']][] = [
                'start' => $row['start'],
                'end' => $row['end']
            ];
        }

        foreach ($confirmedPanelViews as $panelViewId => $intervals) {
            $panelView = $panelViewRepository->findOneBy(['id' => $panelViewId]);

            /** @var PanelView $panelView */
            $freeIntervals = $panelView->getFreeIntervals($intervals, $offer->getStart(), $offer->getEnd());

            $forbidden = true;

            if (count($freeIntervals) > 0) {
                /** @var array $freeInterval */
                foreach ($freeIntervals as $freeInterval) {
                    /** @var \DateInterval $interval */
                    $interval = $freeInterval['end']->diff($freeInterval['start']);
                    if ($interval->format('%a') >= 7)
                        $forbidden = false;
                }
            }

            if ($forbidden)
                $forbiddenPanelViewsIds[] = $panelView->getId();
        }

        return $forbiddenPanelViewsIds;
    }

    /**
     * @param ReservationItem $reservationItem
     * @param Campaign $campaign
     * @param PanelView $panelView
     * @param \DateTime $start
     * @param \DateTime $end
     * @return SchedulerEvent
     */
    private function newEvent(ReservationItem $reservationItem, Campaign $campaign, PanelView $panelView, \DateTime $start, \DateTime $end)
    {
        $event = new SchedulerEvent();

        $event->setCampaign($campaign);
        $event->setPanelView($panelView);
        $event->setReservationItem($reservationItem);
        $event->setStart($start);
        $event->setEnd($end);
        $event->setStatus(SchedulerEvent::CONFIRMED);

        return $event;
    }

    /**
     * @param EntityManager $em
     * @param Offer $offer
     * @param PanelView $panelView
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * @return array
     */
    private function processOverlapReservations(EntityManager $em, Offer $offer, PanelView $panelView, \DateTime $start, \DateTime $end)
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
        foreach ($ris as $ri)
        {
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
                    $oldReservationItem->setOffer($ri->getOffer());
                    $oldReservationItem->setPanelView($ri->getPanelView());
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

                        $offer->addReservationItem($oldReservationItem);
                        $em->persist($oldReservationItem);
                    }
                }
            }
            $affectedOffers[] = $ri->getOffer();
        }

        return $affectedOffers;
    }

    /**
     * @Route(
     *     "/{offerItemId}/{panelViewId}",
     *     name="cb_newage_panel_view_reservations",
     *     requirements={"offerItemId"="\d+","panelViewId"="\d+"}
     * )
     * @Template
     * @ParamConverter("offerItem", class="CBNewAgeBundle:OfferItem", options={"id"="offerItemId"})
     * @ParamConverter("panelView", class="CBNewAgeBundle:PanelView", options={"id"="panelViewId"})
     * @AclAncestor("cb_newage_panel_view_view")
     * @param OfferItem $offerItem
     * @param PanelView $panelView
     *
     * @return array
     */
    public function reservationsAction(OfferItem $offerItem, PanelView $panelView)
    {
//        /** @var OfferRepository $offerRepository */
//        $offerRepository = $this->getDoctrine()->getRepository('CBNewAgeBundle:Offer');
//        $offers = $offerRepository->getOfferWithReservationsFromInterval(
//            $panelView,
//            $offerItem->getStart(),
//            $offerItem->getEnd()
//        );
//
//        return [
//            'offers' => $offers,
//        ];

        return [
            'panelView' => $panelView,
            'offerItem' => $offerItem
        ];
    }
}