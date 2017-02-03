<?php

namespace CB\Bundle\NewAgeBundle\Entity\Repository;

use CB\Bundle\SchedulerBundle\Entity\SchedulerEvent;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class OfferRepository extends EntityRepository
{
    /**
     * Returns a query builder which can be used to get a list of offers,
     * that does not have at least one confirmed event
     *
     * @return QueryBuilder
     */
    public function getUnconfirmedOffersQueryBuilder()
    {
        // List of offers with confirmed events.
        $evQb = $this->getEntityManager()->getRepository('CBSchedulerBundle:SchedulerEvent')->createQueryBuilder('ev')
            ->select('IDENTITY(ri.offer)')
            ->leftJoin('ev.reservationItem', 'ri')
            ->where('ev.status=' . SchedulerEvent::CONFIRMED)
        ;

        // List of offers that has NO confirmed events.
        $qb = $this->createQueryBuilder('o')
            ->select('o')
            ->addSelect('c')
            ->leftJoin('o.campaign', 'c')
            ->where('o.id NOT IN (' . $evQb->getDQL() . ')')
            ->andWhere('o.end <= :ten_days_before_current_date')
        ;

        return $qb;
    }
}