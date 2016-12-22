<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 23/Nov/16
 * Time: 12:43
 */

namespace CB\Bundle\NewAgeBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

class ReservationApiManager extends ApiEntityManager
{
    /**
     * @var ReservationManager
     */
    protected $reservationManager;

    /**
     * Constructor
     *
     * @param string $class Entity name
     * @param ObjectManager $om Object manager
     * @param ReservationManager $reservationManager
     */
    public function __construct($class, ObjectManager $om, ReservationManager $reservationManager)
    {
        $this->reservationManager = $reservationManager;
        parent::__construct($class, $om);
    }

    /**
     * {@inheritdoc}
     */
    public function createEntity()
    {
        return $this->reservationManager->createReservation();
    }
}