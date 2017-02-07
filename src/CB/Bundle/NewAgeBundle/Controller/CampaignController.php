<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 21/Jun/16
 * Time: 15:10
 */
namespace CB\Bundle\NewAgeBundle\Controller;

use CB\Bundle\NewAgeBundle\Entity\Campaign;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;

/**
 * @Route("/campaign")
 */
class CampaignController extends Controller
{
    /**
     * @Route("/index", name="cb_newage_campaign_index")
     * @Template()
     * @AclAncestor("cb_newage_campaign_view")
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/view/{id}", name="cb_newage_campaign_view", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("cb_newage_campaign_view")
     */
    public function viewAction(Campaign $campaign)
    {
        return [
            'entity' => $campaign
        ];
    }

    /**
     * @Route("/create", name="cb_newage_campaign_create")
     * @AclAncestor("cb_newage_campaign_create")
     * @Template("CBNewAgeBundle:Campaign:update.html.twig")
     */
    public function createAction()
    {
        $campaign = $this->get('cb_newage_campaign.manager')->createCampaign();

        return $this->update($campaign);
    }

    /**
     * @Route("/update/{id}", name="cb_newage_campaign_update", requirements={"id"="\d+"})
     * @Template
     * @AclAncestor("cb_newage_campaign_update")
     */
    public function updateAction(Campaign $campaign)
    {
        return $this->update($campaign);
    }

    /**
     * @param Campaign $campaign
     * @return array
     */
    protected function update(Campaign $campaign)
    {
        if ($this->get('cb_newage_campaign.form.handler.entity')->process($campaign)) {
            $this->get('session')->getFlashBag()->add(
                'success',
                $this->get('translator')->trans('cb.newage.campaign.message.saved')
            );

            return $this->get('oro_ui.router')->redirectAfterSave(
                ['route' => 'cb_newage_campaign_update', 'parameters' => ['id' => $campaign->getId()]],
                ['route' => 'cb_newage_campaign_view', 'parameters' => ['id' => $campaign->getId()]]
            );
        }

        return array(
            'entity' => $campaign,
            'form' => $this->get('cb_newage_campaign.form.entity')->createView()
        );
    }

    /**
     * @Route("/add", name="cb_newage_campaign_add")
     * @AclAncestor("cb_newage_campaign_create")
     * @Template("CBNewAgeBundle:Campaign:update.html.twig")
     */
    public function addAction()
    {
        $campaign = $this->get('cb_newage_campaign.manager')->createCampaign();

        if ($this->get('cb_newage_campaign.form.handler.entity')->process($campaign)) {
            return array(
                'saved' => true,
                'entity' => $campaign,
                'form' => $this->get('cb_newage_campaign.form.entity')->createView()
            );
        }

        return array(
            'entity' => $campaign,
            'form' => $this->get('cb_newage_campaign.form.entity')->createView()
        );
    }
}