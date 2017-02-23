define([
    'backbone',
    'routing',
    'cbscheduler/js/scheduler/client/model'
], function(Backbone, routing, ClientModel) {
    'use strict';

    /**
     * @export  cbscheduler/js/scheduler/client/collection
     * @class   cbscheduler.scheduler.client.Collection
     * @extends Backbone.Collection
     */
    return Backbone.Collection.extend({
        route: 'cb_newage_client_api_get_clients',
        url: null,
        model: ClientModel,

        setUrl: function() {
            this.url = routing.generate(
                this.route
            );
        }
    });
});
