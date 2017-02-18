<?php

namespace CB\Bundle\NewAgeBundle\Migrations\Data\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\UserBundle\Entity\Role;

class LoadAvailableRole extends AbstractFixture implements DependentFixtureInterface
{
    const ROLE_AVAILABLE    = 'ROLE_AVAILABLE';

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
        $roleAvailable = new Role(self::ROLE_AVAILABLE);
        $roleAvailable->setLabel('Disponibil');

        $manager->persist($roleAvailable);

        $manager->flush();
    }
}
