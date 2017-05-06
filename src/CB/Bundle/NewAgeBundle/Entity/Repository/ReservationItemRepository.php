<?php

namespace CB\Bundle\NewAgeBundle\Entity\Repository;

use CB\Bundle\NewAgeBundle\Entity\PanelView;

use CB\Bundle\SchedulerBundle\Entity\SchedulerEvent;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class ReservationItemRepository extends EntityRepository
{
    /**
     * Returns all reservation items for a specific panel view that intersect with a period of time.
     *
     * @param PanelView $panelView
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * @return array
     */
    public function getReservationItemsFromInterval(PanelView $panelView, \DateTime $start, \DateTime $end)
    {
        $qb = $this->createQueryBuilder('ri')
            ->select('ri')
            ->leftJoin('ri.offerItem', 'oi')
            ->leftJoin('ri.events', 'ev')
            ->where('oi.panelView = :panelView')
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
