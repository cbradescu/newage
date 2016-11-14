<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 14/Nov/16
 * Time: 08:19
 */

namespace CB\Bundle\NewAgeBundle\Controller\Api\Soap;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Soap\SoapController;

class CityController  extends SoapController
{
    /**
     * @Soap\Method("getCities")
     * @Soap\Param("page", phpType="int")
     * @Soap\Param("limit", phpType="int")
     * @Soap\Param("order", phpType="string")
     * @Soap\Result(phpType="CB\Bundle\NewAgeBundle\Entity\CitySoap[]")
     * @AclAncestor("cb_newage_city_view")
     */
    public function cgetAction($page = 1, $limit = 10, $order = 'DESC')
    {
        $order = (strtoupper($order) == 'ASC') ? $order : 'DESC';
        return $this->handleGetListRequest($page, $limit, [], array('reportedAt' => $order));
    }

    /**
     * @Soap\Method("getcity")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="CB\Bundle\NewAgeBundle\Entity\CitySoap")
     * @AclAncestor("cb_newage_city_view")
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * @Soap\Method("createCity")
     * @Soap\Param("city", phpType="CB\Bundle\NewAgeBundle\Entity\CitySoap")
     * @Soap\Result(phpType="int")
     * @AclAncestor("cb_newage_city_create")
     */
    public function createAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * @Soap\Method("updateCity")
     * @Soap\Param("id", phpType="int")
     * @Soap\Param("city", phpType="CB\Bundle\NewAgeBundle\Entity\CitySoap")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("cb_newage_city_update")
     */
    public function updateAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * @Soap\Method("deleteCity")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("cb_newage_city_delete")
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
        return $this->container->get('cb_newage_city.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->container->get('cb_newage_city.form.entity.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->container->get('cb_newage_city.form.handler.entity.api');
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