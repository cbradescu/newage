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
         * Sets a range of scheduler events this collection works with
         *
         * @param {string} start A date/time specifies the begin of a range. RFC 3339 string
         * @param {string} end   A date/time specifies the end of a range. RFC 3339 string
         */
        setRange: function(start, end) {
            this.url = routing.generate(
                this.route,
                {start: start, end: end}
            );
        }
    });
});
