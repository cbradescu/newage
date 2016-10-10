<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 07/Oct/16
 * Time: 09:44
 */

namespace CB\Bundle\NewAgeBundle\Controller\Api\Soap;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Soap\SoapController;

class SupportTypeController  extends SoapController
{
    /**
     * @Soap\Method("getSupportTypes")
     * @Soap\Param("page", phpType="int")
     * @Soap\Param("limit", phpType="int")
     * @Soap\Param("order", phpType="string")
     * @Soap\Result(phpType="CB\Bundle\NewAgeBundle\Entity\SupportTypeSoap[]")
     * @AclAncestor("cb_newage_support_type_view")
     */
    public function cgetAction($page = 1, $limit = 10, $order = 'DESC')
    {
        $order = (strtoupper($order) == 'ASC') ? $order : 'DESC';
        return $this->handleGetListRequest($page, $limit, [], array('reportedAt' => $order));
    }

    /**
     * @Soap\Method("getsupport_type")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="CB\Bundle\NewAgeBundle\Entity\SupportTypeSoap")
     * @AclAncestor("cb_newage_support_type_view")
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * @Soap\Method("createSupportType")
     * @Soap\Param("support_type", phpType="CB\Bundle\NewAgeBundle\Entity\SupportTypeSoap")
     * @Soap\Result(phpType="int")
     * @AclAncestor("cb_newage_support_type_create")
     */
    public function createAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * @Soap\Method("updateSupportType")
     * @Soap\Param("id", phpType="int")
     * @Soap\Param("support_type", phpType="CB\Bundle\NewAgeBundle\Entity\SupportTypeSoap")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("cb_newage_support_type_update")
     */
    public function updateAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * @Soap\Method("deleteSupportType")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("cb_newage_support_type_delete")
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
        return $this->container->get('cb_newage_support_type.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->container->get('cb_newage_support_type.form.entity.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->container->get('cb_newage_support_type.form.handler.entity.api');
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