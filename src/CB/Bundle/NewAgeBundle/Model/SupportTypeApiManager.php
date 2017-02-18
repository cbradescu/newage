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

class SupportTypeApiManager extends ApiEntityManager
{
    /**
     * @var SupportTypeManager
     */
    protected $supportTypeManager;

    /**
     * Constructor
     *
     * @param string $class Entity name
     * @param ObjectManager $om Object manager
     * @param SupportTypeManager $supportTypeManager
     */
    public function __construct($class, ObjectManager $om, SupportTypeManager $supportTypeManager)
    {
        $this->supportTypeManager = $supportTypeManager;
        parent::__construct($class, $om);
    }

    /**
     * {@inheritdoc}
     */
    public function createEntity()
    {
        return $this->supportTypeManager->createSupportType();
    }
}