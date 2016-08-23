<?php

namespace CB\Bundle\SchedulerBundle\Provider;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;

abstract class AbstractSchedulerProvider implements SchedulerProviderInterface
{
    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @param DoctrineHelper $doctrineHelper */
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * @param string $className
     *
     * @return array
     */
    protected function getSupportedFields($className)
    {
        $classMetadata = $this->doctrineHelper->getEntityMetadata($className);

        return $classMetadata->fieldNames;
    }

    /**
     * @param        $extraFields
     *
     * @param string $class
     *
     * @return array
     */
    protected function filterSupportedFields($extraFields, $class)
    {
        $extraFields = !empty($extraFields)
            ? array_intersect($extraFields, $this->getSupportedFields($class))
            : [];

        return $extraFields;
    }
}
