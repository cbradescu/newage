<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 21/Jun/16
 * Time: 15:10
 */

namespace CB\Bundle\NewAgeBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

class CampaignApiManager extends ApiEntityManager
{
    /**
     * @var CampaignManager
     */
    protected $campaignManager;

    /**
     * Constructor
     *
     * @param string $class Entity name
     * @param ObjectManager $om Object manager
     * @param CampaignManager $campaignManager
     */
    public function __construct($class, ObjectManager $om, CampaignManager $campaignManager)
    {
        $this->campaignManager = $campaignManager;
        parent::__construct($class, $om);
    }

    /**
     * {@inheritdoc}
     */
    public function createEntity()
    {
        return $this->campaignManager->createCampaign();
    }
}