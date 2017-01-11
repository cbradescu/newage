<?php

namespace CB\Bundle\NewAgeBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class PanelViewRepository extends EntityRepository
{
    /**
     * Returns a query builder which can be used to get list of panel views
     *
     * @param int $organizationId
     *
     * @return QueryBuilder
     */
    public function getPanelViewsQueryBuilder($organizationId)
    {
        $qb = $this->createQueryBuilder('pv')
            ->select(
                'pv.id',
                'pv.name',
                'p.name as panelName',
                'IDENTITY(p.supportType)',
                'IDENTITY(p.lightingType)',
                'c.name as city'
            )
            ->leftJoin('pv.panel', 'p')
            ->leftJoin('p.addresses', 'a')
            ->leftJoin('a.city', 'c')
            ->where('pv.organization = :organizationId')
            ->setParameter('organizationId', $organizationId)
            ->addOrderBy('p.name', 'ASC')
            ->addOrderBy('pv.name', 'ASC')
    ;

        return $qb;
    }

    /**
     * Returns a query builder which can be used to get list of reserved panel views on a specific reservation and their status.
     *
     * @param int $reservationId
     *
     * @return QueryBuilder
     */
    public function getReservedPanelViewsWithStatusQueryBuilder()
    {
        $qb = $this->createQueryBuilder('pv')
            ->select(
                'pv.id',
                '(:reservation) as rid',
                'pv.name',
                'pv.url as panelViewUrl',
                'p.name as panel',
                'p.dimensions',
                'st.name as support',
                'lt.name as lighting',
                'c.name as city',
                'CONCAT(a.street,  CONCAT(\' \', a.street2)) as address',
                'MAX(CASE WHEN (ev.id IS NULL) THEN
                    0
                  ELSE
                    ev.status
                  END) as available'
            )
            ->leftJoin('pv.panel', 'p')
            ->leftJoin('p.supportType', 'st')
            ->leftJoin('p.lightingType', 'lt')
            ->leftJoin('p.addresses', 'a')
            ->leftJoin('a.city', 'c')
            ->innerJoin('pv.reservations', 'r')
            ->leftJoin('r.offer', 'o')
            ->leftJoin(
                'pv.events',
                'ev',
                'WITH',
                '(ev.start >= o.start AND ev.start <= o.end) OR (ev.end >= o.start AND ev.end <= o.end) OR (ev.start >= o.start AND ev.end <= o.end) OR (ev.start <= o.start AND ev.end >= o.end)'
            )
            ->where(':reservation MEMBER OF pv.reservations')
            ->groupBy('pv.id')
        ;

        return $qb;
    }
}
