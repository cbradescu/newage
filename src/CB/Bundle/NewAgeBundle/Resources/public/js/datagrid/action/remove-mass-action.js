define([
    'underscore',
    'oroui/js/messenger',
    'orotranslation/js/translator',
    'oro/datagrid/action/mass-action'
], function(_, messenger, __, MassAction) {
    'use strict';

    var RemoveAction;

    /**
     * Remove panel views
     *
     * @export  cbnewage/js/datagrid/action/remove-mass-action
     * @class   oro.datagrid.action.RemoveAction
     * @extends oro.datagrid.action.MassAction
     */
    RemoveAction = MassAction.extend({

    });

    return RemoveAction;
});
