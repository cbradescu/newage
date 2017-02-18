<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 14/Nov/16
 * Time: 08:19
 */

namespace CB\Bundle\NewAgeBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

class CityApiManager extends ApiEntityManager
{
    /**
     * @var CityManager
     */
    protected $cityManager;

    /**
     * Constructor
     *
     * @param string $class Entity name
     * @param ObjectManager $om Object manager
     * @param CityManager $cityManager
     */
    public function __construct($class, ObjectManager $om, CityManager $cityManager)
    {
        $this->cityManager = $cityManager;
        parent::__construct($class, $om);
    }

    /**
     * {@inheritdoc}
     */
    public function createEntity()
    {
        return $this->cityManager->createCity();
    }
}