<?php

namespace CB\Bundle\SchedulerBundle\Provider;

use Oro\Component\PropertyAccess\PropertyAccessor;

use CB\Bundle\SchedulerBundle\Entity\SchedulerEvent;
use CB\Bundle\SchedulerBundle\Entity\Repository\SchedulerEventRepository;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\SecurityBundle\SecurityFacade;

class CampaignSchedulerEventNormalizer extends AbstractSchedulerEventNormalizer
{
    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var PropertyAccessor */
    protected $propertyAccessor;

    /**
     * @param SecurityFacade  $securityFacade
     * @param DoctrineHelper  $doctrineHelper
     */
    public function __construct(
        SecurityFacade $securityFacade,
        DoctrineHelper $doctrineHelper
    ) {
        parent::__construct();
        $this->securityFacade = $securityFacade;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * Converts calendar event to form that can be used in API
     *
     * @param SchedulerEvent $event      The calendar event object
     * @param int           $calendarId The target calendar id
     *
     * @param array         $extraFields
     *
     * @return array
     */
    public function getSchedulerEvent(SchedulerEvent $event, $calendarId = null, array $extraFields = [])
    {
        $item = $this->transformEntity($this->serializeSchedulerEvent($event, $extraFields));
        if (!$calendarId) {
            $calendarId = $item['calendar'];
        }

        $result = [$item];
        $this->applyAdditionalData($result, $calendarId);
        $this->applyPermissions($result[0], $calendarId);

        return $result[0];
    }

    /**
     * @param SchedulerEvent $event
     *
     * @param array         $extraFields
     *
     * @return array
     */
    protected function serializeSchedulerEvent(SchedulerEvent $event, array $extraFields = [])
    {
        $propertyAccessor = $this->getPropertyAccessor();
        $extraValues = [];

        foreach ($extraFields as $field) {
            $extraValues[$field] = $propertyAccessor->getValue($event, $field);
        }

        return array_merge(
            [
                'id'               => $event->getId(),
                'title'            => $event->getTitle(),
                'description'      => $event->getDescription(),
                'start'            => $event->getStart(),
                'end'              => $event->getEnd(),
//                'allDay'           => $event->getAllDay(),
//                'backgroundColor'  => $event->getBackgroundColor(),
//                'createdAt'        => $event->getCreatedAt(),
//                'updatedAt'        => $event->getUpdatedAt(),
//                'invitationStatus' => $event->getInvitationStatus(),
//                'parentEventId'    => $event->getParent() ? $event->getParent()->getId() : null,
                'calendar'         => $event->getScheduler() ? $event->getScheduler()->getId() : null,
                'resourceId'    => 'a'
            ],
            $extraValues
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function applyAdditionalData(&$items, $calendarId)
    {
        $parentEventIds = $this->getParentEventIds($items);
        if ($parentEventIds) {
            /** @var SchedulerEventRepository $repo */
            $repo     = $this->doctrineHelper->getEntityRepository('CBSchedulerBundle:SchedulerEvent');
//////            $invitees = $repo->getInvitedUsersByParentsQueryBuilder($parentEventIds)
//////                ->getQuery()
//////                ->getArrayResult();
////
////            $groupedInvitees = [];
////            foreach ($invitees as $invitee) {
////                $groupedInvitees[$invitee['parentEventId']][] = $invitee;
////            }
//
//            foreach ($items as &$item) {
//                $item['invitedUsers'] = [];
//                if (isset($groupedInvitees[$item['id']])) {
//                    foreach ($groupedInvitees[$item['id']] as $invitee) {
//                        $item['invitedUsers'][] = $invitee['userId'];
//                    }
//                }
//            }
        } else {
            foreach ($items as &$item) {
                $item['invitedUsers'] = [];
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function applyPermissions(&$item, $calendarId)
    {
        $item['editable']     =
            ($item['scheduler'] === $calendarId)
            && empty($item['parentEventId'])
            && $this->securityFacade->isGranted('oro_calendar_event_update');
        $item['removable']    =
            ($item['scheduler'] === $calendarId)
            && $this->securityFacade->isGranted('oro_calendar_event_delete');
        $item['notifiable'] =
            !empty($item['invitationStatus'])
            && empty($item['parentEventId'])
            && !empty($item['invitedUsers']);
    }

    /**
     * @param array  $items
     *
     * @return array
     */
    protected function getParentEventIds(array $items)
    {
        $ids = [];
        foreach ($items as $item) {
            if (empty($item['parentEventId'])) {
                $ids[] = $item['id'];
            }
        }

        return $ids;
    }

    /**
     * @return PropertyAccessor
     */
    protected function getPropertyAccessor()
    {
        if (null === $this->propertyAccessor) {
            $this->propertyAccessor = new PropertyAccessor();
        }

        return $this->propertyAccessor;
    }
}
