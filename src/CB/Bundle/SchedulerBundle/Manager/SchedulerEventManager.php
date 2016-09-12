<?php

namespace CB\Bundle\SchedulerBundle\Manager;

use CB\Bundle\NewAgeBundle\Entity\Campaign;
use CB\Bundle\NewAgeBundle\Entity\PanelView;
use CB\Bundle\NewAgeBundle\Entity\Repository\CampaignRepository;
use CB\Bundle\NewAgeBundle\Entity\Repository\PanelViewRepository;

use CB\Bundle\SchedulerBundle\Entity\SchedulerEvent;
use Oro\Bundle\CalendarBundle\Provider\SystemCalendarConfig;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\SecurityBundle\Exception\ForbiddenException;

class SchedulerEventManager
{
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
        DoctrineHelper $doctrineHelper,
        SecurityFacade $securityFacade,
        EntityNameResolver $entityNameResolver,
        SystemCalendarConfig $calendarConfig
    ) {
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
            ->select('c.id, c.name')
            ->getQuery()
            ->getArrayResult();

        return $panelViews;
    }

    /**
     * Gets a list of campaigns
     *
     * @return array of [id, name]
     */
    public function getCampaigns()
    {
        /** @var CampaignRepository $repo */
        $repo      = $this->doctrineHelper->getEntityRepository('CBNewAgeBundle:Campaign');
        $campaigns = $repo->getCampaignsQueryBuilder($this->securityFacade->getOrganizationId())
            ->select('c.id, c.title as name')
            ->getQuery()
            ->getArrayResult();

        return $campaigns;
    }

    /**
     * Links an event with a campaign by its id
     *
     * @param SchedulerEvent $event
     * @param int           $campaignId
     *
     * @throws \LogicException
     * @throws ForbiddenException
     */
    public function setCampaign(SchedulerEvent $event, $campaignId)
    {
        $campaign = $event->getCampaign();
        if (!$campaign || $campaign->getId() !== $campaignId) {
            $event->setCampaign($this->findCampaign($campaignId));
        }

    }

    /**
     * @param int $campaignId
     *
     * @return Campaign|null
     */
    protected function findCampaign($campaignId)
    {
        return $this->doctrineHelper->getEntityRepository('CBNewAgeBundle:Campaign')
            ->find($campaignId);
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
     * Gets UID of a calendar this event belongs to
     * The calendar UID is a string includes a calendar alias and id in the following format: {alias}_{id}
     *
     * @param string $calendarAlias
     * @param int    $calendarId
     *
     * @return string
     */
    public function getCalendarUid($calendarAlias, $calendarId)
    {
        return sprintf('%s_%d', $calendarAlias, $calendarId);
    }

    /**
     * Extracts calendar alias and id from a calendar UID
     *
     * @param string $calendarUid
     *
     * @return array [$calendarAlias, $calendarId]
     */
    public function parseCalendarUid($calendarUid)
    {
        $delim = strrpos($calendarUid, '_');

        return [
            substr($calendarUid, 0, $delim),
            (int)substr($calendarUid, $delim + 1)
        ];
    }
}
