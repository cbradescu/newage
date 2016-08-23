<?php

namespace CB\Bundle\SchedulerBundle\Provider;

use CB\Bundle\SchedulerBundle\Entity\Scheduler;
use CB\Bundle\SchedulerBundle\Entity\Repository\SchedulerEventRepository;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;

class CampaignSchedulerProvider extends AbstractSchedulerProvider
{
    /** @var EntityNameResolver */
    protected $entityNameResolver;

    /** @var AbstractSchedulerEventNormalizer */
    protected $calendarEventNormalizer;

    /**
     * @param DoctrineHelper                  $doctrineHelper
     * @param EntityNameResolver              $entityNameResolver
     * @param AbstractSchedulerEventNormalizer $calendarEventNormalizer
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        EntityNameResolver $entityNameResolver,
        AbstractSchedulerEventNormalizer $calendarEventNormalizer
    ) {
        parent::__construct($doctrineHelper);
        $this->entityNameResolver      = $entityNameResolver;
        $this->calendarEventNormalizer = $calendarEventNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function getSchedulerDefaultValues($organizationId, $userId, $calendarId, array $calendarIds)
    {
        if (empty($calendarIds)) {
            return [];
        }

        $qb = $this->doctrineHelper->getEntityRepository('OroCalendarBundle:Calendar')
            ->createQueryBuilder('o')
            ->select('o, owner')
            ->innerJoin('o.owner', 'owner');
        $qb->where($qb->expr()->in('o.id', $calendarIds));

        $result = [];

        /** @var Calendar[] $calendars */
        $calendars = $qb->getQuery()->getResult();
        foreach ($calendars as $calendar) {
            $resultItem = [
                'calendarName' => $this->buildCalendarName($calendar),
                'userId'       => $calendar->getOwner()->getId()
            ];
            // prohibit to remove the current calendar from the list of connected calendars
            if ($calendar->getId() === $calendarId) {
                $resultItem['removable'] = false;
                $resultItem['canAddEvent']    = true;
                $resultItem['canEditEvent']   = true;
                $resultItem['canDeleteEvent'] = true;
            }
            $result[$calendar->getId()] = $resultItem;
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getSchedulerEvents(
        $organizationId,
        $userId,
        $calendarId,
        $start,
        $end,
        $connections,
        $extraFields = []
    ) {
        /** @var SchedulerEventRepository $repo */
        $repo        = $this->doctrineHelper->getEntityRepository('CBSchedulerBundle:SchedulerEvent');
        $extraFields = $this->filterSupportedFields($extraFields, 'CB\Bundle\SchedulerBundle\Entity\SchedulerEvent');
        $qb          = $repo->getUserEventListByTimeIntervalQueryBuilder($start, $end, [], $extraFields);

//        $visibleIds = [];
//        foreach ($connections as $id => $visible) {
//            if ($visible) {
//                $visibleIds[] = $id;
//            }
//        }
//        if ($visibleIds) {
//            $qb
//                ->andWhere('c.id IN (:visibleIds)')
//                ->setParameter('visibleIds', $visibleIds);
//        } else {
//            $qb
//                ->andWhere('1 = 0');
//        }

        return $this->calendarEventNormalizer->getSchedulerEvents($calendarId, $qb->getQuery());
    }

    /**
     * @param Calendar $calendar
     *
     * @return string
     */
    protected function buildCalendarName(Calendar $calendar)
    {
        return $calendar->getName() ?: $this->entityNameResolver->getName($calendar->getOwner());
    }
}
