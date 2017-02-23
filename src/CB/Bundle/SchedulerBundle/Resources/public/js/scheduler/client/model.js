/*global define*/
define([
    'backbone'
], function (Backbone) {
    'use strict';

    var ClientModel;

    ClientModel = Backbone.Model.extend({

        defaults: {
            id: null,
            title: null
        }
    });



    return ClientModel;
});