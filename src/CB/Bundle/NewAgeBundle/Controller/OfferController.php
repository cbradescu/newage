<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 14/Nov/16
 * Time: 14:51
 */
namespace CB\Bundle\NewAgeBundle\Controller;

use CB\Bundle\NewAgeBundle\Entity\Offer;
use CB\Bundle\NewAgeBundle\Entity\OfferItem;
use CB\Bundle\NewAgeBundle\Entity\PanelView;
use CB\Bundle\NewAgeBundle\Entity\Repository\PanelViewRepository;

use Doctrine\ORM\QueryBuilder;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
            'forbidden' => $this->getForbiddenPanelViews($offer)
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
            if (count($forbiddenPanelViewIds)>0) {
                $query .= ' WHERE pv.id NOT IN (' . implode(",",$forbiddenPanelViewIds) .')';
                $hasWhere = true;
            }

            if (isset($filters['city']['value'])) {
                if ($hasWhere) {
                    $query .= ' AND';
                } else{
                    $query .= ' WHERE';
                    $hasWhere = true;
                }
                $query .= ' a.city_id IN (' . $filters['city']['value'] . ')';
            }

            if (isset($filters['support']['value'])) {
                if ($hasWhere) {
                    $query .= ' AND';
                } else{
                    $query .= ' WHERE';
                    $hasWhere = true;
                }
                $query .= ' p.support_type_id IN (' . $filters['support']['value'] . ')';
            }

            if (isset($filters['lighting']['value'])) {
                if ($hasWhere) {
                    $query .= ' AND';
                } else{
                    $query .= ' WHERE';
                    $hasWhere = true;
                }
                $query .= ' p.lighting_type_id IN (' . $filters['lighting']['value'] . ')';
            }


            if (isset($filters['dimensions']['value'])) {
                if ($hasWhere) {
                    $query .= ' AND';
                } else{
                    $query .= ' WHERE';
                }
                $query .= ' p.dimensions LIKE \'%' . $filters['dimensions']['value'] . '%\'';
            }
        } else {
            if ($hasWhere) {
                $query .= ' AND';
            } else{
                $query .= ' WHERE';
            }

            $query .= ' pv.id IN (' . $values .')';
        }
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $results = $stmt->fetchAll();

        $em = $this->getDoctrine()->getEntityManager();
        $em->beginTransaction();

        try {
            foreach ($offer->getItems() as $item)
            {
                $offer->removeItem($item);
            }

            $entitiesCount = 0;
            /** @var PanelView $panelView */
            foreach ($results as $row) {
                $panelView = $this->getDoctrine()->getRepository('CBNewAgeBundle:PanelView')->findOneBy(['id'=>$row['id']]);

                $confirmedEvents = $panelView->getConfirmedEvents($offer->getStart(), $offer->getEnd());
                if (count($confirmedEvents)>0) { // Daca are evenimente confirmate, cautam intervale libere
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
                    'Removed old panel views from current offer'
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
        $forbiddenPanelViewsIds=[];

        /** @var PanelViewRepository $panelViewRepository */
        $panelViewRepository = $this->getDoctrine()->getRepository('CBNewAgeBundle:PanelView');
        $results = $panelViewRepository->getConfirmedPanelViews($offer->getStart(), $offer->getEnd())->getQuery()->getResult();

        $confirmedPanelViews = [];
        foreach ($results as $row)
        {

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

            if (count($freeIntervals)>0) {
                foreach ($freeIntervals as $freeInterval)
                {
                    $interval = $freeInterval['end']->diff($freeInterval['start']);
                    if ($interval->format('%a')>=7)
                        $forbidden = false;
                }
            }

            if ($forbidden)
                $forbiddenPanelViewsIds[] = $panelView->getId();
        }

        return $forbiddenPanelViewsIds;
    }
}