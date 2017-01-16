<?php

namespace CB\Bundle\NewAgeBundle\EventListener;

use Doctrine\ORM\UnitOfWork;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

use CB\Bundle\NewAgeBundle\Entity\Offer;
use Oro\Bundle\UserBundle\Entity\User;

class OfferListener
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
        if (!$this->isOfferEntity($entity)) {
            return;
        }

        /** @var Offer $entity */
        $this->setCreatedProperties($entity, $args->getEntityManager());
        $this->setUpdatedProperties($entity, $args->getEntityManager());
    }

    /**
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        if (!$this->isOfferEntity($entity)) {
            return;
        }

        /** @var Offer $entity */
        $this->setUpdatedProperties($entity, $args->getEntityManager(), true);
    }

    /**
     * @param object $entity
     * @return bool
     */
    protected function isOfferEntity($entity)
    {
        return $entity instanceof Offer;
    }

    /**
     * @param Offer $offer
     * @param EntityManager $entityManager
     */
    protected function setCreatedProperties(Offer $offer, EntityManager $entityManager)
    {
        if (!$offer->getCreatedAt()) {
            $offer->setCreatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        }
        if (!$offer->getCreatedBy()) {
            $offer->setCreatedBy($this->getUser($entityManager));
        }
    }

    /**
     * @param Offer $offer
     * @param EntityManager $entityManager
     * @param bool $update
     */
    protected function setUpdatedProperties(Offer $offer, EntityManager $entityManager, $update = false)
    {
        $newUpdatedAt = new \DateTime('now', new \DateTimeZone('UTC'));
        $newUpdatedBy = $this->getUser($entityManager);

        $unitOfWork = $entityManager->getUnitOfWork();
        if ($update) {
            $unitOfWork->propertyChanged($offer, 'updatedAt', $offer->getUpdatedAt(), $newUpdatedAt);
            $unitOfWork->propertyChanged($offer, 'updatedBy', $offer->getUpdatedBy(), $newUpdatedBy);
        }

        $offer->setUpdatedAt($newUpdatedAt);
        $offer->setUpdatedBy($newUpdatedBy);
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
