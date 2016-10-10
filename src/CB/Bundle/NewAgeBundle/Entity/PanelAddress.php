<?php

namespace CB\Bundle\NewAgeBundle\Entity;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;

use Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;

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
class PanelAddress extends AbstractTypedAddress
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

//    /**
//     * @var Collection
//     *
//     * @ORM\ManyToMany(targetEntity="Oro\Bundle\AddressBundle\Entity\AddressType", cascade={"persist"})
//     * @ORM\JoinTable(
//     *     name="cb_newage_panel_addr_to_addr_type",
//     *     joinColumns={@ORM\JoinColumn(name="panel_address_id", referencedColumnName="id", onDelete="CASCADE")},
//     *     inverseJoinColumns={@ORM\JoinColumn(name="type_name", referencedColumnName="name")}
//     * )
//     * @Soap\ComplexType("string[]", nillable=true)
//     * @ConfigField(
//     *      defaultValues={
//     *          "importexport"={
//     *              "order"=200,
//     *              "short"=true
//     *          }
//     *      }
//     * )
//     **/
//    protected $types;

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

    /**
     * Set panel as owner.
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
     * Get address created date/time
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
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
}