define([
    'backbone',
    'routing',
    'cbscheduler/js/scheduler/event/model'
], function(Backbone, routing, EventModel) {
    'use strict';

    /**
     * @export  cbscheduler/js/scheduler/event/collection
     * @class   cbscheduler.scheduler.event.Collection
     * @extends Backbone.Collection
     */
    return Backbone.Collection.extend({
        route: 'cb_api_get_schedulerevents',
        url: null,
        model: EventModel,

        /**
         * Scheduler id
         * @property {int}
         */
        scheduler: null,

        /**
         * Determines whether events from connected schedulers should be included or not
         * @property {bool}
         */
        subordinate: false,

        /**
         * Sets a range of scheduler events this collection works with
         *
         * @param {string} start A date/time specifies the begin of a range. RFC 3339 string
         * @param {string} end   A date/time specifies the end of a range. RFC 3339 string
         */
        setRange: function(start, end) {
            this.url = routing.generate(
                this.route,
                {scheduler: this.scheduler, start: start, end: end, subordinate: this.subordinate}
            );
        },

        /**
         * Sets a scheduler this collection works with
         *
         * @param {int} schedulerId
         */
        setScheduler: function(schedulerId) {
            this.scheduler = schedulerId;
        },

        /**
         * Gets a scheduler this collection works with
         *
         * @return {int} The scheduler id
         */
        getScheduler: function() {
            return this.scheduler;
        }
    });
});
