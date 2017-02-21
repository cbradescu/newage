<?php

namespace CB\Bundle\NewAgeBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\UserBundle\Entity\Role;

class LoadSalesRole extends AbstractFixture implements DependentFixtureInterface
{
    const ROLE_SALES    = 'ROLE_SALES';

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return ['Oro\Bundle\OrganizationBundle\Migrations\Data\ORM\LoadOrganizationAndBusinessUnitData'];
    }

    /**
     * Load role
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $roleSales = new Role(self::ROLE_SALES);
        $roleSales->setLabel('Vanzari');

        $manager->persist($roleSales);

        $manager->flush();
    }
}
