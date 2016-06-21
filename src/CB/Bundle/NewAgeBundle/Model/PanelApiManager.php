<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 16/Jun/16
 * Time: 14:05
 */

namespace CB\Bundle\NewAgeBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

class PanelApiManager extends ApiEntityManager
{
    /**
     * @var PanelManager
     */
    protected $panelManager;

    /**
     * Constructor
     *
     * @param string $class Entity name
     * @param ObjectManager $om Object manager
     * @param PanelManager $panelManager
     */
    public function __construct($class, ObjectManager $om, PanelManager $panelManager)
    {
        $this->panelManager = $panelManager;
        parent::__construct($class, $om);
    }

    /**
     * {@inheritdoc}
     */
    public function createEntity()
    {
        return $this->panelManager->createPanel();
    }
}