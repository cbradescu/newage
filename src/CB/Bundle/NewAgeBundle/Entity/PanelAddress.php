<?php

namespace CB\Bundle\NewAgeBundle\Entity;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Mapping as ORM;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\FormBundle\Entity\PrimaryItem;
/**
 * @ORM\Table("cb_newage_panel_address")
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *       defaultValues={
 *          "entity"={
 *              "icon"="icon-map-marker"
 *          },
 *          "note"={
 *              "immutable"=true
 *          },
 *          "activity"={
 *              "immutable"=true
 *          },
 *          "attachment"={
 *              "immutable"=true
 *          }
 *      }
 * )
 * @ORM\Entity
 */
class PanelAddress implements PrimaryItem
{
    /**
     * @ORM\ManyToOne(targetEntity="Panel", inversedBy="addresses", cascade={"persist"})
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", onDelete="CASCADE")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $owner;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $id;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_primary", type="boolean", nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $primary;

    /**
     * @var string
     *
     * @ORM\Column(name="street", type="string", length=500, nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=80,
     *              "identity"=true
     *          }
     *      }
     * )
     */
    protected $street;

    /**
     * @var string
     *
     * @ORM\Column(name="street2", type="string", length=500, nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "importexport"={
     *              "order"=90
     *          }
     *      }
     * )
     */
    protected $street2;

    /**
     * @var City
     *
     * @ORM\ManyToOne(targetEntity="CB\Bundle\NewAgeBundle\Entity\City")
     * @ORM\JoinColumn(name="city_id", referencedColumnName="id", onDelete="SET NULL")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          },
     *      }
     * )
     */
    protected $city;

    /**
     * @var \DateTime $created
     *
     * @ORM\Column(type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.created_at"
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $created;

    /**
     * @var \DateTime $updated
     *
     * @ORM\Column(type="datetime")
     * @ConfigField(
     *      defaultValues={
     *          "entity"={
     *              "label"="oro.ui.updated_at"
     *          },
     *          "importexport"={
     *              "excluded"=true
     *          }
     *      }
     * )
     */
    protected $updated;

    /**
     * @var string
     *
     * @ORM\Column(name="latitude", type="decimal", precision=15, scale=12)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $latitude;

    /**
     * @var string
     *
     * @ORM\Column(name="longitude", type="decimal", precision=15, scale=12)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $longitude;


    public function __construct()
    {
        $this->primary = false;
    }

    /**
     * Set contact as owner.
     *
     * @param Panel $owner
     */
    public function setOwner(Panel $owner = null)
    {
        $this->owner = $owner;
    }

    /**
     * Get owner panel.
     *
     * @return Panel
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param int $id
     * @return PanelAddress
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set street
     *
     * @param string $street
     * @return PanelAddress
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set street2
     *
     * @param string $street2
     * @return PanelAddress
     */
    public function setStreet2($street2)
    {
        $this->street2 = $street2;

        return $this;
    }

    /**
     * Get street2
     *
     * @return string
     */
    public function getStreet2()
    {
        return $this->street2;
    }

    /**
     * Set city
     *
     * @param City $city
     * @return PanelAddress
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return City
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Get address created date/time
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set address created date/time
     *
     * @param \DateTime $created
     * @return PanelAddress
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get address last update date/time
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set address updated date/time
     *
     * @param \DateTime $updated
     * @return PanelAddress
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Pre persist event listener
     *
     * @ORM\PrePersist
     */
    public function beforeSave()
    {
        $this->created = new \DateTime('now', new \DateTimeZone('UTC'));
        $this->updated = new \DateTime('now', new \DateTimeZone('UTC'));
    }

    /**
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param float $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param float $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * Convert address to string
     * @todo: Address format must be used here
     *
     * @return string
     */
    public function __toString()
    {
        $data = array(
            $this->getStreet(),
            $this->getStreet2(),
            $this->getCity()
        );

        $str = implode(' ', $data);
        $check = trim(str_replace(',', '', $str));
        return empty($check) ? '' : $str;
    }

    /**
     * @param bool $primary
     * @return PanelAddress
     */
    public function setPrimary($primary)
    {
        $this->primary = (bool)$primary;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPrimary()
    {
        return (bool)$this->primary;
    }

    /**
     * Check if entity is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->street)
        && empty($this->street2)
        && empty($this->city)
        && !$this->primary;
    }

    /**
     * @param mixed $other
     * @return bool
     */
    public function isEqual($other)
    {
        $class = ClassUtils::getClass($this);

        if (!$other instanceof $class) {
            return false;
        }

        /** @var PanelAddress $other */
        if ($this->getId() && $other->getId()) {
            return $this->getId() == $other->getId();
        }

        if ($this->getId() || $other->getId()) {
            return false;
        }

        return $this === $other;
    }
}