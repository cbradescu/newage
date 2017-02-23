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
     * @ORM\Column(name="sketch", type="string", length=255, nullable=true)
     * @ConfigField(
     *      defaultValues={
     *          "dataaudit"={
     *              "auditable"=true
     *          }
     *      }
     * )
     */
    protected $sketch;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
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
     * @return string
     */
    public function getSketch()
    {
        return $this->sketch;
    }

    /**
     * @param string $name
     */
    public function setSketch($sketch)
    {
        $this->sketch = $sketch;
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
     * @return PanelView
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

    /**
     * Find confirmed events in an interval for current panel view.
     *
     * @param \DateTime $start
     * @param \DateTime $end
     * @return array
     */
    public function getConfirmedEvents(\DateTime $start, \DateTime $end)
    {
        $confirmedEvents = [];
        foreach ($this->events as $event)
        {
            if ($event->getStatus()==SchedulerEvent::CONFIRMED and
                (
                    ($event->getStart()>=$start and $event->getStart()<=$end) or
                    ($event->getEnd()>=$start and $event->getEnd()<=$end) or
                    ($event->getStart()>=$start and $event->getEnd()<=$end) or
                    ($event->getStart()<=$start and $event->getEnd()>=$end)
                )
            )
                $confirmedEvents[] = [
                    'start' =>  $event->getStart(),
                    'end'   =>  $event->getEnd()
                ];
        }

        return $confirmedEvents;
    }

    /**
     * Return concatenated reserved intervals from a period.
     *
     * @param array $confirmed
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * @return array
     */
    public function getFreeIntervals(array $confirmed, \DateTime $start, \DateTime $end)
    {
        // sorting ascending by start date.
        usort($confirmed, function ($a, $b) {
            return $a['start']->getTimestamp() - $b['start']->getTimestamp();
        });

        $results = [];

        $first = array_shift($confirmed);
        /** @var array $int - current interval [start,end] */
        if ($first['start'] > $start)
            $results[] = [
                'start' => $start,
                'end' => $first['start']->modify('-1 day')
            ];

        $cloneDate = clone $first['end'];
        $int['start'] = $int['end'] = $cloneDate->modify('+1 day');

        foreach ($confirmed as $ev) {
            if ($ev['start'] > $int['end']) {
                if ($ev['start'] < $end) {
                    $cloneDate = clone $ev['start'];
                    $int['end'] = $cloneDate->modify('-1 day');
                } else {
                    $int['end'] = $end;
                }

                $results[] = $int;
            }

            if ($ev['end']<$end) {
                $cloneDate = clone $ev['end'];
                $int['start'] = $int['end'] = $cloneDate->modify('+1 day');
            } else {
                return $results;
            }
        }

        if ($int['start'] < $end) {
            $int['end'] = $end;

            $results[] = $int;
        }

        return $results;
    }
}