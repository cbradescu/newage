define(function(require) {
    'use strict';

    var SchedulerEventGuestCollection;
    var RoutingCollection = require('oroui/js/app/models/base/routing-collection');

    SchedulerEventGuestCollection = RoutingCollection.extend({
        routeDefaults: {
            routeName: 'oro_api_get_schedulerevents_guests',
            routeQueryParameterNames: ['page', 'limit']
        },

        stateDefaults: {
            page: 1
        },

        parse: function(response) {
            return response;
        },

        setPage: function(page) {
            this._route.set('page', page);
        }
    });

    return SchedulerEventGuestCollection;
});
