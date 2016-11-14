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
            ->setParameter('organizationId', $organizationId);
        ;

        return $qb;
    }
}
