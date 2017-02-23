<?php

namespace CB\Bundle\SchedulerBundle\Entity;

use CB\Bundle\NewAgeBundle\Entity\Client;
use CB\Bundle\NewAgeBundle\Entity\PanelView;
use CB\Bundle\NewAgeBundle\Entity\ReservationItem;
use CB\Bundle\SchedulerBundle\Model\ExtendSchedulerEvent;

use Doctrine\ORM\Mapping as ORM;

use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\ConfigField;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareInterface;
use Oro\Bundle\EntityBundle\EntityProperty\DatesAwareTrait;

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
 *          "security"={
 *              "type"="ACL"
 *          }
 *      }
 * )
 */
class SchedulerEvent extends ExtendSchedulerEvent implements DatesAwareInterface
{
    use DatesAwareTrait;

    const RESERVED = 0;
    const CONFIRMED = 1;

    static $statuses = [
        self::RESERVED,
        self::CONFIRMED
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
     * @ORM\Column(name="status", type="integer")
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
     * @var ReservationItem
     *
     * @ORM\ManyToOne(targetEntity="CB\Bundle\NewAgeBundle\Entity\ReservationItem", inversedBy="events", cascade={"persist"})
     * @ORM\JoinColumn(name="reservation_item_id", referencedColumnName="id", onDelete="CASCADE")
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $reservationItem;


    public function __construct()
    {
        parent::__construct();

        $this->status = SchedulerEvent::RESERVED;
    }

    /**
     * @return array
     */
    public static function getStatuses()
    {
        return self::$statuses;
    }

    /**
     * @return string
     */
    public function getStatusLabel()
    {
        return self::getStatusLabelForIndex($this->status);
    }

    /**
     * @param string $value
     * @return string
     */
    public static function getStatusLabelForIndex($value)
    {
        return self::$statuses[$value];
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
     * Gets owning client
     *
     * @return Client|null
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Sets owning client
     *
     * @param Client $client
     *
     * @return self
     */
    public function setClient(Client $client = null)
    {
        $this->client = $client;

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
     * Gets reservationItem
     *
     * @return ReservationItem|null
     */
    public function getReservationItem()
    {
        return $this->reservationItem;
    }

    /**
     * Sets reservationItem
     *
     * @param ReservationItem $reservationItem
     *
     * @return SchedulerEvent
     */
    public function setReservationItem(ReservationItem $reservationItem)
    {
        $this->reservationItem = $reservationItem;

        return $this;
    }

    /**
     * @param string|null $status
     * @return bool
     */
    protected function isValid($status)
    {
        return $status === self::RESERVED || in_array($status, SchedulerEvent::getStatuses());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if ($this->client)
            return (string)$this->getClient()->getTitle();
        else
            return 'default';
    }
}
