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
        return $this->createQueryBuilder('c')
            ->select(
                'c.id',
                'c.name'
            )
            ;
    }
}
