<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 07/Oct/16
 * Time: 11:41
 */

namespace CB\Bundle\NewAgeBundle\Controller\Api\Rest;

use CB\Bundle\NewAgeBundle\Entity\LightingType;
use Symfony\Component\HttpFoundation\Response;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;

/**
 * @Rest\RouteResource("lighting_type")
 * @Rest\NamePrefix("cb_newage_lighting_type_api_")
 */
class LightingTypeController extends RestController implements ClassResourceInterface
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
     *     description="Get all LightingType items",
     *     resource=true
     * )
     * @AclAncestor("cb_newage_lighting_type_view")
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
     *     description="Get LightingType item",
     *     resource=true
     * )
     * @AclAncestor("cb_newage_lighting_type_view")
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * REST PUT
     *
     * @param int $id LightingType item id
     *
     * @ApiDoc(
     *     description="Update LightingType",
     *     resource=true
     * )
     * @AclAncestor("cb_newage_lighting_type_update")
     * @return Response
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Create new lighting_type
     *
     * @ApiDoc(
     *     description="Create new LightingType",
     *     resource=true
     * )
     * @AclAncestor("cb_newage_lighting_type_create")
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
     *     description="Delete LightingType",
     *     resource=true
     * )
     * @AclAncestor("cb_newage_lighting_type_delete")
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
        return $this->get('cb_newage_lighting_type.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('cb_newage_lighting_type.form.entity.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->get('cb_newage_lighting_type.form.handler.entity.api');
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
        /** @var LightingType $entity */
        parent::fixFormData($data, $entity);

        unset($data['id']);

        return true;
    }
}