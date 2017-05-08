<?php

namespace CB\Bundle\NewAgeBundle\Extension\MassAction\Actions;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\AbstractMassAction;

class RemoveMassAction extends AbstractMassAction
{
    /** @var array */
    protected $requiredOptions = ['handler', 'entity_name', 'data_identifier'];

    /**
     * {@inheritDoc}
     */
    public function setOptions(ActionConfiguration $options)
    {
        if (empty($options['handler'])) {
            $options['handler'] = 'cb_newage.mass_action.remove_handler';
        }

        if (empty($options['frontend_type'])) {
            $options['frontend_type'] = 'remove-mass';
        }

        if (empty($options['route'])) {
            $options['route'] = 'cb_remove_mass_action';
        }

        if (empty($options['route_parameters'])) {
            $options['route_parameters'] = [];
        }

        if (empty($options['frontend_handle'])) {
            $options['frontend_handle'] = 'redirect';
        }

        if (empty($options['confirmation'])) {
            $options['confirmation'] = true;
        }
        return parent::setOptions($options);
    }
}