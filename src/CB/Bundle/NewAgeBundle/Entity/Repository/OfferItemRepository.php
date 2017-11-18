<?php

namespace CB\Bundle\NewAgeBundle\Entity\Repository;

use CB\Bundle\SchedulerBundle\Entity\SchedulerEvent;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\RequestStack;

class OfferItemRepository extends EntityRepository
{
    /**
     * Creates the offered panel view datagrid in offer view
     *
     * @return QueryBuilder
     */
    public function getOfferedPanelViewsQueryBuilder()
    {
        $qb = $this->createQueryBuilder('oi')
            ->select(
                'oi.id',
                'oi.start',
                'oi.end',
                'pv.id as panelViewId',
                'pv.name as panelView',
                'pv.url',
                'pv.sketch',
                'p.name as panel',
                'p.dimensions',
                'p.neighborhoods',
                'st.name as support',
                'lt.name as lighting',
                'c.name as city',
                'CONCAT(addr.street,  CONCAT(\' \', addr.street2)) as address',
                'CONCAT(addr.latitude,  CONCAT(\',\', addr.longitude)) as gps',
                'o.name as campaign',
                'COUNT(ev.id) as reservations',
                'CASE WHEN (COUNT(ev.id)>0) THEN \'danger\' ELSE \'\' END as row_class_name'
            )
            ->leftJoin('oi.panelView', 'pv')
            ->leftJoin('pv.panel', 'p')
            ->leftJoin('p.supportType', 'st')
            ->leftJoin('p.lightingType', 'lt')
            ->leftJoin('p.addresses', 'addr')
            ->leftJoin('addr.city', 'c')
            ->leftJoin('oi.offer', 'o')

            ->leftJoin('pv.offerItems', 'oi2')
            ->leftJoin('oi2.reservationItems', 'ri2')
            ->leftJoin(
                'ri2.events',
                'ev',
                'WITH',
                'ev.status = ' . SchedulerEvent::RESERVED . ' AND ((ev.start >= oi.start AND ev.start <= oi.end) OR (ev.end >= oi.start AND ev.end <= oi.end) OR (ev.start >= oi.start AND ev.end <= oi.end) OR (ev.start <= oi.start AND ev.end >= oi.end))'
                )
            ->where('o.id=:offer')
            ->groupBy('oi.id')
        ;

        return $qb;
    }
}