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

class OfferApiManager extends ApiEntityManager
{
    /**
     * @var OfferManager
     */
    protected $offerManager;

    /**
     * Constructor
     *
     * @param string $class Entity name
     * @param ObjectManager $om Object manager
     * @param OfferManager $offerManager
     */
    public function __construct($class, ObjectManager $om, OfferManager $offerManager)
    {
        $this->offerManager = $offerManager;
        parent::__construct($class, $om);
    }

    /**
     * {@inheritdoc}
     */
    public function createEntity()
    {
        return $this->offerManager->createOffer();
    }
}