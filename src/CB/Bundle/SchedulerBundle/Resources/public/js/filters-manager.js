define(function(require) {
    'use strict';

    var CBFiltersManager;
    var FiltersManager = require('orofilter/js/filters-manager');

    CBFiltersManager = FiltersManager.extend({
        /* define extended logic here */

        /**
         * Triggers when filter is updated
         *
         * @param {oro.filter.AbstractFilter} filter
         * @protected
         */
        _onFilterUpdated: function(filter) {
            // start
            var activeFilters = [];

            _.each(this.filters, function(filter, name) {
                if (filter.enabled && !_.isObject(filter.value.value) && filter.value.value) {
                    activeFilters.push({name: name, value: filter.value.value});
                }

                if (_.isObject(filter.value.value)) {
                    switch (name) {
                        case 'city':
                        case 'supportType':
                        case 'lightingType':
                            var value = filter.value.value.join();

                            if (value) {
                                activeFilters.push({name: name, value: value});
                            }
                            break;
                    }
                }
            }, this);

            mediator.trigger('setSchedulerFilters', activeFilters);
            // end

            this._resetHintContainer();
            this.trigger('updateFilter', filter);
        }
    });

    return CBFiltersManager;
});
