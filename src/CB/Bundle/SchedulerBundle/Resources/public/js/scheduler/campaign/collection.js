define([
    'backbone',
    'routing',
    'cbscheduler/js/scheduler/campaign/model'
], function(Backbone, routing, CampaignModel) {
    'use strict';

    /**
     * @export  cbscheduler/js/scheduler/campaign/collection
     * @class   cbscheduler.scheduler.campaign.Collection
     * @extends Backbone.Collection
     */
    return Backbone.Collection.extend({
        route: 'cb_newage_campaign_api_get_campaigns',
        url: null,
        model: CampaignModel,

        setUrl: function() {
            this.url = routing.generate(
                this.route
            );
        }
    });
});
