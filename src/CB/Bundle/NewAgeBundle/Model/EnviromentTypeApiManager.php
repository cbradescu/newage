<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 07/Oct/16
 * Time: 09:44
 */

namespace CB\Bundle\NewAgeBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

class EnviromentTypeApiManager extends ApiEntityManager
{
    /**
     * @var EnviromentTypeManager
     */
    protected $enviromentTypeManager;

    /**
     * Constructor
     *
     * @param string $class Entity name
     * @param ObjectManager $om Object manager
     * @param EnviromentTypeManager $enviromentTypeManager
     */
    public function __construct($class, ObjectManager $om, EnviromentTypeManager $enviromentTypeManager)
    {
        $this->enviromentTypeManager = $enviromentTypeManager;
        parent::__construct($class, $om);
    }

    /**
     * {@inheritdoc}
     */
    public function createEntity()
    {
        return $this->enviromentTypeManager->createEnviromentType();
    }
}