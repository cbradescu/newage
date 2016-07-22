<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 21/Jun/16
 * Time: 15:10
 */

namespace CB\Bundle\NewAgeBundle\Controller\Api\Soap;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Soap\SoapController;

class CampaignController  extends SoapController
{
    /**
     * @Soap\Method("getCampaigns")
     * @Soap\Param("page", phpType="int")
     * @Soap\Param("limit", phpType="int")
     * @Soap\Param("order", phpType="string")
     * @Soap\Result(phpType="CB\Bundle\NewAgeBundle\Entity\CampaignSoap[]")
     * @AclAncestor("cb_newage_campaign_view")
     */
    public function cgetAction($page = 1, $limit = 10, $order = 'DESC')
    {
        $order = (strtoupper($order) == 'ASC') ? $order : 'DESC';
        return $this->handleGetListRequest($page, $limit, [], array('reportedAt' => $order));
    }

    /**
     * @Soap\Method("getcampaign")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="CB\Bundle\NewAgeBundle\Entity\CampaignSoap")
     * @AclAncestor("cb_newage_campaign_view")
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * @Soap\Method("createCampaign")
     * @Soap\Param("campaign", phpType="CB\Bundle\NewAgeBundle\Entity\CampaignSoap")
     * @Soap\Result(phpType="int")
     * @AclAncestor("cb_newage_campaign_create")
     */
    public function createAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * @Soap\Method("updateCampaign")
     * @Soap\Param("id", phpType="int")
     * @Soap\Param("campaign", phpType="CB\Bundle\NewAgeBundle\Entity\CampaignSoap")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("cb_newage_campaign_update")
     */
    public function updateAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * @Soap\Method("deleteCampaign")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("cb_newage_campaign_delete")
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->container->get('cb_newage_campaign.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->container->get('cb_newage_campaign.form.entity.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->container->get('cb_newage_campaign.form.handler.entity.api');
    }

    /**
     * {@inheritDoc}
     */
    protected function fixFormData(array &$data, $entity)
    {
        parent::fixFormData($data, $entity);

        unset($data['id']);
        unset($data['createdAt']);
        unset($data['updatedAt']);

        return true;
    }

}