<?php

namespace CB\Bundle\SchedulerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use CB\Bundle\SchedulerBundle\Model\ExtendScheduler;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use CB\Bundle\NewAgeBundle\Entity\Campaign;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * @ORM\Entity(repositoryClass="CB\Bundle\SchedulerBundle\Entity\Repository\SchedulerRepository")
 * @ORM\Table(name="cb_scheduler")
 * @Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-scheduler"
 *          },
 *          "ownership"={
 *              "owner_type"="ORGANIZATION",
 *              "owner_field_name"="organization",
 *              "owner_column_name"="organization_id"
 *          },
 *          "security"={
 *              "type"="ACL",
 *              "group_name"=""
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
 */
class Scheduler extends ExtendScheduler
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $id;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @var Campaign
     *
     * @ORM\ManyToOne(targetEntity="CB\Bundle\NewAgeBundle\Entity\Campaign")
     * @ORM\JoinColumn(name="campaign_owner_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $owner;

    /**
     * @var ArrayCollection|SchedulerEvent[]
     *
     * @ORM\OneToMany(targetEntity="SchedulerEvent", mappedBy="scheduler", cascade={"persist"})
     */
    protected $events;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Oro\Bundle\OrganizationBundle\Entity\Organization")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $organization;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->events = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return empty($this->name)
            ? ($this->owner ? (string)$this->owner : '[default]')
            : $this->name;
    }

    /**
     * Gets the scheduler id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets scheduler name.
     * Usually user's default scheduler has no name and this method returns null.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets scheduler name.
     *
     * @param string|null $name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets all events of this scheduler.
     *
     * @return SchedulerEvent[]
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * Adds an event to this scheduler.
     *
     * @param  SchedulerEvent $event
     *
     * @return self
     */
    public function addEvent(SchedulerEvent $event)
    {
        $this->events[] = $event;

        $event->setScheduler($this);

        return $this;
    }

    /**
     * Gets owning campaign for this calendar
     *
     * @return Campaign
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Sets owning campaign for this calendar
     *
     * @param Campaign $owningCampaign
     *
     * @return self
     */
    public function setCampaign($owningCampaign)
    {
        $this->owner = $owningCampaign;

        return $this;
    }

    /**
     * Sets owning organization
     *
     * @param Organization $organization
     *
     * @return self
     */
    public function setOrganization(Organization $organization = null)
    {
        $this->organization = $organization;

        return $this;
    }

    /**
     * Gets owning organization
     *
     * @return Organization
     */
    public function getOrganization()
    {
        return $this->organization;
    }
}
