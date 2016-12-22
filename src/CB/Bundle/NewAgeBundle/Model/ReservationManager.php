<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 23/Nov/16
 * Time: 12:43
 */

namespace CB\Bundle\NewAgeBundle\Model;

use CB\Bundle\NewAgeBundle\Entity\Offer;
use CB\Bundle\NewAgeBundle\Entity\Reservation;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class ReservationManager
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
     * @param Offer $offer
     *
     * @return Reservation
     */
    public function createReservation()
    {
        return $this->createReservationObject();
    }

    /**
     * @param Offer $offer

     * @return Reservation
     */
    protected function createReservationObject()
    {
        return new Reservation();
    }
}