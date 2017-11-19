<?php

namespace CB\Bundle\NewAgeBundle\Entity\Repository;

use CB\Bundle\NewAgeBundle\Entity\OfferItem;
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
            ->select('IDENTITY(oi.offer)')
            ->leftJoin('ev.reservationItem', 'ri')
            ->leftJoin('ri.offerItem', 'oi')
            ->where('ev.status=' . SchedulerEvent::CONFIRMED);

        // List of offers that has NO confirmed events.
        $qb = $this->createQueryBuilder('o')
            ->select('o')
            ->addSelect('c')
            ->leftJoin('o.client', 'c')
            ->where('o.id NOT IN (' . $evQb->getDQL() . ')')
            ->andWhere('o.end <= :ten_days_before_current_date');

        return $qb;
    }

    /**
     * @param OfferItem $offerItem
     * @return array
     */
    public function getOfferItemOverlapsInfo(OfferItem $offerItem)
    {
        // List of offers that has NO confirmed events.
        $qb = $this->getEntityManager()->getRepository('CBNewAgeBundle:OfferItem')->createQueryBuilder('oi')
            ->select(
                'distinct o.id',
                'o.start',
                'o.end',
                'o.name',
                'c.title'
            )
            ->leftJoin('oi.offer', 'o')
            ->leftJoin('o.client', 'c')
            ->leftJoin('oi.reservationItems', 'ri')
            ->leftJoin(
                'ri.events',
                'ev',
                'WITH',
                '(ev.start >= :start AND ev.start <= :end) OR (ev.end >= :start AND ev.end <= :end) OR (ev.start >= :start AND ev.end <= :end) OR (ev.start <= :start AND ev.end >= :end)'
            )
            ->where('ev.status=' . SchedulerEvent::RESERVED)
            ->andWhere('oi.offer<>:offer')
            ->andWhere('oi.panelView=:panelView')
            ->setParameter('offer', $offerItem->getOffer()->getId())
            ->setParameter('panelView', $offerItem->getPanelView())
            ->setParameter('start', $offerItem->getStart()->format('Y-m-d'))
            ->setParameter('end', $offerItem->getEnd()->format('Y-m-d'))
            ;

        return $qb->getQuery()->getResult();
    }
}