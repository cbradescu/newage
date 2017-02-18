/*global define*/
define([
    'backbone'
], function (Backbone) {
    'use strict';

    var CampaignModel;

    CampaignModel = Backbone.Model.extend({

        defaults: {
            id: null,
            title: null
        }
    });



    return CampaignModel;
});