<?php

namespace CB\Bundle\SchedulerBundle\Entity\Repository;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class SchedulerEventRepository extends EntityRepository
{
    /**
     * Returns a query builder which can be used to get a list of user scheduler events filtered by start and end dates
     *
     * @param \DateTime      $startDate
     * @param \DateTime      $endDate
     * @param array|Criteria $filters   Additional filtering criteria, e.g. ['allDay' => true, ...]
     *                                  or \Doctrine\Common\Collections\Criteria
     * @param array          $extraFields
     *
     * @return QueryBuilder
     */
    public function getUserEventListByTimeIntervalQueryBuilder($startDate, $endDate, $filters = [], $extraFields = [])
    {
        $qb = $this->getUserEventListQueryBuilder($filters, $extraFields);
        $this->addTimeIntervalFilter($qb, $startDate, $endDate);

        return $qb;
    }

    /**
     * Returns a query builder which can be used to get a list of user scheduler events
     *
     * @param array|Criteria $filters Additional filtering criteria, e.g. ['allDay' => true, ...]
     *                                or \Doctrine\Common\Collections\Criteria
     * @param array          $extraFields
     *
     * @return QueryBuilder
     */
    public function getUserEventListQueryBuilder($filters = [], $extraFields = [])
    {
        $qb = $this->getEventListQueryBuilder($extraFields)
            ->addSelect("'a' as resourceId")
//            ->addSelect('e.invitationStatus, IDENTITY(e.parent) AS parentEventId, c.id as scheduler')
//            ->innerJoin('e.scheduler', 'c')
            ->addSelect('c.id as scheduler')
            ->innerJoin('e.scheduler', 'c')
        ;

        $this->addFilters($qb, $filters);

        return $qb;
    }

    /**
     * Returns a query builder which can be used to get a list of system scheduler events filtered by start and end dates
     *
     * @param \DateTime      $startDate
     * @param \DateTime      $endDate
     * @param array|Criteria $filters   Additional filtering criteria, e.g. ['allDay' => true, ...]
     *                                  or \Doctrine\Common\Collections\Criteria
     * @param array          $extraFields
     *
     * @return QueryBuilder
     */
    public function getSystemEventListByTimeIntervalQueryBuilder($startDate, $endDate, $filters = [], $extraFields = [])
    {
        $qb = $this->getEventListQueryBuilder($extraFields)
            ->addSelect('c.id as scheduler')
            ->innerJoin('e.systemScheduler', 'c')
            ->andWhere('c.public = :public')
            ->setParameter('public', false);

        $this->addFilters($qb, $filters);
        $this->addTimeIntervalFilter($qb, $startDate, $endDate);

        return $qb;
    }

    /**
     * Returns a query builder which can be used to get a list of public scheduler events filtered by start and end dates
     *
     * @param \DateTime      $startDate
     * @param \DateTime      $endDate
     * @param array|Criteria $filters   Additional filtering criteria, e.g. ['allDay' => true, ...]
     *                                  or \Doctrine\Common\Collections\Criteria
     * @param array          $extraFields
     *
     * @return QueryBuilder
     */
    public function getPublicEventListByTimeIntervalQueryBuilder($startDate, $endDate, $filters = [], $extraFields = [])
    {
        $qb = $this->getEventListQueryBuilder($extraFields)
            ->addSelect('c.id as scheduler')
            ->innerJoin('e.systemScheduler', 'c')
            ->andWhere('c.public = :public')
            ->setParameter('public', true);

        $this->addFilters($qb, $filters);
        $this->addTimeIntervalFilter($qb, $startDate, $endDate);

        return $qb;
    }

    /**
     * Returns a base query builder which can be used to get a list of scheduler events
     *
     * @param array $extraFields
     *
     * @return QueryBuilder
     */
    public function getEventListQueryBuilder($extraFields = [])
    {
        $qb = $this->createQueryBuilder('e')
//            ->select(
//                'e.id, e.title, e.description, e.start, e.end, e.allDay,'
//                . ' e.backgroundColor, e.createdAt, e.updatedAt'
//            );
            ->select(
                'e.id,
                 c.id as campaignId,
                 c.title as campaignName,
                 pv.id as panelViewId,
                 pv.name as panelViewName,
                 p.id as panelId,
                 p.name as panelName,
                 st.id as supportTypeId,
                 lt.id as lightingTypeId,
                 e.start,
                 e.end,
                 e.status'
            )
            ->leftJoin('e.panelView', 'pv')
            ->leftJoin('e.campaign', 'c')
            ->leftJoin('pv.panel', 'p')
            ->leftJoin('p.supportType', 'st')
            ->leftJoin('p.lightingType', 'lt')
        ;
//        if ($extraFields) {
//            foreach ($extraFields as $field) {
//                $qb->addSelect('e.' . $field);
//            }
//        }

        return $qb;
    }

    /**
     * Adds time condition to a query builder responsible to get calender events
     *
     * @param QueryBuilder  $qb
     * @param \DateTime     $startDate
     * @param \DateTime     $endDate
     */
    public function addTimeIntervalFilter(QueryBuilder $qb, $startDate, $endDate)
    {
        $qb
            ->andWhere(
                '(e.start < :start AND e.end >= :start) OR '
                . '(e.start <= :end AND e.end > :end) OR'
                . '(e.start >= :start AND e.end < :end)'
            )
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->orderBy('c.id, e.start');
    }

    /**
     * Returns a query builder which can be used to get invited users for the given scheduler events
     *
     * @param int[] $parentEventIds
     *
     * @return QueryBuilder
     */
    public function getInvitedUsersByParentsQueryBuilder($parentEventIds)
    {
        return $this->createQueryBuilder('e')
            ->select('IDENTITY(e.parent) AS parentEventId, e.id AS eventId, u.id AS userId')
            ->innerJoin('e.scheduler', 'c')
            ->innerJoin('c.owner', 'u')
            ->where('e.parent IN (:parentEventIds)')
            ->setParameter('parentEventIds', $parentEventIds);
    }

    /**
     * Adds filters to a query builder
     *
     * @param QueryBuilder   $qb
     * @param array|Criteria $filters Additional filtering criteria, e.g. ['allDay' => true, ...]
     *                                or \Doctrine\Common\Collections\Criteria
     */
    protected function addFilters(QueryBuilder $qb, $filters)
    {
        if ($filters) {
            if (is_array($filters)) {
                $newCriteria = new Criteria();
                foreach ($filters as $fieldName => $value) {
                    $newCriteria->andWhere(Criteria::expr()->eq($fieldName, $value));
                }

                $filters = $newCriteria;
            }

            if ($filters instanceof Criteria) {
                $qb->addCriteria($filters);
            }
        }
    }
}
