<?php

namespace CB\Bundle\SchedulerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

use BeSimple\SoapBundle\ServiceDefinition\Annotation as Soap;

use CB\Bundle\SchedulerBundle\Model\ExtendSchedulerProperty;
use Oro\Bundle\EntityConfigBundle\Metadata\Annotation\Config;

/**
 * This entity is used to store different kind of user's properties for a scheduler.
 * The combination of schedulerAlias and scheduler is unique identifier of a scheduler.
 *
 * @ORM\Entity(repositoryClass="CB\Bundle\SchedulerBundle\Entity\Repository\SchedulerPropertyRepository")
 * @ORM\Table(
 *      name="cb_scheduler_property",
 *      uniqueConstraints={
 *          @ORM\UniqueConstraint(
 *              name="cb_scheduler_prop_uq",
 *              columns={"scheduler_alias", "scheduler_id", "target_scheduler_id"}
 *          )
 *      }
 * )
 * @Config(
 *      defaultValues={
 *          "entity"={
 *              "icon"="icon-cog"
 *          },
 *          "note"={
 *              "immutable"=true
 *          },
 *          "comment"={
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
class SchedulerProperty extends ExtendSchedulerProperty
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $id;

    /**
     * @var Scheduler
     *
     * @ORM\ManyToOne(targetEntity="Scheduler")
     * @ORM\JoinColumn(name="target_scheduler_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     * @Soap\ComplexType("CB\Bundle\SchedulerBundle\Entity\Scheduler")
     */
    protected $targetScheduler;

    /**
     * @var string
     *
     * @ORM\Column(name="scheduler_alias", type="string", length=32)
     * @Soap\ComplexType("string")
     */
    protected $schedulerAlias;

    /**
     * @var int
     *
     * @ORM\Column(name="scheduler_id", type="integer")
     * @Soap\ComplexType("int")
     */
    protected $scheduler;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", options={"default"=0})
     * @Soap\ComplexType("int", nillable=true)
     */
    protected $position = 0;

    /**
     * @var boolean
     *
     * @ORM\Column(name="visible", type="boolean", options={"default"=true})
     * @Soap\ComplexType("boolean", nillable=true)
     */
    protected $visible = true;

    /**
     * @var string|null
     *
     * @ORM\Column(name="background_color", type="string", length=7, nullable=true)
     * @Soap\ComplexType("string", nillable=true)
     */
    protected $backgroundColor;

    /**
     * Gets id of this set of scheduler properties.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Gets user's scheduler this set of scheduler properties belong to
     *
     * @return Scheduler
     */
    public function getTargetScheduler()
    {
        return $this->targetScheduler;
    }

    /**
     * Sets user's scheduler this set of scheduler properties belong to
     *
     * @param Scheduler $targetScheduler
     *
     * @return self
     */
    public function setTargetScheduler($targetScheduler)
    {
        $this->targetScheduler = $targetScheduler;

        return $this;
    }

    /**
     * Gets an alias of the connected scheduler
     *
     * @return string
     */
    public function getSchedulerAlias()
    {
        return $this->schedulerAlias;
    }

    /**
     * Sets an alias of the connected scheduler
     *
     * @param string $schedulerAlias
     *
     * @return self
     */
    public function setSchedulerAlias($schedulerAlias)
    {
        $this->schedulerAlias = $schedulerAlias;

        return $this;
    }

    /**
     * Gets an id of the connected scheduler
     *
     * @return int
     */
    public function getScheduler()
    {
        return $this->scheduler;
    }

    /**
     * Sets an id of the connected scheduler
     *
     * @param int $scheduler
     *
     * @return self
     */
    public function setScheduler($scheduler)
    {
        $this->scheduler = $scheduler;

        return $this;
    }

    /**
     * Gets a number indicates where the connected scheduler should be displayed
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Sets a number indicates where the connected scheduler should be displayed
     *
     * @param int $position
     *
     * @return self
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Gets a property indicates whether events of the connected scheduler should be displayed or not
     *
     * @return boolean
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Sets a property indicates whether events of the connected scheduler should be displayed or not
     *
     * @param bool $visible
     *
     * @return self
     */
    public function setVisible($visible)
    {
        $this->visible = (bool)$visible;

        return $this;
    }

    /**
     * Gets a background color of the connected scheduler events.
     * If this method returns null the background color should be calculated automatically on UI.
     *
     * @return string|null The color in hex format, for example F00 or FF0000 for a red color.
     */
    public function getBackgroundColor()
    {
        return $this->backgroundColor;
    }

    /**
     * Sets a background color of the connected scheduler events.
     *
     * @param string|null $backgroundColor The color in hex format, for example F00 or FF0000 for a red color.
     *                                     Set it to null to allow UI to calculate the background color automatically.
     *
     * @return self
     */
    public function setBackgroundColor($backgroundColor)
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getId();
    }
}
