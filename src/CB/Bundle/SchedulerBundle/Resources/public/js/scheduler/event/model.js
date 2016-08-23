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
            // original id is copied to originalId property and this attribute is replaced with schedulerUid + originalId
            id: null,
            title: null,
            description: null,
            start: null,
            end: null,
            allDay: false,
            backgroundColor: null,
            reminders: {},
            parentEventId: null,
            invitationStatus: null,
            invitedUsers: null,
            editable: false,
            removable: false,
            schedulerAlias: null,
            scheduler: null, // schedulerId
            schedulerUid: null // calculated automatically, equals to schedulerAlias + schedulerId
        },

        initialize: function() {
            this.urlRoot = routing.generate(this.route);
            this._updateComputableAttributes();
            this.on('change:id change:schedulerAlias change:scheduler', this._updateComputableAttributes, this);
        },

        url: function() {
            var url;
            var id = this.id;

            this.id = this.originalId;
            url = Backbone.Model.prototype.url.call(this, arguments);
            this.id = id;

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
                {id: this.originalId},
                _.omit(
                    this.toJSON(),
                    ['id', 'editable', 'removable', 'schedulerUid', 'parentEventId', 'invitationStatus']
                ),
                attrs || {}
            );
            modelData.invitedUsers = modelData.invitedUsers ? modelData.invitedUsers.join(',') : undefined;

            options.contentType = 'application/json';
            options.data = JSON.stringify(modelData);

            Backbone.Model.prototype.save.call(this, attrs, options);
        },

        _updateComputableAttributes: function() {
            var schedulerAlias = this.get('schedulerAlias');
            var schedulerId = this.get('scheduler');
            var schedulerUid = schedulerAlias && schedulerId ? schedulerAlias + '_' + schedulerId : null;

            this.set('schedulerUid', schedulerUid);

            if (!this.originalId && this.id && schedulerUid) {
                this.originalId = this.id;
                this.set('id', schedulerUid + '_' + this.originalId);
            }
        },

        validate: function(attrs) {
            var errors = [];

            if (moment(attrs.end).diff(attrs.start) < 0) {
                errors.push('oro.scheduler.error_message.event_model.end_date_earlier_than_start');
            }

            return errors.length ? errors : null;
        },

        getInvitationStatus: function() {
            var invitationStatus = this.get('invitationStatus');
            var invitedUsers = this.get('invitedUsers');
            if (!invitationStatus && invitedUsers && invitedUsers.length) {
                invitationStatus = 'accepted';
            }
            return invitationStatus;
        }
    });

    return EventModel;
});
