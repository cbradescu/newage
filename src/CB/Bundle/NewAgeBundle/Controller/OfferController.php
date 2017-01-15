<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 14/Nov/16
 * Time: 14:51
 */
namespace CB\Bundle\NewAgeBundle\Controller;

use CB\Bundle\NewAgeBundle\Entity\Offer;
use CB\Bundle\NewAgeBundle\Entity\PanelView;
use CB\Bundle\NewAgeBundle\Entity\Repository\PanelViewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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
                $this->get('translator')->trans('cb_newage.offer.message.saved')
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
//            error_log($row['panelView']. "\t" . $row['start']->format('Y-m-d') . "\t" . $row['end']->format('Y-m-d') ."\n", 3, '/var/www/newage/crm-application/app/logs/catalin');

            $confirmedPanelViews[$row['panelView']][] = [
                'start' => $row['start'],
                'end' => $row['end']
            ];
        }

//        ob_start();
//        var_dump($confirmedPanelViews);
//        $x = ob_get_clean();
//        error_log('Dupa sortare: '. count($confirmedPanelViews) ."\n", 3, '/var/www/newage/crm-application/app/logs/catalin');
//        error_log('Dupa sortare: '. $x ."\n", 3, '/var/www/newage/crm-application/app/logs/catalin');


        foreach ($confirmedPanelViews as $panelViewId => $intervals) {
            $panelView = $panelViewRepository->findOneBy(['id' => $panelViewId]);
//            error_log('PanelView: '.$panelView->getId() . "\t" . count($intervals) ."\n", 3, '/var/www/newage/crm-application/app/logs/catalin');

            /** @var PanelView $panelView */
            $freeIntervals = $panelView->getFreeIntervals($intervals, $offer->getStart(), $offer->getEnd());

            $forbidden = true;
//            error_log('Free intervals: '. count($freeIntervals) ."\n", 3, '/var/www/newage/crm-application/app/logs/catalin');

            if (count($freeIntervals)>0) {
                foreach ($freeIntervals as $freeInterval)
                {
//                    error_log('Intervals: ' . $freeInterval['start']->format('Y-m-d') . "\t" . $freeInterval['end']->format('Y-m-d') ."\n", 3, '/var/www/newage/crm-application/app/logs/catalin');
                    $interval = $freeInterval['end']->diff($freeInterval['start']);
                    if ($interval->format('%a')>=5)
                        $forbidden = false;
                }
            }

            if ($forbidden)
                $forbiddenPanelViewsIds[] = $panelView->getId();
        }

        return $forbiddenPanelViewsIds;
    }
}