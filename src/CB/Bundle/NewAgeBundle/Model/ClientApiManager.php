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

class ClientApiManager extends ApiEntityManager
{
    /**
     * @var ClientManager
     */
    protected $clientManager;

    /**
     * Constructor
     *
     * @param string $class Entity name
     * @param ObjectManager $om Object manager
     * @param ClientManager $clientManager
     */
    public function __construct($class, ObjectManager $om, ClientManager $clientManager)
    {
        $this->clientManager = $clientManager;
        parent::__construct($class, $om);
    }

    /**
     * {@inheritdoc}
     */
    public function createEntity()
    {
        return $this->clientManager->createClient();
    }
}