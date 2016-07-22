<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 21/Jun/16
 * Time: 15:10
 */

namespace CB\Bundle\NewAgeBundle\Model;

use CB\Bundle\NewAgeBundle\Entity\Campaign;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class CampaignManager
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
     * @return Campaign
     */
    public function createCampaign()
    {
        return $this->createCampaignObject();
    }

    /**
     * @return Campaign
     */
    protected function createCampaignObject()
    {
        return new Campaign();
    }
}