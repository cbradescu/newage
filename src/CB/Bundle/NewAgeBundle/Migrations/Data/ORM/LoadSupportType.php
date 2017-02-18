<?php

namespace CB\Bundle\NewAgeBundle\Migrations\Data\ORM;

use CB\Bundle\NewAgeBundle\Entity\SupportType;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

use Oro\Bundle\UserBundle\Entity\Role;

class LoadSupportType extends AbstractFixture
{
    /**
     * Load role
     *
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $supportType = new SupportType();
        $supportType->setName('BACKL');

        $manager->persist($supportType);

        $supportType = new SupportType();
        $supportType->setName('DER');

        $manager->persist($supportType);

        $supportType = new SupportType();
        $supportType->setName('ROOFTOP');

        $manager->persist($supportType);

        $supportType = new SupportType();
        $supportType->setName('BLB');

        $manager->persist($supportType);

        $supportType = new SupportType();
        $supportType->setName('CL');

        $manager->persist($supportType);

        $manager->flush();
    }
}
