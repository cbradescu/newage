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
        originalId: null, // original id received from a server

        defaults: {
            id: null,
            title: null, // campaign name
            start: null,
            end: null,
            resourceId: null, // panel view id
            resourceName: null, //panel view name
            panelView: null,
            campaign: null,
            backgroundColor: null,
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
                    ['id', 'title', 'resourceId', 'resourceName', 'editable', 'removable', 'allDay', 'backgroundColor']
                ),
                attrs || {}
            );

            options.contentType = 'application/json';
            options.data = JSON.stringify(modelData);

            Backbone.Model.prototype.save.call(this, attrs, options);
        },

        validate: function(attrs) {
            var errors = [];

            if (!attrs.campaign) {
                errors.push('cb.scheduler.error_message.scheduler_event_model.campaign_not_blank');
            }

            if (moment(attrs.end).diff(attrs.start) < 0) {
                errors.push('cb.scheduler.error_message.event_model.end_date_earlier_than_start');
            }

            return errors.length ? errors : null;
        }
    });

    return EventModel;
});
