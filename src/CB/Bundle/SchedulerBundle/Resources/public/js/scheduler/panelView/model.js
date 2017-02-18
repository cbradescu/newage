define([
    'underscore',
    'backbone',
    'routing'
], function(_, Backbone, routing) {
    'use strict';

    var PanelViewModel;

    /**
     * @export  cbscheduler/js/scheduler/resource/model
     * @class   cbscheduler.scheduler.resource.Model
     * @extends Backbone.Model
     */
    PanelViewModel = Backbone.Model.extend({
        route: 'cb_newage_panel_view_api_get_panel_views',
        urlRoot: null,

        defaults: {
            id: null,
            name: null,
            panel: null,
            owner: null,
            organization: null
        },

        initialize: function() {
            this.urlRoot = routing.generate(this.route);
        },

        url: function() {
            var url;

            url = Backbone.Model.prototype.url.call(this, arguments);

            return url;
        }
    });

    return PanelViewModel;
});
