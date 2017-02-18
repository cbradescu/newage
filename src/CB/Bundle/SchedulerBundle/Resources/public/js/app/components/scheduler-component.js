define(function(require) {
    'use strict';

    var _ = require('underscore');
    var BaseComponent = require('oroui/js/app/components/base/component');
    var SchedulerView = require('cbscheduler/js/scheduler-view');
    var EventCollection = require('cbscheduler/js/scheduler/event/collection');

    /**
     * Creates scheduler
     */
    var SchedulerComponent = BaseComponent.extend({

        /**
         * @type {cbscheduler.js.scheduler}
         */
        scheduler: null,

        /**
         * @type {EventCollection}
         */
        eventCollection: null,

        /**
         * @constructor
         * @param {Object} options
         */
        initialize: function(options) {
            this.options = options;
            if (!this.options.el) {
                this.options.el = this.options._sourceElement;
            }
            this.eventCollection = new EventCollection(JSON.parse(this.options.eventsItemsJson));
            delete this.options.eventsItemsJson;
            delete this.options.connectionsItemsJson;
            this.prepareOptions();
            this.renderScheduler();
        },
        prepareOptions: function() {
            var options = this.options;
            options.collection = this.eventCollection;
            options.scrollToCurrentTime = true;

            options.eventsOptions.header = {
                left: options.eventsOptions.leftHeader || '',
                center: options.eventsOptions.centerHeader || '',
                right: options.eventsOptions.rightHeader || ''
            };

            _.extend(options.eventsOptions, options.schedulerOptions);

            delete options.schedulerOptions;
            delete options.eventsOptions.centerHeader;
            delete options.eventsOptions.leftHeader;
            delete options.eventsOptions.rightHeader;
        },
        renderScheduler: function() {
            this.scheduler = new SchedulerView(this.options);
            this.scheduler.render();
        }
    });

    return SchedulerComponent;
});
