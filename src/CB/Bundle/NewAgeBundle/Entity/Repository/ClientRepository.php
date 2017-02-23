<?php

namespace CB\Bundle\NewAgeBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class ClientRepository extends EntityRepository
{
    /**
     * Returns a query builder which can be used to get list of clients
     *
     * @param int $organizationId
     *
     * @return QueryBuilder
     */
    public function getClientsQueryBuilder($organizationId)
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.organization = :organizationId')
            ->setParameter('organizationId', $organizationId);
    }
}
