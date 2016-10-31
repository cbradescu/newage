<?php

namespace CB\Bundle\NewAgeBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class PanelViewRepository extends EntityRepository
{
    /**
     * Returns a query builder which can be used to get list of panel views
     *
     * @return QueryBuilder
     */
    public function getPanelViewsQueryBuilder()
    {
        $qb = $this->createQueryBuilder('c')
            ->select(
                'c.id',
                'c.name',
                'IDENTITY(p.supportType)',
                'IDENTITY(p.lightingType)',
                'a.city'
            )
            ->leftJoin('c.panel', 'p')
            ->leftJoin('p.addresses', 'a')
        ;

        return $qb;
    }
}
