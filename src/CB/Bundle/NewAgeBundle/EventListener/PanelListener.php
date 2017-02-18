<?php

namespace CB\Bundle\NewAgeBundle\EventListener;

use Doctrine\ORM\UnitOfWork;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use CB\Bundle\NewAgeBundle\Entity\Panel;
use Oro\Bundle\UserBundle\Entity\User;

class PanelListener
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
        if (!$this->isPanelEntity($entity)) {
            return;
        }

        /** @var Panel $entity */
        $this->setCreatedProperties($entity, $args->getEntityManager());
        $this->setUpdatedProperties($entity, $args->getEntityManager());
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$this->isPanelEntity($entity)) {
            return;
        }

        /** @var Panel $entity */
        $this->setUpdatedProperties($entity, $args->getEntityManager(), true);
    }

    /**
     * @param object $entity
     * @return bool
     */
    protected function isPanelEntity($entity)
    {
        return $entity instanceof Panel;
    }

    /**
     * @param Panel $panel
     * @param EntityManager $entityManager
     */
    protected function setCreatedProperties(Panel $panel, EntityManager $entityManager)
    {
        if (!$panel->getCreatedAt()) {
            $panel->setCreatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        }
        if (!$panel->getCreatedBy()) {
            $panel->setCreatedBy($this->getUser($entityManager));
        }
    }

    /**
     * @param Panel $panel
     * @param EntityManager $entityManager
     * @param bool $update
     */
    protected function setUpdatedProperties(Panel $panel, EntityManager $entityManager, $update = false)
    {
        $newUpdatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $newUpdatedBy = $this->getUser($entityManager);

        $unitOfWork = $entityManager->getUnitOfWork();
        if ($update) {
            $unitOfWork->propertyChanged($panel, 'updatedAt', $panel->getUpdatedAt(), $newUpdatedAt);
            $unitOfWork->propertyChanged($panel, 'updatedBy', $panel->getUpdatedBy(), $newUpdatedBy);
        }

        $panel->setUpdatedAt($newUpdatedAt);
        $panel->setUpdatedBy($newUpdatedBy);
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
