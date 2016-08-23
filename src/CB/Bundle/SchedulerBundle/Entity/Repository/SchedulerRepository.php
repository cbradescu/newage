<?php

namespace CB\Bundle\SchedulerBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

use Doctrine\ORM\QueryBuilder;
use CB\Bundle\SchedulerBundle\Entity\Scheduler;

class SchedulerRepository extends EntityRepository
{
    /**
     * Gets user's default scheduler
     *
     * @param int $campaignId
     * @param int $organizationId
     *
     * @return Scheduler|null
     */
    public function findDefaultScheduler($campaignId, $organizationId)
    {
        return $this->findOneBy(
            array(
                'owner'        => $campaignId,
                'organization' => $organizationId
            )
        );
    }

    /**
     * Gets default schedulers for the given users
     *
     * @param int[] $userIds
     * @param int   $organizationId
     *
     * @return Scheduler[]
     */
    public function findDefaultSchedulers(array $userIds, $organizationId)
    {
        $queryBuilder = $this->createQueryBuilder('c');

        return $queryBuilder
            ->andWhere('c.organization = :organization')->setParameter('organization', $organizationId)
            ->andWhere($queryBuilder->expr()->in('c.owner', $userIds))
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns a query builder which can be used to get all user's schedulers
     *
     * @param int $organizationId
     * @param int $userId
     *
     * @return QueryBuilder
     */
    public function getUserSchedulersQueryBuilder($organizationId, $userId)
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.organization = :organizationId AND c.owner = :userId')
            ->setParameter('organizationId', $organizationId)
            ->setParameter('userId', $userId);
    }
}
