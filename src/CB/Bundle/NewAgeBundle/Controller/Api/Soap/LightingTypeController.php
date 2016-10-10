<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 07/Oct/16
 * Time: 11:41
 */

namespace CB\Bundle\NewAgeBundle\Controller\Api\Soap;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Soap\SoapController;

class LightingTypeController  extends SoapController
{
    /**
     * @Soap\Method("getLightingTypes")
     * @Soap\Param("page", phpType="int")
     * @Soap\Param("limit", phpType="int")
     * @Soap\Param("order", phpType="string")
     * @Soap\Result(phpType="CB\Bundle\NewAgeBundle\Entity\LightingTypeSoap[]")
     * @AclAncestor("cb_newage_lighting_type_view")
     */
    public function cgetAction($page = 1, $limit = 10, $order = 'DESC')
    {
        $order = (strtoupper($order) == 'ASC') ? $order : 'DESC';
        return $this->handleGetListRequest($page, $limit, [], array('reportedAt' => $order));
    }

    /**
     * @Soap\Method("getlighting_type")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="CB\Bundle\NewAgeBundle\Entity\LightingTypeSoap")
     * @AclAncestor("cb_newage_lighting_type_view")
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * @Soap\Method("createLightingType")
     * @Soap\Param("lighting_type", phpType="CB\Bundle\NewAgeBundle\Entity\LightingTypeSoap")
     * @Soap\Result(phpType="int")
     * @AclAncestor("cb_newage_lighting_type_create")
     */
    public function createAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * @Soap\Method("updateLightingType")
     * @Soap\Param("id", phpType="int")
     * @Soap\Param("lighting_type", phpType="CB\Bundle\NewAgeBundle\Entity\LightingTypeSoap")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("cb_newage_lighting_type_update")
     */
    public function updateAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * @Soap\Method("deleteLightingType")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("cb_newage_lighting_type_delete")
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
        return $this->container->get('cb_newage_lighting_type.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->container->get('cb_newage_lighting_type.form.entity.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->container->get('cb_newage_lighting_type.form.handler.entity.api');
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