<?php

namespace CB\Bundle\NewAgeBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class PanelViewRepository extends EntityRepository
{
    /**
     * Returns a query builder which can be used to get list of campaigns
     *
     * @param int $organizationId
     *
     * @return QueryBuilder
     */
    public function getPanelViewsQueryBuilder($organizationId)
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.organization = :organizationId')
            ->setParameter('organizationId', $organizationId);
    }
}
