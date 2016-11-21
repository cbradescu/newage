<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 14/Nov/16
 * Time: 14:51
 */
namespace CB\Bundle\NewAgeBundle\Controller;

use CB\Bundle\NewAgeBundle\Entity\Offer;
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
     */
    public function panelViewsInfoAction(Offer $offer = null)
    {

        return [
            'offer' => $offer
        ];
    }
}