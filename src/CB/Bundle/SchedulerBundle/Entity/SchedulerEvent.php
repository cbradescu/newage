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
 *          @ORM\Index(name="cb_scheduler_event_up_idx", columns={"updatedAt"})
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

    const RESERVED = 0;
    const CONFIRMED = 1;

    static $statuses = [
        self::RESERVED,
        self::CONFIRMED
    ];

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
        $client = $this->reservationItem->getOfferItem()->getOffer()->getClient();
        if ($client)
            return (string)$client->getTitle();
        else
            return 'default';
    }
}