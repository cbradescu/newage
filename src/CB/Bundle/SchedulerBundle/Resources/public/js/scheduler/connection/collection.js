define([
    'backbone',
    'routing',
    'cbscheduler/js/scheduler/connection/model'
], function(Backbone, routing, ConnectionModel) {
    'use strict';

    /**
     * @export  cbscheduler/js/scheduler/connection/collection
     * @class   oro.scheduler.connection.Collection
     * @extends Backbone.Collection
     */
    return Backbone.Collection.extend({
        route: 'oro_api_get_scheduler_connections',
        url: null,
        model: ConnectionModel,

        /**
         * Sets a scheduler this collection works with
         *
         * @param {int} schedulerId
         */
        setScheduler: function(schedulerId) {
            this.url = routing.generate(this.route, {id: schedulerId});
        }
    });
});
