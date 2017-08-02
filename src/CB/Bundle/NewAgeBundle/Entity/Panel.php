<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 16/Jun/16
 * Time: 14:05
 */

namespace CB\Bundle\NewAgeBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\AttachmentBundle\Entity\File;
use Oro\Bundle\DataAuditBundle\Metadata\Annotation as Oro;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(
 *      name="cb_newage_panel"
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      routeName="cb_newage_panel_index",
 *      routeView="cb_newage_panel_view",
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

class Panel
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
     * @ORM\Column(name="name", type="string", unique=true, length=255)
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
     * @var string
     *
     * @ORM\Column(name="neighborhoods", type="text", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $neighborhoods;

    /**
     * @var string
     *
     * @ORM\Column(name="dimensions", type="string", length=255, nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $dimensions;

    /**
     * @var SupportType
     *
     * @ORM\ManyToOne(targetEntity="CB\Bundle\NewAgeBundle\Entity\SupportType")
     * @ORM\JoinColumn(name="support_type_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *      }
     * )
     */
    protected $supportType;

    /**
     * @var LightingType
     *
     * @ORM\ManyToOne(targetEntity="CB\Bundle\NewAgeBundle\Entity\LightingType")
     * @ORM\JoinColumn(name="lighting_type_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *      }
     * )
     */
    protected $lightingType;

    /**
     * @var Collection
     *
     * @ORM\OneToMany(targetEntity="CB\Bundle\NewAgeBundle\Entity\PanelAddress",
     *    mappedBy="owner", cascade={"all"}, orphanRemoval=true
     * )
     * @ORM\OrderBy({"primary" = "DESC"})
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
    protected $addresses;

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

    /**
     * init addresses with empty collection
     */
    public function __construct()
    {
        $this->addresses = new ArrayCollection();
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
     * @return string
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * @param string $dimensions
     */
    public function setDimensions($dimensions)
    {
        $this->dimensions = $dimensions;
    }

    /**
     * @return string
     */
    public function getNeighborhoods()
    {
        return $this->neighborhoods;
    }

    /**
     * @param string $neighborhoods
     */
    public function setNeighborhoods($neighborhoods)
    {
        $this->neighborhoods = $neighborhoods;
    }

    /**
     * @param SupportType $supportType
     *
     * @return Panel
     */
    public function setSupportType($supportType)
    {
        $this->supportType = $supportType;

        return $this;
    }

    /**
     * @return SupportType
     */
    public function getSupportType()
    {
        return $this->supportType;
    }

    /**
     * @param SupportType $lightingType
     *
     * @return Panel
     */
    public function setLightingType($lightingType)
    {
        $this->lightingType = $lightingType;

        return $this;
    }

    /**
     * @return LightingType
     */
    public function getLightingType()
    {
        return $this->lightingType;
    }

    /**
     * Set addresses.
     *
     * This method could not be named setAddresses because of bug CRM-253.
     *
     * @param Collection|PanelAddress[] $addresses
     * @return Panel
     */
    public function resetAddresses($addresses)
    {
        $this->addresses->clear();

        foreach ($addresses as $address) {
            $this->addAddress($address);
        }

        return $this;
    }

    /**
     * Add address
     *
     * @param PanelAddress $address
     * @return Panel
     */
    public function addAddress(PanelAddress $address)
    {
        /** @var PanelAddress $address */
        if (!$this->addresses->contains($address)) {
            $this->addresses->add($address);
            $address->setOwner($this);
        }

        return $this;
    }

    /**
     * Remove address
     *
     * @param PanelAddress $address
     * @return Panel
     */
    public function removeAddress(PanelAddress $address)
    {
        if ($this->addresses->contains($address)) {
            $this->addresses->removeElement($address);
        }

        return $this;
    }

    /**
     * Get addresses
     *
     * @return Collection|PanelAddress[]
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @param PanelAddress $address
     * @return bool
     */
    public function hasAddress(PanelAddress $address)
    {
        return $this->getAddresses()->contains($address);
    }

    /**
     * Gets primary address if it's available.
     *
     * @return PanelAddress|null
     */
    public function getPrimaryAddress()
    {
        $result = null;

        /** @var PanelAddress $address */
        foreach ($this->getAddresses() as $address) {
            if ($address->isPrimary()) {
                $result = $address;
                break;
            }
        }

        return $result;
    }

    /**
     * @param PanelAddress $address
     *
     * @return Panel
     */
    public function setPrimaryAddress(PanelAddress $address)
    {
        if ($this->hasAddress($address)) {
            $address->setPrimary(true);
            /** @var PanelAddress $otherAddress */
            foreach ($this->getAddresses() as $otherAddress) {
                if (!$address->isEqual($otherAddress)) {
                    $otherAddress->setPrimary(false);
                }
            }
        }

        return $this;
    }

    /**
     * @param User $owningUser
     *
     * @return Panel
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
     * @return Panel
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
     * @return Panel
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
     * @return Panel
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
}