<?php

namespace CB\Bundle\NewAgeBundle\EventListener;

use Doctrine\ORM\UnitOfWork;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use CB\Bundle\NewAgeBundle\Entity\Item;
use Oro\Bundle\UserBundle\Entity\User;

class ItemListener
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var SecurityContextInterface
     */
    protected $securityContext;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        // can't inject security context directly because of circular dependency for Doctrine entity manager
        $this->container = $container;
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$this->isItemEntity($entity)) {
            return;
        }

        /** @var Item $entity */
        $this->setCreatedProperties($entity, $args->getEntityManager());
    }

    /**
     * @param object $entity
     * @return bool
     */
    protected function isItemEntity($entity)
    {
        return $entity instanceof Item;
    }

    /**
     * @param Item $item
     * @param EntityManager $entityManager
     */
    protected function setCreatedProperties(Item $item, EntityManager $entityManager)
    {
            if (!$item->getCreatedBy()) {
                $item->setCreatedBy($this->getUser($entityManager));
            }
    }

    /**
     * @param EntityManager $entityManager
     * @return User|null
     */
    protected function getUser(EntityManager $entityManager)
    {
        $token = $this->getSecurityContext()->getToken();
        if (!$token) {
            return null;
        }

        $user = $token->getUser();
        if (!$user) {
            return null;
        }

        if ($entityManager->getUnitOfWork()->getEntityState($user) == UnitOfWork::STATE_DETACHED) {
            $user = $entityManager->find('OroUserBundle:User', $user->getId());
        }

        return $user;
    }

    /**
     * @return SecurityContextInterface
     */
    protected function getSecurityContext()
    {
        if (!$this->securityContext) {
            $this->securityContext = $this->container->get('security.context');
        }

        return $this->securityContext;
    }
}