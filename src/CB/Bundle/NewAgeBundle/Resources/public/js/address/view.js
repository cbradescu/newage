define([
    'underscore',
    'backbone',
    'orotranslation/js/translator',
    'oroui/js/mediator',
    'orolocale/js/formatter/address'
], function(_, Backbone, __, mediator, addressFormatter) {
    'use strict';

    var $ = Backbone.$;

    /**
     * @export  oroaddress/js/address/view
     * @class   oroaddress.address.View
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        tagName: 'div',

        attributes: {
            'class': 'list-item map-item'
        },

        events: {
            'click': 'activate',
            'click .item-edit-button': 'edit',
            'click .item-remove-button': 'close'
        },

        options: {
            map: {
                'street': 'street',
                'street2': 'street2',
                'city': 'city',
                'latittude': 'latitude',
                'longitude': 'longitude'
            }
        },

        initialize: function(options) {
            this.options.map = _.defaults(options.map || {}, this.options.map);
            this.$el.attr('id', 'address-book-' + this.model.id);
            this.template = _.template($('#template-addressbook-item').html());
            this.listenTo(this.model, 'destroy', this.remove);
            this.listenTo(this.model, 'change:active', this.toggleActive);
        },

        activate: function() {
            this.model.set('active', true);
        },

        toggleActive: function() {
            if (this.model.get('active')) {
                this.$el.addClass('active');
            } else {
                this.$el.removeClass('active');
            }
        },

        edit: function() {
            this.trigger('edit', this, this.model);
        },

        close: function() {
            if (this.model.get('primary')) {
                mediator.execute('showErrorMessage', __('Primary address can not be removed'));
            } else {
                this.model.destroy({wait: true});
            }
        },

        render: function() {
            var data = this.model.toJSON();
            var mappedData = this.prepareData(data);
            data.formatted_address = addressFormatter.format(mappedData, null, '\n');
            this.$el.append(this.template(data));
            if (this.model.get('primary')) {
                this.activate();
            }
            return this;
        },

        prepareData: function(data) {
            var mappedData = {};
            var map = this.options.map;

            if (data) {
                _.each(data, function(value, key) {
                    if (map[key]) {
                        mappedData[map[key]] = mappedData[map[key]] || value;
                    }
                });
            }

            return mappedData;
        }
    });
});
