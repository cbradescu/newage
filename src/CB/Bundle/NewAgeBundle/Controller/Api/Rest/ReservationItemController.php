<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 14/Nov/16
 * Time: 14:51
 */

namespace CB\Bundle\NewAgeBundle\Controller\Api\Rest;

use CB\Bundle\NewAgeBundle\Entity\ReservationItem;
use Symfony\Component\HttpFoundation\Response;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;

/**
 * @Rest\RouteResource("reservation_item")
 * @Rest\NamePrefix("cb_newage_reservation_item_api_")
 */
class ReservationItemController extends RestController implements ClassResourceInterface
{
    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *     description="Delete ReservationItem",
     *     resource=true
     * )
     * @AclAncestor("cb_newage_reservation_item_delete")
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
        return $this->get('cb_newage_reservation_item.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->get('cb_newage_reservation_item.form.handler.entity.api');
    }

    /**
     * {@inheritDoc}
     */
    protected function fixFormData(array &$data, $entity)
    {
        /** @var ReservationItem $entity */
        parent::fixFormData($data, $entity);

        unset($data['id']);

        return true;
    }
}