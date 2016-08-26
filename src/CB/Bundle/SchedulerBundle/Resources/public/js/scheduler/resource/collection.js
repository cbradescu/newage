define([
    'backbone',
    'routing',
    'cbscheduler/js/scheduler/resource/model'
], function(Backbone, routing, ResourceModel) {
    'use strict';

    /**
     * @export  cbscheduler/js/scheduler/event/collection
     * @class   cbscheduler.scheduler.event.Collection
     * @extends Backbone.Collection
     */
    return Backbone.Collection.extend({
        route: 'cb_newage_panel_view_api_get_panel_views',
        url: null,
        model: ResourceModel,

        setUrl: function() {
            this.url = routing.generate(
                this.route
            );
        }
    });
});
