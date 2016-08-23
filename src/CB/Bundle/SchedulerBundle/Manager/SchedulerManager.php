<?php

namespace CB\Bundle\SchedulerBundle\Manager;

use Oro\Component\PhpUtils\ArrayUtil;

use CB\Bundle\SchedulerBundle\Provider\SchedulerPropertyProvider;
use CB\Bundle\SchedulerBundle\Provider\SchedulerProviderInterface;

class SchedulerManager
{
    /** @var SchedulerPropertyProvider */
    protected $schedulerPropertyProvider;

    /** @var SchedulerProviderInterface[] */
    protected $providers = [];

    /**
     * @param SchedulerPropertyProvider $schedulerPropertyProvider
     */
    public function __construct(SchedulerPropertyProvider $schedulerPropertyProvider)
    {
        $this->schedulerPropertyProvider = $schedulerPropertyProvider;
    }

    /**
     * Registers the given provider in the chain
     *
     * @param string                    $alias
     * @param SchedulerProviderInterface $provider
     */
    public function addProvider($alias, SchedulerProviderInterface $provider)
    {
        $this->providers[$alias] = $provider;
    }

    /**
     * Gets schedulers connected to the given scheduler
     *
     * @param int $organizationId The id of an organization for which this information is requested
     * @param int $userId         The id of an user requested this information
     * @param int $schedulerId     The target scheduler id
     *
     * @return array
     */
    public function getSchedulers($organizationId, $userId, $schedulerId)
    {
        // make sure input parameters have proper types
        $userId     = (int)$userId;
        $schedulerId = (int)$schedulerId;

        $result = $this->schedulerPropertyProvider->getItems($schedulerId);

        $existing = [];
        foreach ($result as $key => $item) {
            $existing[$item['schedulerAlias']][$item['scheduler']] = $key;
        }

        foreach ($this->providers as $alias => $provider) {
            $schedulerIds           = isset($existing[$alias]) ? array_keys($existing[$alias]) : [];
            $schedulerDefaultValues = $provider->getSchedulerDefaultValues(
                $organizationId,
                $userId,
                $schedulerId,
                $schedulerIds
            );
            foreach ($schedulerDefaultValues as $id => $values) {
                if (isset($existing[$alias][$id])) {
                    $key = $existing[$alias][$id];
                    if ($values !== null) {
                        $scheduler = $result[$key];
                        $this->applySchedulerDefaultValues($scheduler, $values);
                        $result[$key] = $scheduler;
                    } else {
                        unset($result[$key]);
                    }
                } else {
                    $values['targetScheduler'] = $schedulerId;
                    $values['schedulerAlias']  = $alias;
                    $values['scheduler']       = $id;
                    $result[]                 = $values;
                }
            }
        }

        $this->normalizeSchedulerData($result);

        return $result;
    }

    /**
     * Gets the list of scheduler events
     *
     * @param int       $organizationId The id of an organization for which this information is requested
     * @param int       $userId         The id of an user requested this information
     * @param int       $schedulerId     The target scheduler id
     * @param \DateTime $start          A date/time specifies the begin of a time interval
     * @param \DateTime $end            A date/time specifies the end of a time interval
     * @param bool      $subordinate    Determines whether events from connected schedulers should be included or not
     * @param array     $extraFields
     *
     * @return array
     */
    public function getSchedulerEvents(
        $organizationId,
        $userId,
        $schedulerId,
        $start,
        $end,
        $subordinate,
        $extraFields = []
    ) {
        // make sure input parameters have proper types
        $schedulerId = (int)$schedulerId;
        $subordinate = (bool)$subordinate;

        $allConnections = $this->schedulerPropertyProvider->getItemsVisibility($schedulerId, $subordinate);

        $result = [];

        foreach ($this->providers as $alias => $provider) {
            $connections = [];
            foreach ($allConnections as $c) {
                if ($c['schedulerAlias'] === $alias) {
                    $connections[$c['scheduler']] = $c['visible'];
                }
            }
            $events = $provider->getSchedulerEvents(
                $organizationId,
                $userId,
                $schedulerId,
                $start,
                $end,
                $connections,
                $extraFields
            );
            if (!empty($events)) {
                foreach ($events as &$event) {
                    $event['schedulerAlias'] = $alias;
                    if (!isset($event['editable'])) {
                        $event['editable'] = true;
                    }
                    if (!isset($event['removable'])) {
                        $event['removable'] = true;
                    }
                    if (!isset($event['notifiable'])) {
                        $event['notifiable'] = false;
                    }
                }
                $result = array_merge($result, $events);
            }
        }

        return $result;
    }

    /**
     * @param array $schedulers
     */
    protected function normalizeSchedulerData(array &$schedulers)
    {
        // apply default values and remove redundant properties
        $defaultValues = $this->getSchedulerDefaultValues();
        foreach ($schedulers as &$scheduler) {
            $this->applySchedulerDefaultValues($scheduler, $defaultValues);
        }

        ArrayUtil::sortBy($schedulers, false, 'position');
    }

    /**
     * @param array $scheduler
     * @param array $defaultValues
     */
    protected function applySchedulerDefaultValues(array &$scheduler, array $defaultValues)
    {
        foreach ($defaultValues as $fieldName => $val) {
            // set default value for a field if the field does not exists or it's value is null
            if (!isset($scheduler[$fieldName])) {
                $scheduler[$fieldName] = is_callable($val)
                    ? call_user_func($val, $fieldName)
                    : $val;
            }
        }
    }

    /**
     * @return array
     */
    protected function getSchedulerDefaultValues()
    {
        $result = $this->schedulerPropertyProvider->getDefaultValues();

        $result['schedulerName'] = null;
        $result['removable']    = true;

        return $result;
    }
}
