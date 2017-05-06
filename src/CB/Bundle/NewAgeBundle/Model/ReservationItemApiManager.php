<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 14/Nov/16
 * Time: 14:51
 */

namespace CB\Bundle\NewAgeBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

class ReservationItemApiManager extends ApiEntityManager
{
    /**
     * @var ReservationItemManager
     */
    protected $reservationItemManager;

    /**
     * Constructor
     *
     * @param string $class Entity name
     * @param ObjectManager $om Object manager
     * @param ReservationItemManager $reservationItemManager
     */
    public function __construct($class, ObjectManager $om, ReservationItemManager $reservationItemManager)
    {
        $this->reservationItemManager = $reservationItemManager;
        parent::__construct($class, $om);
    }

    /**
     * {@inheritdoc}
     */
    public function createEntity()
    {
        return $this->reservationItemManager->createReservationItem();
    }
}