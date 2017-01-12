define([
    'underscore',
    'oroui/js/messenger',
    'orotranslation/js/translator',
    'oro/datagrid/action/mass-action'
], function(_, messenger, __, MassAction) {
    'use strict';

    var ConfirmAction;

    /**
     * Confirm panel views
     *
     * @export  cbnewage/js/datagrid/action/confirm-mass-action
     * @class   oro.datagrid.action.ConfirmAction
     * @extends oro.datagrid.action.MassAction
     */
    ConfirmAction = MassAction.extend({
        /** @property {Object} */
        defaultMessages: {
            confirm_title: 'Mass Action Confirmation',
            confirm_content: 'Are you sure you want to do this?',
            confirm_ok: 'Yes, do it',
            confirm_cancel: 'Cancel',
            success: 'Mass action performed.',
            error: 'Mass action is not performed.',
            empty_selection: 'Please, select items to perform mass action.',
            confirmed_selection: 'You have confirmed already confirmed panel views.'
        },


        /**
         * Ask a confirmation and execute mass action.
         */
        execute: function() {
            if (this.checkSelectionState()) {
                MassAction.__super__.execute.call(this);
            }
        },

        /**
         * Checks if any records are selected.
         *
         * @returns {boolean}
         */
        checkSelectionState: function() {
            var selectionState = this.datagrid.getSelectionState();

            if (selectionState.selectedIds.length === 0 && selectionState.inset) {
                messenger.notificationFlashMessage('warning', __(this.messages.empty_selection));
                return false;
            } else {
                var data = this.datagrid.collection.models;
                var selectedIds = {};
                _.each(selectionState.selectedIds, function (sel) { selectedIds[sel] = true; });

                var confirmedIds = _.filter(data, function (val) {
                    var available = val.attributes['available'];
                    return selectedIds[val.id] && ~available.indexOf('badge-disable');
                });

                if (confirmedIds.length > 0) {
                    messenger.notificationFlashMessage('danger', __(this.messages.confirmed_selection));
                    return false;
                }
            }
            return false;
        },

    });

    return ConfirmAction;
});
