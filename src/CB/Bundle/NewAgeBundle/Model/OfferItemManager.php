<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 14/Nov/16
 * Time: 14:51
 */

namespace CB\Bundle\NewAgeBundle\Model;

use CB\Bundle\NewAgeBundle\Entity\OfferItem;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class OfferItemManager
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
     * @return Offer
     */
    public function createOfferItem()
    {
        return $this->createOfferItemObject();
    }

    /**
     * @return Offer
     */
    protected function createOfferItemObject()
    {
        return new OfferItem();
    }
}