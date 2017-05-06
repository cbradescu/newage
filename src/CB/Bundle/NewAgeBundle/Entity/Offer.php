<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 14/Nov/16
 * Time: 14:51
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

/**
 * @ORM\Entity(repositoryClass="CB\Bundle\NewAgeBundle\Entity\Repository\OfferRepository")
 * @ORM\Table(
 *      name="cb_newage_offer"
 * )
 * @ORM\HasLifecycleCallbacks()
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
     * @var Client
     *
     * @ORM\ManyToOne(targetEntity="CB\Bundle\NewAgeBundle\Entity\Client", inversedBy="events")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $client;

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
    protected $offerItems;

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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="created_by_user_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $createdBy;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="updated_by_user_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $updatedBy;

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
        $this->start = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->end = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->end->modify('+7 days');

        $this->offerItems = new ArrayCollection();
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
     * Gets client
     *
     * @return Client|null
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Sets client
     *
     * @param Client $client
     *
     * @return Offer
     */
    public function setClient(Client $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get offerItems collection
     *
     * @return Collection
     */
    public function getOfferItems()
    {
        return $this->offerItems;
    }

    /**
     * Set offerItems collection
     *
     * @param Collection $items
     *
     * @return Offer
     */
    public function setOfferItems(Collection $items)
    {
        $this->offerItems = $items;

        return $this;
    }

    /**
     * Add specified offerItem
     *
     * @param OfferItem $item
     *
     * @return Offer
     */
    public function addOfferItem(OfferItem $item)
    {
        if (!$this->getOfferItems()->contains($item)) {
            $this->getOfferItems()->add($item);
        }

        return $this;
    }

    /**
     * Remove specified offerItem
     *
     * @param OfferItem $item
     *
     * @return Offer
     */
    public function removeOfferItem(OfferItem $item)
    {
        if ($this->getOfferItems()->contains($item)) {
            $this->getOfferItems()->removeElement($item);
        }

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
     * @param User $createdBy
     *
     * @return Offer
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param User $updatedBy
     *
     * @return Offer
     */
    public function setUpdatedBy($updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * @return User
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
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

    public function isGreaterThanStart($days)
    {
        $start = clone $this->start;
        return $this->end >= $start->modify('+' . $days . ' days');
    }

    public function hasConfirmedItems()
    {
        /** @var ReservationItem $ri */
        foreach ($this->getOfferItems() as $oi) {
            foreach ($oi->getReservationItems() as $ri) {
                /** @var SchedulerEvent $event */
                foreach ($ri->getEvents() as $event) {
                    if ($event->getStatus() == SchedulerEvent::CONFIRMED)
                        return true;
                }
            }
        }

        return false;
    }

    public function hasReservationItems()
    {
        /** @var ReservationItem $ri */
        foreach ($this->getOfferItems() as $oi) {
            foreach ($oi->getReservationItems() as $ri) {
                /** @var SchedulerEvent $event */
                foreach ($ri->getEvents() as $event) {
                    if ($event->getStatus() == SchedulerEvent::RESERVED)
                        return true;
                }
            }
        }

        return false;
    }
}