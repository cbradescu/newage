define([
    'underscore',
    'oroui/js/messenger',
    'orotranslation/js/translator',
    'oro/datagrid/action/mass-action'
], function(_, messenger, __, MassAction) {
    'use strict';

    var CancelAction;

    /**
     * Confirm panel views
     *
     * @export  cbnewage/js/datagrid/action/confirm-mass-action
     * @class   oro.datagrid.action.ConfirmAction
     * @extends oro.datagrid.action.MassAction
     */
    CancelAction = MassAction.extend({

    });

    return CancelAction;
});
