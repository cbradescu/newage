<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 16/Jun/16
 * Time: 11:22
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
 * @ORM\Entity(repositoryClass="CB\Bundle\NewAgeBundle\Entity\Repository\PanelViewRepository")
 * @ORM\Table(
 *      name="cb_newage_panel_view"
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Oro\Loggable
 * @Config(
 *      routeName="cb_newage_panel_view_index",
 *      routeView="cb_newage_panel_view_view",
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

class PanelView
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
     * @var Panel
     *
     * @ORM\ManyToOne(targetEntity="CB\Bundle\NewAgeBundle\Entity\Panel")
     * @ORM\JoinColumn(name="panel_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $panel;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $url;

    /**
     * @var ArrayCollection|SchedulerEvent[]
     *
     * @ORM\OneToMany(targetEntity="CB\Bundle\SchedulerBundle\Entity\SchedulerEvent", mappedBy="panelView", cascade={"persist"})
     */
    protected $events;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="CB\Bundle\NewAgeBundle\Entity\Offer", mappedBy="panelViews")
     * @ORM\JoinTable(name="cb_newage_offer_to_panel_view")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=240,
     *              "short"=true
     *          }
     *      }
     * )
     */
    protected $offers;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="CB\Bundle\NewAgeBundle\Entity\Reservation", mappedBy="reservedPanelViews")
     * @ORM\JoinTable(name="cb_newage_reservation_to_panel_view")
     */
    protected $reservations;

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


    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->offers = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getPanel()->getName(). ' ' . $this->name;
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
     * @return Panel
     */
    public function getPanel()
    {
        return $this->panel;
    }

    /**
     * @param Panel $panel
     */
    public function setPanel($panel)
    {
        $this->panel = $panel;
    }

    /**
     * Get offers collection
     *
     * @return Collection|Offer[]
     */
    public function getOffers()
    {
        return $this->offers;
    }

    /**
     * Add specified offer
     *
     * @param Offer $offer
     *
     * @return PanelView
     */
    public function addOffer(Offer $offer)
    {
        if (!$this->getOffers()->contains($offer)) {
            $this->getOffers()->add($offer);
            $offer->addPanelView($this);
        }

        return $this;
    }

    /**
     * Remove specified offer
     *
     * @param Offer $offer
     *
     * @return PanelView
     */
    public function removeOffer(Offer $offer)
    {
        if ($this->getOffers()->contains($offer)) {
            $this->getOffers()->removeElement($offer);
            $offer->removePanelView($this);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasOffers()
    {
        return count($this->offers) > 0;
    }

    /**
     * Get reservations collection
     *
     * @return Collection|Reservation[]
     */
    public function getReservations()
    {
        return $this->reservations;
    }

    /**
     * Add specified reservation
     *
     * @param Reservation $reservation
     *
     * @return PanelView
     */
    public function addReservation(Reservation $reservation)
    {
        if (!$this->getReservations()->contains($reservation)) {
            $this->getReservations()->add($reservation);
            $reservation->addPanelView($this);
        }

        return $this;
    }

    /**
     * Remove specified reservation
     *
     * @param Reservation $reservation
     *
     * @return PanelView
     */
    public function removeReservation(Reservation $reservation)
    {
        if ($this->getReservations()->contains($reservation)) {
            $this->getReservations()->removeElement($reservation);
            $reservation->removePanelView($this);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function hasReservations()
    {
        return count($this->reservations) > 0;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $name
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @param User $owningUser
     *
     * @return PanelView
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
     * @return Panelview
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

    public function getCity()
    {
        return $this->panel->getAddresses()->first()->getCity()->getName();
    }

    public function getSupport()
    {
        return $this->panel->getSupportType()->getName();
    }

    public function getLighting()
    {
        return $this->panel->getLightingType()->getName();
    }
}