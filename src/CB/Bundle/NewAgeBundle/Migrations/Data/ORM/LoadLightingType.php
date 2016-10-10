<?php

namespace CB\Bundle\NewAgeBundle\Migrations\Data\ORM;

use CB\Bundle\NewAgeBundle\Entity\LightingType;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\UserBundle\Entity\Role;

class LoadLightingType extends AbstractFixture
{
    /**
     * Load role
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $lightingType = new LightingType();
        $lightingType->setName('Yes');

        $manager->persist($lightingType);

        $lightingType = new LightingType();
        $lightingType->setName('No');

        $manager->persist($lightingType);

        $lightingType = new LightingType();
        $lightingType->setName('LED');

        $manager->persist($lightingType);


        $manager->flush();
    }
}
