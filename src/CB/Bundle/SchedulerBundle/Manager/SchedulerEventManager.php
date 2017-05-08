<?php

namespace CB\Bundle\SchedulerBundle\Manager;

use Doctrine\ORM\EntityManager;
use CB\Bundle\NewAgeBundle\Entity\Client;
use CB\Bundle\NewAgeBundle\Entity\PanelView;
use CB\Bundle\NewAgeBundle\Entity\Repository\ClientRepository;
use CB\Bundle\NewAgeBundle\Entity\Repository\PanelViewRepository;

use CB\Bundle\SchedulerBundle\Entity\SchedulerEvent;
use Oro\Bundle\CalendarBundle\Provider\SystemCalendarConfig;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;

class SchedulerEventManager
{
    /** @var EntityManager */
    protected $em;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var EntityNameResolver */
    protected $entityNameResolver;

    /** @var SystemCalendarConfig */
    protected $calendarConfig;

    /**
     * @param DoctrineHelper       $doctrineHelper
     * @param SecurityFacade       $securityFacade
     * @param EntityNameResolver   $entityNameResolver
     * @param SystemCalendarConfig $calendarConfig
     */
    public function __construct(
        EntityManager $em,
        DoctrineHelper $doctrineHelper,
        SecurityFacade $securityFacade,
        EntityNameResolver $entityNameResolver,
        SystemCalendarConfig $calendarConfig
    ) {
        $this->em                  = $em;
        $this->doctrineHelper     = $doctrineHelper;
        $this->securityFacade     = $securityFacade;
        $this->entityNameResolver = $entityNameResolver;
        $this->calendarConfig     = $calendarConfig;
    }

    /**
     * Gets a list of panelViews
     *
     * @return array of [id, name]
     */
    public function getPanelViews()
    {
        /** @var PanelViewRepository $repo */
        $repo      = $this->doctrineHelper->getEntityRepository('CBNewAgeBundle:PanelView');
        $panelViews = $repo->getPanelViewsQueryBuilder($this->securityFacade->getOrganizationId())
            ->select('pv.id, pv.name, p.name as panelName')
            ->getQuery()
            ->getArrayResult();

        return $panelViews;
    }

    /**
     * Gets a list of clients
     *
     * @return array of [id, name]
     */
    public function getClients()
    {
        /** @var ClientRepository $repo */
        $repo      = $this->doctrineHelper->getEntityRepository('CBNewAgeBundle:Client');
        $clients = $repo->getClientsQueryBuilder($this->securityFacade->getOrganizationId())
            ->select('c.id, c.title as name')
            ->getQuery()
            ->getArrayResult();

        return $clients;
    }

    /**
     * Links an event with a client by its id
     *
     * @param SchedulerEvent $event
     * @param int           $clientId
     *
     * @throws \LogicException
     * @throws ForbiddenException
     */
    public function setClient(SchedulerEvent $event, $clientId)
    {
        $client = $event->getClient();
        if (!$client || $client->getId() !== $clientId) {
            $event->setClient($this->findClient($clientId));
        }

    }

    /**
     * @param int $clientId
     *
     * @return Client|null
     */
    protected function findClient($clientId)
    {
        return $this->doctrineHelper->getEntityRepository('CBNewAgeBundle:Client')
            ->find($clientId);
    }

    /**
     * Links an event with a panelView by its id
     *
     * @param SchedulerEvent $event
     * @param int           $panelViewId
     *
     * @throws \LogicException
     * @throws ForbiddenException
     */
    public function setPanelView(SchedulerEvent $event, $panelViewId)
    {
        $panelView = $event->getPanelView();
        if (!$panelView || $panelView->getId() !== $panelViewId) {
            $event->setPanelView($this->findPanelView($panelViewId));
        }
    }

    /**
     * @param int $panelViewId
     *
     * @return PanelView|null
     */
    protected function findPanelView($panelViewId)
    {
        return $this->doctrineHelper->getEntityRepository('CBNewAgeBundle:PanelView')
            ->find($panelViewId);
    }
    /**
     * Toggle SchedulerEvent
     *
     * @param SchedulerEvent $entity
     */
    public function toggleEventStatus(SchedulerEvent $entity)
    {
        $this->setStatus($entity, SchedulerEvent::RESERVED);
        $this->em->persist($entity);

        $this->em->flush();
    }

    public function setStatus(SchedulerEvent $event, $status)
    {
        $event->setStatus($status);
    }
}
