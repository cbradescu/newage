<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 14/Nov/16
 * Time: 14:51
 */

namespace CB\Bundle\NewAgeBundle\Controller\Api\Rest;

use CB\Bundle\NewAgeBundle\Entity\Offer;
use CB\Bundle\NewAgeBundle\Entity\OfferItem;
use CB\Bundle\NewAgeBundle\Entity\ReservationItem;
use Symfony\Component\HttpFoundation\Response;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;

use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\SoapBundle\Controller\Api\Rest\RestController;

/**
 * @Rest\RouteResource("offer_item")
 * @Rest\NamePrefix("cb_newage_offer_item_api_")
 */
class OfferItemController extends RestController implements ClassResourceInterface
{
    /**
     * REST DELETE
     *
     * @param int $id
     *
     * @ApiDoc(
     *     description="Delete OfferItem",
     *     resource=true
     * )
     * @AclAncestor("cb_newage_offer_item_delete")
     * @return Response
     */
    public function deleteAction($id)
    {
//        /** @var OfferItem $offerItem */
//        $offerItem = $this->getManager()->find($id);
//
//        /** @var Offer $offer */
//        $offer = $offerItem->getOffer();
//
//        /** @var ReservationItem $reservationItem */
//        foreach ($offer->getReservationItems() as $reservationItem)
//        {
//            if ($reservationItem->getPanelView()->getId() == $offerItem->getPanelView()->getId() &&
//                $reservationItem->getStart() == $offerItem->getStart() && $reservationItem->getEnd() == $offerItem->getEnd()) {
//
//                if ($offer->getReservationItems()->contains($reservationItem)) {
//                    $offer->removeReservationItem($reservationItem);
//                }
//            }
//        }
//
//        if ($offer->getOfferItems()->contains($offerItem)) {
//            $offer->removeOfferItem($offerItem);
//            $offer->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
//
//            return $this->handleDeleteRequest($offerItem);
//        } else {
//            return $this->handleView($this->view(null, Codes::HTTP_NOT_FOUND));
//        }

        return $this->handleDeleteRequest($id);
    }

    /**
     * {@inheritdoc}
     */
    public function getManager()
    {
        return $this->get('cb_newage_offer_item.manager.api');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormHandler()
    {
        return $this->get('cb_newage_offer_item.form.handler.entity.api');
    }

    /**
     * {@inheritDoc}
     */
    protected function fixFormData(array &$data, $entity)
    {
        /** @var OfferItem $entity */
        parent::fixFormData($data, $entity);

        unset($data['id']);

        return true;
    }
}