<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 04/May/15
 * Time: 14:37
 */

namespace CB\Bundle\SchedulerBundle\Model;

use CB\Bundle\SchedulerBundle\Entity\SchedulerEvent;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class SchedulerEventManager
{
    /**
     * @var EntityManager
     */
    protected $entityManager;
    /**
     * @var AclHelper
     */
    protected $aclHelper;

    /**
     * @param EntityManager $entityManager
     * @param AclHelper $aclHelper
     */
    public function __construct(
        EntityManager $entityManager,
        AclHelper $aclHelper
    )
    {
        $this->entityManager = $entityManager;
        $this->aclHelper = $aclHelper;
    }

    /**
     * @return SchedulerEvent
     */
    public function createSchedulerEvent()
    {
        return $this->createSchedulerEventObject();
    }

    /**
     * @return SchedulerEvent
     */
    protected function createSchedulerEventObject()
    {
        return new SchedulerEvent();
    }
}