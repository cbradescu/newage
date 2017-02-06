<?php

namespace CB\Bundle\NewAgeBundle\Entity\Repository;

use CB\Bundle\NewAgeBundle\Entity\PanelView;
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

    /**
     * Returns all offers that has reservations for a specific panel view that intersect with a period of time.
     *
     * @param PanelView $panelView
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * @return array
     */
    public function getOfferWithReservationsFromInterval(PanelView $panelView, \DateTime $start, \DateTime $end)
    {
        $qb = $this->createQueryBuilder('o')
            ->select('o')
            ->leftJoin('o.reservationItems', 'ri')
            ->leftJoin('ri.events', 'ev')
            ->where('ri.panelView = :panelView')
            ->andWhere('ev.status <> :confirmed')
            ->andWhere('
                (ri.start >= :start AND ri.start <= :end) OR 
                (ri.end >= :start AND ri.end <= :end) OR 
                (ri.start >= :start AND ri.end <= :end) OR 
                (ri.start <= :start AND ri.end >= :end)'
            )
            ->setParameter('panelView', $panelView)
            ->setParameter('confirmed', SchedulerEvent::CONFIRMED)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
        ;

        return $qb->getQuery()->getResult();
    }
}