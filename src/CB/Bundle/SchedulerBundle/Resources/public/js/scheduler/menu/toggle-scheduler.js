define(['oroui/js/app/views/base/view'
    ], function(BaseView) {
    'use strict';

    /**
     * @export  cbscheduler/js/scheduler/menu/toggle-scheduler
     * @class   cbscheduler.scheduler.menu.ToggleScheduler
     * @extends oroui/js/app/views/base/view
     */
    return BaseView.extend({

        initialize: function(options) {
            this.connectionsView = options.connectionsView;
        },

        execute: function(model) {
            this.connectionsView.toggleScheduler(model);
        }
    });
});
