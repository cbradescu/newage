<?php
/**
 * Created by orm-generator.
 * User: <%= user %>
 * Date: <%= date %>
 * Time: <%= time %>
 */

namespace <%= BundleNamespace %>\Model;

use <%= BundleNamespace %>\Entity\<%= EntityName %>;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class <%= EntityName %>Manager
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
     * @return <%= EntityName %>
     */
    public function create<%= EntityName %>()
    {
        return $this->create<%= EntityName %>Object();
    }

    /**
     * @return <%= EntityName %>
     */
    protected function create<%= EntityName %>Object()
    {
        return new <%= EntityName %>();
    }
}