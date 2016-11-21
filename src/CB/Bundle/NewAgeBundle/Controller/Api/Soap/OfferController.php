<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 14/Nov/16
 * Time: 14:51
 */

namespace CB\Bundle\NewAgeBundle\Controller\Api\Soap;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Soap\SoapController;

class OfferController  extends SoapController
{
    /**
     * @Soap\Method("getOffers")
     * @Soap\Param("page", phpType="int")
     * @Soap\Param("limit", phpType="int")
     * @Soap\Param("order", phpType="string")
     * @Soap\Result(phpType="CB\Bundle\NewAgeBundle\Entity\OfferSoap[]")
     * @AclAncestor("cb_newage_offer_view")
     */
    public function cgetAction($page = 1, $limit = 10, $order = 'DESC')
    {
        $order = (strtoupper($order) == 'ASC') ? $order : 'DESC';
        return $this->handleGetListRequest($page, $limit, [], array('reportedAt' => $order));
    }

    /**
     * @Soap\Method("getoffer")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="CB\Bundle\NewAgeBundle\Entity\OfferSoap")
     * @AclAncestor("cb_newage_offer_view")
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * @Soap\Method("createOffer")
     * @Soap\Param("offer", phpType="CB\Bundle\NewAgeBundle\Entity\OfferSoap")
     * @Soap\Result(phpType="int")
     * @AclAncestor("cb_newage_offer_create")
     */
    public function createAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * @Soap\Method("updateOffer")
     * @Soap\Param("id", phpType="int")
     * @Soap\Param("offer", phpType="CB\Bundle\NewAgeBundle\Entity\OfferSoap")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("cb_newage_offer_update")
     */
    public function updateAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * @Soap\Method("deleteOffer")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("cb_newage_offer_delete")
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
        return $this->container->get('cb_newage_offer.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->container->get('cb_newage_offer.form.entity.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->container->get('cb_newage_offer.form.handler.entity.api');
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