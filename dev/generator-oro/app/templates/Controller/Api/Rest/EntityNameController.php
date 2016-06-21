<?php
/**
 * Created by orm-generator.
 * User: <%= user %>
 * Date: <%= date %>
 * Time: <%= time %>
 */

namespace <%= BundleNamespace %>\Controller\Api\Rest;

use <%= BundleNamespace %>\Entity\<%= EntityName %>;
use Symfony\Component\HttpFoundation\Response;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;

/**
 * @Rest\RouteResource("<%= entity_name %>")
 * @Rest\NamePrefix("<%= bundle_name %>_<%= entity_name %>_api_")
 */
class <%= EntityName %>Controller extends RestController implements ClassResourceInterface
{
    /**
     * REST GET list
     *
     * @Rest\QueryParam(
     *     name="page",
     *     requirements="\d+",
     *     nullable=true,
     *     description="Page number, starting from 1. Defaults to 1."
     * )
     * @Rest\QueryParam(
     *     name="limit",
     *     requirements="\d+",
     *     nullable=true,
     *     description="Number of items per page. defaults to 10."
     * )
     * @ApiDoc(
     *     description="Get all <%= EntityName %> items",
     *     resource=true
     * )
     * @AclAncestor("<%= bundle_name %>_<%= entity_name %>_view")
     * @return Response
     */
    public function cgetAction()
    {
        $page  = (int)$this->getRequest()->get('page', 1);
        $limit = (int)$this->getRequest()->get('limit', self::ITEMS_PER_PAGE);

        return $this->handleGetListRequest($page, $limit);
    }

    /**
     * REST GET item
     *
     * @param string $id
     *
     * @ApiDoc(
     *     description="Get <%= EntityName %> item",
     *     resource=true
     * )
     * @AclAncestor("<%= bundle_name %>_<%= entity_name %>_view")
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * REST PUT
     *
     * @param int $id <%= EntityName %> item id
     *
     * @ApiDoc(
     *     description="Update <%= EntityName %>",
     *     resource=true
     * )
     * @AclAncestor("<%= bundle_name %>_<%= entity_name %>_update")
     * @return Response
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Create new <%= entity_name %>
     *
     * @ApiDoc(
     *     description="Create new <%= EntityName %>",
     *     resource=true
     * )
     * @AclAncestor("<%= bundle_name %>_<%= entity_name %>_create")
     */
    public function postAction()
    {
        return $this->handleCreateRequest();
    }

    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *     description="Delete <%= EntityName %>",
     *     resource=true
     * )
     * @AclAncestor("<%= bundle_name %>_<%= entity_name %>_delete")
     * @return Response
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
        return $this->get('<%= bundle_name %>_<%= entity_name %>.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('<%= bundle_name %>_<%= entity_name %>.form.entity.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->get('<%= bundle_name %>_<%= entity_name %>.form.handler.entity.api');
    }

    /**
     * {@inheritdoc}
     */
    protected function transformEntityField($field, &$value)
    {
        switch ($field) {
            case 'owner':
                //TODO add here any other entity
            default:
                parent::transformEntityField($field, $value);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function fixFormData(array &$data, $entity)
    {
        /** @var <%= EntityName %> $entity */
        parent::fixFormData($data, $entity);

        unset($data['id']);

        return true;
    }
}