<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 23/Nov/16
 * Time: 12:43
 */

namespace CB\Bundle\NewAgeBundle\Entity;

use CB\Bundle\SchedulerBundle\Entity\SchedulerEvent;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="cb_newage_reservation"
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Loggable
 * @Config(
 *      routeName="cb_newage_reservation_index",
 *      routeView="cb_newage_reservation_view",
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

class Reservation
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
     * @var Offer
     *
     * @ORM\OneToOne(targetEntity="CB\Bundle\NewAgeBundle\Entity\Offer", inversedBy="reservation", cascade={"persist"})
     * @ORM\JoinColumn(name="offer_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $offer;

    /**
     * @var ArrayCollection $reservedPanelViews
     *
     * @ORM\ManyToMany(targetEntity="CB\Bundle\NewAgeBundle\Entity\PanelView", inversedBy="reservations")
     * @ORM\JoinTable(name="cb_newage_reservation_to_panel_view")
     */
    protected $reservedPanelViews;

    /**
     * @var ArrayCollection|SchedulerEvent[]
     *
     * @ORM\OneToMany(targetEntity="CB\Bundle\SchedulerBundle\Entity\SchedulerEvent", mappedBy="reservation", cascade={"persist"})
     */
    protected $events;

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

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->offer->getName();
    }

    public function __construct()
    {
        $this->reservedPanelViews = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Offer|null $offer
     *
     * @return Reservation
     */
    public function setOffer(Offer $offer)
    {
        $offer->setReservation($this);
        $this->offer = $offer;

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
     * Get reservedPanelViews collection
     *
     * @return Collection
     */
    public function getReservedPanelViews()
    {
        return $this->reservedPanelViews;
    }

    /**
     * Add specified panelView
     *
     * @param PanelView $panelView
     *
     * @return Reservation
     */
    public function addReservedPanelView(PanelView $panelView)
    {
        if (!$this->getReservedPanelViews()->contains($panelView)) {
            $this->getReservedPanelViews()->add($panelView);
        }

        return $this;
    }

    /**
     * Set reservedPanelViews collection
     *
     * @param Collection $panelViews
     *
     * @return Reservation
     */
    public function setReservedPanelViews(Collection $panelViews)
    {
        $this->reservedPanelViews = $panelViews;

        foreach ($panelViews as $panelView)
        {
            // If event does not exists with current Panel View attributes we add it.
            if (!$this->findEventBy($this->getAttributes($panelView))) {
                $event = new SchedulerEvent();
                $event->setPanelView($panelView);
                $event->setStart($this->getOffer()->getStart());
                $event->setEnd($this->getOffer()->getEnd());
                $event->setCampaign($this->getOffer()->getCampaign());
                $event->setStatus(SchedulerEvent::RESERVED);
                $event->setReservation($this);

                $this->addEvent($event);
            }
        }

        return $this;
    }

    /**
     * Remove specified panelView
     *
     * @param PanelView $panelView
     *
     * @return Reservation
     */
    public function removeReservedPanelView(PanelView $panelView)
    {
        if ($this->getReservedPanelViews()->contains($panelView)) {
            $this->getReservedPanelViews()->removeElement($panelView);

            // After removing Panel View, we also remove the event.
            $event = $this->findEventBy($this->getAttributes($panelView));
            if ($event)
                $this->removeEvent($event);
        }

        return $this;
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
     * Add specified event
     *
     * @param SchedulerEvent $event
     *
     * @return Reservation
     */
    public function addEvent(SchedulerEvent $event)
    {
        if (!$this->getEvents()->contains($event)) {
            $this->getEvents()->add($event);
        }

        return $this;
    }

    /**
     * Set events collection
     *
     * @param Collection $events
     *
     * @return Reservation
     */
    public function setEvents(Collection $events)
    {
        $this->events = $events;

        return $this;
    }

    /**
     * Remove specified event
     *
     * @param SchedulerEvent $event
     *
     * @return Reservation
     */
    public function removeEvent(SchedulerEvent $event)
    {
        if ($this->getEvents()->contains($event)) {
            $this->getEvents()->removeElement($event);
        }

        return $this;
    }

    /**
     * @param User $owningUser
     *
     * @return Reservation
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
     * @return Reservation
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
     * @param  WorkflowItem $workflowItem
     * @return Reservation
     */
    public function setWorkflowItem($workflowItem)
    {
        $this->workflowItem = $workflowItem;

        return $this;
    }

    /**
     * @return WorkflowItem
     */
    public function getWorkflowItem()
    {
        return $this->workflowItem;
    }

    /**
     * @param  WorkflowItem $workflowStep
     * @return Reservation
     */
    public function setWorkflowStep($workflowStep)
    {
        $this->workflowStep = $workflowStep;

        return $this;
    }

    /**
     * @return WorkflowStep
     */
    public function getWorkflowStep()
    {
        return $this->workflowStep;
    }

    /**
     * @param array $attributes
     * @return SchedulerEvent|mixed|null
     */
    public function findEventBy($attributes)
    {
        foreach ($this->events as $event)
        {
            $hasAttribute = true;
            foreach ($attributes as $name => $value)
            {
                if (call_user_func( [$event, 'get' . ucfirst($name)]) != $value)
                    $hasAttribute = false;
            }

            if ($hasAttribute)
                return $event;
        }

        return null;
    }

    public function getAttributes(PanelView $panelView)
    {
        return [
            'panelView' => $panelView,
            'campaign' => $this->getOffer()->getCampaign(),
            'start' => $this->getOffer()->getStart(),
            'end' => $this->getOffer()->getEnd(),
            'reservation' => $this
        ];
    }

    public function hasReservedPanelView(PanelView $panelView)
    {
        return $this->getReservedPanelViews()->contains($panelView);
    }
}