<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 16/Jun/16
 * Time: 11:22
 */

namespace CB\Bundle\SchedulerBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

class SchedulerEventApiManager extends ApiEntityManager
{
    /**
     * @var SchedulerEventManager
     */
    protected $schedulerEventManager;

    /**
     * Constructor
     *
     * @param string $class Entity name
     * @param ObjectManager $om Object manager
     * @param SchedulerEventManager $schedulerEventManager
     */
    public function __construct($class, ObjectManager $om, SchedulerEventManager $schedulerEventManager)
    {
        $this->schedulerEventManager = $schedulerEventManager;
        parent::__construct($class, $om);
    }

    /**
     * {@inheritdoc}
     */
    public function createEntity()
    {
        return $this->schedulerEventManager->createSchedulerEvent();
    }
}