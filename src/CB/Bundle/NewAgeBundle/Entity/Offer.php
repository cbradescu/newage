<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 14/Nov/16
 * Time: 14:51
 */

namespace CB\Bundle\NewAgeBundle\Entity;

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
 *      name="cb_newage_offer"
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Loggable
 * @Config(
 *      routeName="cb_newage_offer_index",
 *      routeView="cb_newage_offer_view",
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

class Offer
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $name;

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
     * @var Campaign
     *
     * @ORM\ManyToOne(targetEntity="CB\Bundle\NewAgeBundle\Entity\Campaign", inversedBy="events")
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $campaign;

    /**
     *
     * @var ArrayCollection $panelViews
     *
     * @ORM\ManyToMany(targetEntity="CB\Bundle\NewAgeBundle\Entity\PanelView", inversedBy="offers")
     * @ORM\JoinTable(name="cb_newage_offer_to_panel_view")
     * @ConfigField(
     *      defaultValues={
     *          "merge"={
     *              "display"=true
     *          },
     *          "importexport"={
     *              "order"=50,
     *              "short"=true
     *          }
     *      }
     * )
     */
    protected $panelViews;

    /**
     * @var Reservation
     *
     * @ORM\OneToOne(targetEntity="CB\Bundle\NewAgeBundle\Entity\Reservation", mappedBy="offer")
     */
    protected $reservation;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="CB\Bundle\NewAgeBundle\Entity\OfferItem",
     *    mappedBy="offer", cascade={"all"}, orphanRemoval=true
     * )
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "full"=true,
     *              "order"=250
     *          },
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $items;

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
     * @var WorkflowItem
     *
     * @ORM\OneToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowItem")
     * @ORM\JoinColumn(name="workflow_item_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $workflowItem;

    /**
     * @var WorkflowStep
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\WorkflowBundle\Entity\WorkflowStep")
     * @ORM\JoinColumn(name="workflow_step_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $workflowStep;


    public function __construct()
    {
        $this->start = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->end = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->end->modify('+5 days');

        $this->panelViews = new ArrayCollection();
        $this->items = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->name;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Gets date an offer begins.
     *
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Sets date an offer begins.
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
     * Gets date an offer ends.
     *
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Sets date an offer ends.
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
     * Gets campaign
     *
     * @return Campaign|null
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * Sets campaign
     *
     * @param Campaign $campaign
     *
     * @return Offer
     */
    public function setCampaign(Campaign $campaign = null)
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * Get items collection
     *
     * @return Collection
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Set items collection
     *
     * @param Collection $items
     *
     * @return Offer
     */
    public function setItems(Collection $items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Add specified item
     *
     * @param OfferItem $item
     *
     * @return Offer
     */
    public function addItem(OfferItem $item)
    {
        if (!$this->getItems()->contains($item)) {
            $this->getItems()->add($item);
        }

        return $this;
    }

    /**
     * Remove specified item
     *
     * @param OfferItem $item
     *
     * @return Offer
     */
    public function removeItem(OfferItem $item)
    {
        if ($this->getItems()->contains($item)) {
            $this->getItems()->removeElement($item);
        }

        return $this;
    }

    /**
     * Get panelViews collection
     *
     * @return Collection
     */
    public function getPanelViews()
    {
        return $this->panelViews;
    }

    /**
     * Add specified panelView
     *
     * @param PanelView $panelView
     *
     * @return Offer
     */
    public function addPanelView(PanelView $panelView)
    {
        if (!$this->getPanelViews()->contains($panelView)) {
            $this->getPanelViews()->add($panelView);
        }

        return $this;
    }

    /**
     * Set panelViews collection
     *
     * @param Collection $panelViews
     *
     * @return Offer
     */
    public function setPanelViews(Collection $panelViews)
    {
        $this->panelViews = $panelViews;

        return $this;
    }

    /**
     * Remove specified panelView
     *
     * @param PanelView $panelView
     *
     * @return Offer
     */
    public function removePanelView(PanelView $panelView)
    {
        if ($this->getPanelViews()->contains($panelView)) {
            $this->getPanelViews()->removeElement($panelView);
        }

        return $this;
    }

    /**
     * @return Reservation
     */
    public function getReservation()
    {
        return $this->reservation;
    }

    /**
     * @param Reservation $reservation
     *
     * @return Offer
     */
    public function setReservation($reservation)
    {
        $this->reservation = $reservation;

        return $this;
    }

    /**
     * @param User $owningUser
     *
     * @return Offer
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
     * @return Offer
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
     * @return Offer
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
     * @return Offer
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

    public function isGreaterThanStart($days)
    {
        $start = clone $this->start;
        return $this->end >= $start->modify('+' . $days. ' days');
    }
}