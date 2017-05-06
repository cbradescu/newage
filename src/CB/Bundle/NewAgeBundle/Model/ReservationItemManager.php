<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 14/Nov/16
 * Time: 14:51
 */

namespace CB\Bundle\NewAgeBundle\Model;

use CB\Bundle\NewAgeBundle\Entity\ReservationItem;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class ReservationItemManager
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
     * @return ReservationItem
     */
    public function createReservationItem()
    {
        return $this->createReservationItemObject();
    }

    /**
     * @return ReservationItem
     */
    protected function createReservationItemObject()
    {
        return new ReservationItem();
    }
}