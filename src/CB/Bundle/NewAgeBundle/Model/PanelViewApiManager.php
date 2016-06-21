<?php
/**
 * Created by orm-generator.
 * User: catalin
 * Date: 16/Jun/16
 * Time: 11:22
 */

namespace CB\Bundle\NewAgeBundle\Model;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

class PanelViewApiManager extends ApiEntityManager
{
    /**
     * @var PanelViewManager
     */
    protected $panelViewManager;

    /**
     * Constructor
     *
     * @param string $class Entity name
     * @param ObjectManager $om Object manager
     * @param PanelViewManager $panelViewManager
     */
    public function __construct($class, ObjectManager $om, PanelViewManager $panelViewManager)
    {
        $this->panelViewManager = $panelViewManager;
        parent::__construct($class, $om);
    }

    /**
     * {@inheritdoc}
     */
    public function createEntity()
    {
        return $this->panelViewManager->createPanelView();
    }
}