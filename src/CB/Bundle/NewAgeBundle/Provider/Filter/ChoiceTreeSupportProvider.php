<?php

namespace CB\Bundle\NewAgeBundle\Provider\Filter;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\LocaleBundle\DQL\DQLNameFormatter;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

class ChoiceTreeSupportProvider
{
    const CLASS_NAME = 'CB\Bundle\NewAgeBundle\Entity\SupportType';

    /** @var Registry */
    protected $registry;

    /** @var AclHelper */
    protected $aclHelper;

    /** @var DQLNameFormatter */
    protected $dqlNameFormatter;

    /**
     * @param Registry $registry
     * @param AclHelper $aclHelper
     * @param DQLNameFormatter $dqlNameFormatter
     */
    public function __construct(Registry $registry, AclHelper $aclHelper, DQLNameFormatter $dqlNameFormatter)
    {
        $this->registry = $registry;
        $this->aclHelper = $aclHelper;
        $this->dqlNameFormatter = $dqlNameFormatter;
    }

    /**
     * @return array
     */
    public function getList()
    {
        $qb = $this->createListQb();

        return $this->aclHelper->apply($qb)->getArrayResult();
    }

    /**
     * @return bool
     */
    public function shouldBeLazy()
    {
        $qb = $this->createListQb()
            ->select('COUNT(1)');

        return $this->aclHelper->apply($qb)->getSingleScalarResult() >= 500;
    }

    /**
     * @return QueryBuilder
     */
    protected function createListQb()
    {
        return $this->registry->getManager()->getRepository(static::CLASS_NAME)->createQueryBuilder('st')
            ->select('st.id', 'st.name');
    }
}
