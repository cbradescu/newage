<?php

namespace CB\Bundle\SchedulerBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class SchedulerPropertyRepository extends EntityRepository
{
    /**
     * @param int         $targetSchedulerId
     * @param string|null $alias
     *
     * @return QueryBuilder
     */
    public function getConnectionsByTargetSchedulerQueryBuilder($targetSchedulerId, $alias = null)
    {
        $qb = $this->createQueryBuilder('connection')
            ->where('connection.targetScheduler = :targetSchedulerId')
            ->setParameter('targetSchedulerId', $targetSchedulerId);
        if ($alias) {
            $qb
                ->andWhere('connection.schedulerAlias = :alias')
                ->setParameter('alias', $alias);
        }

        return $qb;
    }
}
