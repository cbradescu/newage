<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 07/Oct/16
 * Time: 09:44
 */

namespace CB\Bundle\NewAgeBundle\Model;

use CB\Bundle\NewAgeBundle\Entity\EnviromentType;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class EnviromentTypeManager
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
     * @return EnviromentType
     */
    public function createEnviromentType()
    {
        return $this->createEnviromentTypeObject();
    }

    /**
     * @return EnviromentType
     */
    protected function createEnviromentTypeObject()
    {
        return new EnviromentType();
    }
}