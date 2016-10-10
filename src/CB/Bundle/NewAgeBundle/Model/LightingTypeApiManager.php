<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 07/Oct/16
 * Time: 11:41
 */

namespace CB\Bundle\NewAgeBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

class LightingTypeApiManager extends ApiEntityManager
{
    /**
     * @var LightingTypeManager
     */
    protected $lightingTypeManager;

    /**
     * Constructor
     *
     * @param string $class Entity name
     * @param ObjectManager $om Object manager
     * @param LightingTypeManager $lightingTypeManager
     */
    public function __construct($class, ObjectManager $om, LightingTypeManager $lightingTypeManager)
    {
        $this->lightingTypeManager = $lightingTypeManager;
        parent::__construct($class, $om);
    }

    /**
     * {@inheritdoc}
     */
    public function createEntity()
    {
        return $this->lightingTypeManager->createLightingType();
    }
}