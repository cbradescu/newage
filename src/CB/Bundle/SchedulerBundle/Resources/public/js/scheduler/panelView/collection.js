define([
    'backbone',
    'routing',
    'cbscheduler/js/scheduler/panelView/model'
], function(Backbone, routing, PanelViewModel) {
    'use strict';

    /**
     * @export  cbscheduler/js/scheduler/panelView/collection
     * @class   cbscheduler.scheduler.panelView.Collection
     * @extends Backbone.Collection
     */
    return Backbone.Collection.extend({
        route: 'cb_newage_panel_view_api_get_panel_views',
        url: null,
        model: PanelViewModel,

        setUrl: function() {
            this.url = routing.generate(
                this.route
            );
        },
        setFilters: function(offer, panel, panelView, supportType, lightingType, city) {
            this.url = routing.generate(
                this.route,
                {offer: offer, panel: panel, id: panelView, supportType: supportType, lightingType: lightingType, city: city}
            );
        }
    });
});
