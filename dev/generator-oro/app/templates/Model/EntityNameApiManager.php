<?php
/**
 * Created by orm-generator.
 * User: <%= user %>
 * Date: <%= date %>
 * Time: <%= time %>
 */

namespace <%= BundleNamespace %>\Model;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\SoapBundle\Entity\Manager\ApiEntityManager;

class <%= EntityName %>ApiManager extends ApiEntityManager
{
    /**
     * @var <%= EntityName %>Manager
     */
    protected $<%= entityName %>Manager;

    /**
     * Constructor
     *
     * @param string $class Entity name
     * @param ObjectManager $om Object manager
     * @param <%= EntityName %>Manager $<%= entityName %>Manager
     */
    public function __construct($class, ObjectManager $om, <%= EntityName %>Manager $<%= entityName %>Manager)
    {
        $this-><%= entityName %>Manager = $<%= entityName %>Manager;
        parent::__construct($class, $om);
    }

    /**
     * {@inheritdoc}
     */
    public function createEntity()
    {
        return $this-><%= entityName %>Manager->create<%= EntityName %>();
    }
}