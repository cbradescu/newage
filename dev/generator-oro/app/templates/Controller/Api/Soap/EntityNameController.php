<?php
/**
 * Created by orm-generator.
 * User: <%= user %>
 * Date: <%= date %>
 * Time: <%= time %>
 */

namespace <%= BundleNamespace %>\Controller\Api\Soap;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Soap\SoapController;

class <%= EntityName %>Controller  extends SoapController
{
    /**
     * @Soap\Method("get<%= EntityNames %>")
     * @Soap\Param("page", phpType="int")
     * @Soap\Param("limit", phpType="int")
     * @Soap\Param("order", phpType="string")
     * @Soap\Result(phpType="<%= BundleNamespace %>\Entity\<%= EntityName %>Soap[]")
     * @AclAncestor("<%= bundle_name %>_<%= entity_name %>_view")
     */
    public function cgetAction($page = 1, $limit = 10, $order = 'DESC')
    {
        $order = (strtoupper($order) == 'ASC') ? $order : 'DESC';
        return $this->handleGetListRequest($page, $limit, [], array('reportedAt' => $order));
    }

    /**
     * @Soap\Method("get<%= entity_name %>")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="<%= BundleNamespace %>\Entity\<%= EntityName %>Soap")
     * @AclAncestor("<%= bundle_name %>_<%= entity_name %>_view")
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * @Soap\Method("create<%= EntityName %>")
     * @Soap\Param("<%= entity_name %>", phpType="<%= BundleNamespace %>\Entity\<%= EntityName %>Soap")
     * @Soap\Result(phpType="int")
     * @AclAncestor("<%= bundle_name %>_<%= entity_name %>_create")
     */
    public function createAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * @Soap\Method("update<%= EntityName %>")
     * @Soap\Param("id", phpType="int")
     * @Soap\Param("<%= entity_name %>", phpType="<%= BundleNamespace %>\Entity\<%= EntityName %>Soap")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("<%= bundle_name %>_<%= entity_name %>_update")
     */
    public function updateAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * @Soap\Method("delete<%= EntityName %>")
     * @Soap\Param("id", phpType="int")
     * @Soap\Result(phpType="boolean")
     * @AclAncestor("<%= bundle_name %>_<%= entity_name %>_delete")
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
        return $this->container->get('<%= bundle_name %>_<%= entity_name %>.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->container->get('<%= bundle_name %>_<%= entity_name %>.form.entity.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->container->get('<%= bundle_name %>_<%= entity_name %>.form.handler.entity.api');
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