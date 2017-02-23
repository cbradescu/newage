define([
    'underscore',
    'backbone',
    'routing',
    'moment'
], function(_, Backbone, routing, moment) {
    'use strict';

    var EventModel;

    /**
     * @export  cbscheduler/js/scheduler/event/model
     * @class   cbscheduler.scheduler.event.Model
     * @extends Backbone.Model
     */
    EventModel = Backbone.Model.extend({
        route: 'cb_api_get_schedulerevents',
        urlRoot: null,

        defaults: {
            id: null,
            title: null, // client name
            start: null,
            end: null,
            resourceId: null, // panel view id
            resourceName: null, //panel view name
            panelView: null,
            client: null,
            status: 0,
            backgroundColor: '#ffff99',
            editable: false,
            removable: false
        },

        initialize: function() {
            this.urlRoot = routing.generate(this.route);
        },

        url: function() {
            var url;

            url = Backbone.Model.prototype.url.call(this, arguments);

            return url;
        },

        save: function(key, val, options) {
            var attrs;
            var modelData;

            // Handle both `"key", value` and `{key: value}` -style arguments.
            if (key === null || key === undefined || typeof key === 'object') {
                attrs = key || {};
                options = val;
            } else {
                attrs = {};
                attrs[key] = val;
            }

            modelData = _.extend(
                {},
                _.omit(
                    this.toJSON(),
                    ['id', 'title', 'resourceId', 'resourceName', 'editable', 'removable', 'allDay', 'backgroundColor', 'lightingType', 'supportType', 'panel']
                ),
                attrs || {}
            );

            options.contentType = 'application/json';
            options.data = JSON.stringify(modelData);

            Backbone.Model.prototype.save.call(this, attrs, options);
        },

        validate: function(attrs) {
            var errors = [];

            if (!attrs.client) {
                errors.push('cb.scheduler.error_message.scheduler_event_model.client_not_blank');
            }

            if (moment(attrs.end).diff(attrs.start) < 0) {
                errors.push('cb.scheduler.error_message.event_model.end_date_earlier_than_start');
            }

            return errors.length ? errors : null;
        }
    });

    return EventModel;
});
