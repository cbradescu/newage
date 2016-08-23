define([
    'underscore',
    'backbone',
    'routing'
], function(_, Backbone, routing) {
    'use strict';

    /**
     * @export  cbscheduler/js/scheduler/connection/model
     * @class   cbscheduler.scheduler.connection.Model
     * @extends Backbone.Model
     */
    return Backbone.Model.extend({
        route: 'oro_api_post_scheduler_connection',
        urlRoot: null,

        /**
         * This property can be used to indicate whether scheduler events
         * should be reloaded or not after a scheduler connection is changed.
         * To force events reloading set this property to true.
         * To prohibit events reloading set this property to false.
         * @property
         */
        reloadEventsRequest: null,

        defaults: {
            id: null,
            targetScheduler: null,
            schedulerAlias: null,
            scheduler: null, // schedulerId
            schedulerUid: null, // calculated automatically, equals to schedulerAlias + schedulerId
            position: 0,
            visible: true,
            backgroundColor: null,
            schedulerName: null,
            userId: null,
            removable: true,
            canAddEvent: false,
            canEditEvent: false,
            canDeleteEvent: false,
            options: null
        },

        initialize: function() {
            this.urlRoot = routing.generate(this.route);
            this._updateSchedulerUidAttribute();
            this.on('change:schedulerAlias change:scheduler', this._updateSchedulerUidAttribute, this);
        },

        save: function(key, val, options) {
            var attrs;

            // Handle both `"key", value` and `{key: value}` -style arguments.
            if (key === null || key === undefined || typeof key === 'object') {
                attrs = key;
                options = val;
            } else {
                attrs = {};
                attrs[key] = val;
            }

            options.contentType = 'application/json';
            options.data = JSON.stringify(
                _.extend({}, _.omit(
                    this.toJSON(),
                    ['schedulerUid', 'schedulerName', 'userId', 'removable',
                        'canAddEvent', 'canEditEvent', 'canDeleteEvent']
                ), attrs || {})
            );

            Backbone.Model.prototype.save.call(this, attrs, options);
        },

        toJSON: function(options) {
            return _.omit(Backbone.Model.prototype.toJSON.call(this, options), ['options']);
        },

        _updateSchedulerUidAttribute: function() {
            var schedulerAlias = this.get('schedulerAlias');
            var schedulerId = this.get('scheduler');
            var schedulerUid = schedulerAlias && schedulerId ? schedulerAlias + '_' + schedulerId : null;
            this.set('schedulerUid', schedulerUid);
        }
    });
});
