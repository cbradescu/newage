<?php
namespace CB\Bundle\NewAgeBundle\Entity;

use CB\Bundle\SchedulerBundle\Entity\SchedulerEvent;

use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="cb_newage_reservation_item"
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Loggable
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

class ReservationItem
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Offer", inversedBy="reservationItems", cascade={"persist"})
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
     * @var PanelVIew
     *
     * @ORM\ManyToOne(targetEntity="CB\Bundle\NewAgeBundle\Entity\PanelVIew")
     * @ORM\JoinColumn(name="panel_view_id", referencedColumnName="id")
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

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_at", type="date")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $start;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_at", type="date")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $end;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_owner_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *      }
     * )
     */
    protected $owner;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.created_at"
     *          }
     *      }
     * )
     */
    protected $createdAt;

    /**
     * @var \DateTime $updatedAt
     *
     * @ORM\Column(type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.updated_at"
     *          }
     *      }
     * )
     */
    protected $updatedAt;


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
     * @return string
     */
    public function getId()
    {
        return $this->id;
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
     * Remove all events from collection
     *
     * @return ReservationItem
     */

    public function removeAllEvents()
    {


        return $this;
    }


    /**
     * Gets date an reservation item begins.
     *
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Sets date an reservation item begins.
     *
     * @param \DateTime $start
     *
     * @return self
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Gets date an reservation item ends.
     *
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Sets date an reservation item ends.
     *
     * @param \DateTime $end
     *
     * @return self
     */
    public function setEnd($end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Gets panelView
     *
     * @return PanelVIew|null
     */
    public function getPanelVIew()
    {
        return $this->panelView;
    }

    /**
     * Sets panelView
     *
     * @param PanelVIew $panelVIew
     *
     * @return ReservationItem
     */
    public function setPanelVIew(PanelVIew $panelVIew = null)
    {
        $this->panelView = $panelVIew;

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
     * @return ReservationItem
     */
    public function setOffer($offer)
    {
        $this->offer = $offer;

        return $this;
    }

    /**
     * @param User $owningUser
     *
     * @return ReservationItem
     */
    public function setOwner($owningUser)
    {
        $this->owner = $owningUser;

        return $this;
    }

    /**
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set organization
     *
     * @param Organization $organization
     * @return ReservationItem
     */
    public function setOrganization(Organization $organization = null)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Get organization
     *
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }

    /**
     * Get contact last update date/time
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get panel create date/time
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = $this->createdAt ? $this->createdAt : new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updatedAt = clone $this->createdAt;
    }

    /**
     * Pre update event handler
     *
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * Adds a default event for current ReservationItem.
     * Has same start and end date as reservation item.
     *
     * @return ReservationItem
     */
    public function addDefaultEvent()
    {
        $this->removeAllEvents();

        $event = new SchedulerEvent();
        $event->setCampaign($this->getOffer()->getCampaign());
        $event->setPanelView($this->getPanelView());
        $event->setReservationItem($this);
        $event->setStart($this->getStart());
        $event->setEnd($this->getEnd());
        $event->setStatus(SchedulerEvent::RESERVED);

        $this->addEvent($event);

        return $this;
    }
}