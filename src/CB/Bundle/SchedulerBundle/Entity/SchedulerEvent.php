<?php

namespace CB\Bundle\SchedulerBundle\Entity;

use CB\Bundle\NewAgeBundle\Entity\Campaign;
use Doctrine\ORM\Mapping as ORM;

use CB\Bundle\SchedulerBundle\Model\ExtendSchedulerEvent;
use CB\Bundle\NewAgeBundle\Entity\PanelView;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;

use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="CB\Bundle\SchedulerBundle\Entity\Repository\SchedulerEventRepository")
 * @ORM\Table(
 *      name="cb_scheduler_event",
 *      indexes={
 *          @ORM\Index(name="cb_scheduler_event_idx", columns={"id", "start_at", "end_at"}),
 *          @ORM\Index(name="cb_scheduler_event_up_idx", columns={"updated_at"})
 *      }
 * )
 * @ORM\HasLifecycleCallbacks()
 * @Config(
 *      routeName="cb_scheduler_view",
 *      routeView="cb_scheduler_event_view",
 *      defaultValues={
 *          "dataaudit"={
 *              "auditable"=true
 *          },
 *          "entity"={
 *              "icon"="icon-time"
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
class SchedulerEvent extends ExtendSchedulerEvent implements DatesAwareInterface
{
    use DatesAwareTrait;

    const OFFERED  = 'offered';
    const RESERVED = 'reserved';
    const ACCEPTED = 'accepted';

    protected $statuses = [
        SchedulerEvent::OFFERED,
        SchedulerEvent::RESERVED,
        SchedulerEvent::ACCEPTED
    ];

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var PanelView
     *
     * @ORM\ManyToOne(targetEntity="CB\Bundle\NewAgeBundle\Entity\PanelView", inversedBy="events")
     * @ORM\JoinColumn(name="panel_view_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
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
     * @var \DateTime
     *
     * @ORM\Column(name="start_at", type="datetime")
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
     * @ORM\Column(name="end_at", type="datetime")
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
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=32, nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $status;

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
        parent::__construct();

        $this->status = SchedulerEvent::OFFERED;
    }

    /**
     * Gets an scheduler event id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets panel view
     *
     * @return PanelView View|null
     */
    public function getPanelView()
    {
        return $this->panelView;
    }

    /**
     * Sets panel view
     *
     * @param PanelView $panelView
     *
     * @return self
     */
    public function setPanelView(PanelView $panelView = null)
    {
        $this->panelView = $panelView;

        return $this;
    }

    /**
     * Gets owning campaign
     *
     * @return Campaign|null
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * Sets owning campaign
     *
     * @param Campaign $campaign
     *
     * @return self
     */
    public function setCampaign(Campaign $campaign = null)
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * Gets date/time an event begins.
     *
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Sets date/time an event begins.
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
     * Gets date/time an event ends.
     *
     * If an event is all-day the end date is inclusive.
     * This means an event with start Nov 10 and end Nov 12 will span 3 days on the scheduler.
     *
     * If an event is NOT all-day the end date is exclusive.
     * This is only a gotcha when your end has time 00:00. It means your event ends on midnight,
     * and it will not span through the next day.
     *
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Sets date/time an event ends.
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
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     */
    public function setStatus($status)
    {
        if ($this->isValid($status)) {
            $this->status = $status;
        } else {
            throw new \LogicException(sprintf('Status "%s" is not supported', $status));
        }
    }

    /**
     * @param string|null $status
     * @return bool
     */
    protected function isValid($status)
    {
        return $status === self::OFFERED || in_array($status, $this->statuses);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->campaign)
            return (string)$this->getCampaign()->getTitle();
        else
            return 'default';
    }

    /**
     * @param User $owningUser
     *
     * @return SchedulerEvent
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
     * @return SchedulerEvent
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
}
