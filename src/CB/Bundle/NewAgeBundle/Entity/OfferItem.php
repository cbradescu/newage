<?php
namespace CB\Bundle\NewAgeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

/**
 * @ORM\Entity(repositoryClass="CB\Bundle\NewAgeBundle\Entity\Repository\OfferItemRepository")
 * @ORM\Table(
 *      name="cb_newage_offer_item"
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      routeName="cb_newage_offer_item_index",
 *      routeView="cb_newage_offer_item_view",
 *      defaultValues={
 *          "dataaudit"={
 *              "auditable"=true
 *          },
 *          "entity"={
 *              "icon"="icon-list-alt"
 *          },
 *          "ownership"={
 *              "owner_type"="USER",
 *              "owner_field_name"="owner",
 *              "owner_column_name"="owner_id",
 *              "organization_field_name"="organization",
 *              "organization_column_name"="organization_id"
 *          },
 *          "security"={
 *              "type"="ACL"
 *          }
 *      }
 * )
 */

class OfferItem extends Item
{
    /**
     * @var PanelView
     *
     * @ORM\ManyToOne(targetEntity="CB\Bundle\NewAgeBundle\Entity\PanelView", inversedBy="offerItems", cascade={"persist"})
     * @ORM\JoinColumn(name="panel_view_id", referencedColumnName="id", onDelete="CASCADE")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $panelView;

    /**
     * @ORM\ManyToOne(targetEntity="Offer", inversedBy="offerItems", cascade={"persist"})
     * @ORM\JoinColumn(name="offer_id", referencedColumnName="id", onDelete="CASCADE")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $offer;

    /**
     * @var ReservationItem
     *
     * @ORM\OneToMany(targetEntity="ReservationItem",
     *    mappedBy="offerItem", cascade={"all"}, orphanRemoval=true
     * )
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $reservationItems;


    public function __construct()
    {
        $this->reservationItems = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->panelView->getName();
    }

    /**
     * Gets panelView
     *
     * @return PanelView|null
     */
    public function getPanelView()
    {
        return $this->panelView;
    }

    /**
     * Sets panelView
     *
     * @param PanelView $panelView
     *
     * @return Item
     */
    public function setPanelView(PanelView $panelView = null)
    {
        $this->panelView = $panelView;

        return $this;
    }

    /**
     * @return Offer
     */
    public function getOffer()
    {
        return $this->offer;
    }

    /**
     * @param Offer $offer
     *
     * @return OfferItem
     */
    public function setOffer($offer)
    {
        $this->offer = $offer;

        return $this;
    }

    /**
     * Get reservationItems collection
     *
     * @return Collection
     */
    public function getReservationItems()
    {
        return $this->reservationItems;
    }

    /**
     * Set reservationItems collection
     *
     * @param Collection $reservationItems
     *
     * @return OfferItem
     */
    public function setReservationItems(Collection $reservationItems)
    {
        $this->reservationItems = $reservationItems;

        return $this;
    }

    /**
     * Add specified reservationItem
     *
     * @param ReservationItem $reservationItem
     *
     * @return OfferItem
     */
    public function addReservationItem(ReservationItem $reservationItem)
    {
        if (!$this->getReservationItems()->contains($reservationItem)) {
            $this->getReservationItems()->add($reservationItem);
        }

        return $this;
    }

    /**
     * Remove specified reservationItem
     *
     * @param ReservationItem $reservationItem
     *
     * @return OfferItem
     */
    public function removeReservationItem(ReservationItem $reservationItem)
    {
        if ($this->getReservationItems()->contains($reservationItem)) {
            $this->getReservationItems()->removeElement($reservationItem);
        }

        return $this;
    }
}