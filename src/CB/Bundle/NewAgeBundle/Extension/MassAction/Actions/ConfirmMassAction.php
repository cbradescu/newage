<?php

namespace CB\Bundle\NewAgeBundle\Extension\MassAction\Actions;

use Oro\Bundle\DataGridBundle\Extension\Action\ActionConfiguration;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\AbstractMassAction;

class ConfirmMassAction extends AbstractMassAction
{
    /** @var array */
    protected $requiredOptions = ['handler', 'entity_name', 'data_identifier'];

    /**
     * {@inheritDoc}
     */
    public function setOptions(ActionConfiguration $options)
    {
        if (empty($options['handler'])) {
            $options['handler'] = 'cb_newage.mass_action.confirm_handler';
        }

        if (empty($options['frontend_type'])) {
            $options['frontend_type'] = 'confirm-mass';
        }

        if (empty($options['route'])) {
            $options['route'] = 'cb_confirm_massaction';
        }

        if (empty($options['route_parameters'])) {
            $options['route_parameters'] = [];
        }

        if (empty($options['frontend_handle'])) {
            $options['frontend_handle'] = 'redirect';
        }

        return parent::setOptions($options);
    }
}
