<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 23/Nov/16
 * Time: 12:43
 */

namespace CB\Bundle\NewAgeBundle\Controller\Api\Rest;

use CB\Bundle\NewAgeBundle\Entity\Reservation;
use CB\Bundle\NewAgeBundle\Entity\PanelView;
use Symfony\Component\HttpFoundation\Response;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Util\Codes;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;

/**
 * @Rest\RouteResource("reservation")
 * @Rest\NamePrefix("cb_newage_reservation_api_")
 */
class ReservationController extends RestController implements ClassResourceInterface
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
     *     description="Get all Reservation items",
     *     resource=true
     * )
     * @AclAncestor("cb_newage_reservation_view")
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
     *     description="Get Reservation item",
     *     resource=true
     * )
     * @AclAncestor("cb_newage_reservation_view")
     * @return Response
     */
    public function getAction($id)
    {
        return $this->handleGetRequest($id);
    }

    /**
     * REST PUT
     *
     * @param int $id Reservation item id
     *
     * @ApiDoc(
     *     description="Update Reservation",
     *     resource=true
     * )
     * @AclAncestor("cb_newage_reservation_update")
     * @return Response
     */
    public function putAction($id)
    {
        return $this->handleUpdateRequest($id);
    }

    /**
     * Create new reservation
     *
     * @ApiDoc(
     *     description="Create new Reservation",
     *     resource=true
     * )
     * @AclAncestor("cb_newage_reservation_create")
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
     *     description="Delete Reservation",
     *     resource=true
     * )
     * @AclAncestor("cb_newage_reservation_delete")
     * @return Response
     */
    public function deleteAction($id)
    {
        return $this->handleDeleteRequest($id);
    }

    /**
     * REST DELETE
     *
     * @param int $rid
     * @param int $id
     *
     * @ApiDoc(
     *     description="Removes a Panel View from Reservation",
     *     resource=true
     * )
     * @AclAncestor("cb_newage_reservation_update")
     * @return Response
     */
    public function deletePviewAction($rid, $id)
    {
        /** @var Reservation $reservation */
        $reservation = $this->getManager()->find($rid);

        /** @var PanelView $panelView */
        $panelView = $this->get('cb_newage_panel_view.manager.api')->find($id);

        // Remove coresponding event
        $em = $this->getManager()->getObjectManager();
        $event = $em->getRepository('CBSchedulerBundle:SchedulerEvent')->findOneBy(
            [
                'reservation'   => $reservation->getId(),
                'panelView'     => $panelView->getId()
            ]
        );

        $reservation->removeReservedPanelView($panelView);
        $em->remove($event);
        $em->flush();

        return $this->buildResponse(
            $this->view(null, Codes::HTTP_NO_CONTENT),
            self::ACTION_DELETE,
            [
                'id' => $id,
                'success' => true
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('cb_newage_reservation.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getForm()
    {
        return $this->get('cb_newage_reservation.form.entity.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->get('cb_newage_reservation.form.handler.entity.api');
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
        /** @var Reservation $entity */
        parent::fixFormData($data, $entity);

        unset($data['id']);

        return true;
    }
}