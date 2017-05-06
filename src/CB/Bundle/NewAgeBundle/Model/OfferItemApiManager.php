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

class OfferItemApiManager extends ApiEntityManager
{
    /**
     * @var OfferItemManager
     */
    protected $offerItemManager;

    /**
     * Constructor
     *
     * @param string $class Entity name
     * @param ObjectManager $om Object manager
     * @param OfferItemManager $offerItemManager
     */
    public function __construct($class, ObjectManager $om, OfferItemManager $offerItemManager)
    {
        $this->offerItemManager = $offerItemManager;
        parent::__construct($class, $om);
    }

    /**
     * {@inheritdoc}
     */
    public function createEntity()
    {
        return $this->offerItemManager->createOfferItem();
    }
}