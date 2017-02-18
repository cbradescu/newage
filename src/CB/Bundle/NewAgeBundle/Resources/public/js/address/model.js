define([
    'backbone'
], function(Backbone) {
    'use strict';

    /**
     * @export  oroaddress/js/address/model
     * @class   oroaddress.address.Model
     * @extends Backbone.Model
     */
    return Backbone.Model.extend({
        defaults: {
            street: '',
            street2: '',
            city: '',
            latitude: '',
            longitude: '',
            primary: false,
            active: false
        },

        getSearchableString: function() {
            return this.get('city') + ', ' +
                this.get('street') + ' ' + (this.get('street2') || '');
        }
    });
});
