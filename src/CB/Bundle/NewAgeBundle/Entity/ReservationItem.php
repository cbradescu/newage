<?php
namespace CB\Bundle\NewAgeBundle\Entity;

use CB\Bundle\SchedulerBundle\Entity\SchedulerEvent;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

/**
 * @ORM\Entity(repositoryClass="CB\Bundle\NewAgeBundle\Entity\Repository\ReservationItemRepository")
 * @ORM\Table(
 *      name="cb_newage_reservation_item"
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      routeName="cb_newage_reservation_item_index",
 *      routeView="cb_newage_reservation_item_view",
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

class ReservationItem extends Item
{
    /**
     * @var OfferItem
     *
     * @ORM\ManyToOne(targetEntity="OfferItem", inversedBy="reservationItems", cascade={"persist"})
     * @ORM\JoinColumn(name="offer_item_id", referencedColumnName="id", onDelete="CASCADE")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $offerItem;

    /**
     * @var SchedulerEvent
     *
     * @ORM\OneToMany(targetEntity="CB\Bundle\SchedulerBundle\Entity\SchedulerEvent",
     *    mappedBy="reservationItem", cascade={"all"}, orphanRemoval=true
     * )
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->panelView->getName();
    }


    /**
     * @return mixed
     */
    public function getOfferItem()
    {
        return $this->offerItem;
    }

    /**
     * @param mixed $offerItem
     */
    public function setOfferItem($offerItem)
    {
        $this->offerItem = $offerItem;
    }

    /**
     * Get events collection
     *
     * @return Collection
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Set events collection
     *
     * @param Collection $events
     *
     * @return ReservationItem
     */
    public function setEvents(Collection $events)
    {
        $this->events = $events;

        return $this;
    }

    /**
     * Add specified event
     *
     * @param SchedulerEvent $event
     *
     * @return ReservationItem
     */
    public function addEvent(SchedulerEvent $event)
    {
        if (!$this->getEvents()->contains($event)) {
            $this->getEvents()->add($event);
        }

        return $this;
    }

    /**
     * Remove specified event
     *
     * @param SchedulerEvent $event
     *
     * @return ReservationItem
     */
    public function removeEvent(SchedulerEvent $event)
    {
        if ($this->getEvents()->contains($event)) {
            $this->getEvents()->removeElement($event);
        }

        return $this;
    }

    /**
     * Adds a default event for current ReservationItem.
     * Has same start and end date as reservation item.
     *
     * @return ReservationItem
     */
    public function addDefaultEvent()
    {
        $event = new SchedulerEvent();
        $event->setReservationItem($this);
        $event->setStart($this->getStart());
        $event->setEnd($this->getEnd());
        $event->setStatus(SchedulerEvent::RESERVED);

        $this->addEvent($event);

        return $this;
    }

    public function removeAllEvents()
    {
        foreach ($this->events as $event)
        {
            $this->removeEvent($event);
        }

        return $this;
    }
}