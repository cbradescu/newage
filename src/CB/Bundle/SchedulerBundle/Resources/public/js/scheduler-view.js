define(function(require) {
    'use strict';

    var SchedulerView;
    var _ = require('underscore');
    var $ = require('jquery');
    var moment = require('moment');
    var __ = require('orotranslation/js/translator');
    var tools = require('oroui/js/tools');
    var messenger = require('oroui/js/messenger');
    var mediator = require('oroui/js/mediator');
    var LoadingMask = require('oroui/js/app/views/loading-mask-view');
    var BaseView = require('oroui/js/app/views/base/view');
    var EventCollection = require('cbscheduler/js/scheduler/event/collection');
    var ResourceCollection = require('cbscheduler/js/scheduler/panelView/collection');
    var ClientCollection = require('cbscheduler/js/scheduler/client/collection');
    var EventModel = require('cbscheduler/js/scheduler/event/model');
    var EventView = require('cbscheduler/js/scheduler/event/view');
    var eventDecorator = require('cbscheduler/js/scheduler/event-decorator');
    var colorUtil = require('oroui/js/tools/color-util');
    var dateTimeFormatter = require('orolocale/js/formatter/datetime');
    var localeSettings = require('orolocale/js/locale-settings');

    require('fullscheduler');

    SchedulerView = BaseView.extend({
        // MOMENT_BACKEND_FORMAT: dateTimeFormatter.getBackendDateTimeFormat(),
        MOMENT_BACKEND_FORMAT: dateTimeFormatter.getBackendDateTimeFormat(),
        /** @property */
        eventsTemplate: _.template(
            '<div>' +
            '<div class="calendar-container">' +
            '<div class="calendar"></div>' +
            '</div>' +
            '</div>'
        ),

        /** @property {Object} */
        selectors: {
            calendar:           '.calendar',
            loadingMask:        '.loading-mask',
            loadingMaskContent: '.loading-content'
        },

        /** @property {Object} */
        options: {
            timezone: localeSettings.getTimeZone(),
            eventsOptions: {
                defaultView: 'timelineWeek',
                allDayText: __('oro.calendar.control.all_day'),
                buttonText: {
                    today: __('oro.calendar.control.today'),
                    month: __('oro.calendar.control.month'),
                    week: __('oro.calendar.control.week'),
                    day: __('oro.calendar.control.day')
                },

                displayEventTime: false,
                weekNumbers: true,
                slotDuration: {days: 1},
                scrollTime: '00:00',
                allDayDefault: true,
                lazyFetching: false, // FORCE DATABASE REFRESH

                resourceAreaWidth: '15%',
                resourceColumns: [
                    {
                        labelText: 'Panel View',
                        field: 'name'
                    }
                ],

                editable: true,
                removable: true,
                eventDurationEditable: false, // disable event resize
                eventStartEditable: false, // disable event drag drop
                collection: null,
                resourceCollection: null,
                clientCollection: null,
                fixedWeekCount: false, // http://fullcalendar.io/docs/display/fixedWeekCount/
                itemViewTemplateSelector: null,
                itemFormTemplateSelector: null,
                itemFormDeleteButtonSelector: null,
                defaultTimedEventDuration: moment.duration('00:30:00'),
                defaultAllDayEventDuration: moment.duration('24:00:00'),
                header: {
                    allDayDefault: true
                },
                firstDay: localeSettings.getCalendarFirstDayOfWeek() - 1,
                monthNames: localeSettings.getCalendarMonthNames('wide', true),
                monthNamesShort: localeSettings.getCalendarMonthNames('abbreviated', true),
                dayNames: localeSettings.getCalendarDayOfWeekNames('wide', true),
                dayNamesShort: localeSettings.getCalendarDayOfWeekNames('abbreviated', true)
            }
        },

        /**
         * this property is used to prevent loading of events from a server when the calendar object is created
         * @property {bool}
         */
        enableEventLoading: true,
        fullCalendar: null,
        eventView: null,
        loadingMask: null,
        offer: null,
        panel: null,
        panelView: null,
        supportType: null,
        lightingType: null,
        city: null,

        /**
         * This property can be used to prevent unnecessary reloading of calendar events.
         * key = calendarUid
         * @property
         */
        eventsLoaded: {},

        listen: {
            'layout:reposition mediator': 'onWindowResize'
        },

        /**
         * One of 'fullscreen' | 'scroll' | 'default'
         * @property
         */
        layout: undefined,

        initialize: function(options) {
            if (!options) {
                options = {};
            }
            if (options.eventsOptions) {
                _.defaults(options.eventsOptions, this.options.eventsOptions);
            }
            this.options = _.defaults(options || {}, this.options);

            // init event collection
            this.collection = this.collection || new EventCollection();

            // init resource collection
            this.resourceCollection = this.resourceCollection || new ResourceCollection();

            // init client collection
            this.clientCollection = this.clientCollection || new ClientCollection();
            this.clientCollection.setUrl();
            this.clientCollection.fetch();

            this.filters = [];

            // set options for new events
            this.options.newEventEditable = this.options.eventsOptions.editable;
            this.options.newEventRemovable = this.options.eventsOptions.removable;

            // subscribe to event collection events
            this.listenTo(this.collection, 'add', this.onEventAdded);
            this.listenTo(this.collection, 'change', this.onEventChanged);
            this.listenTo(this.collection, 'destroy', this.onEventDeleted);

            mediator.on('setSchedulerFilters', this.setSchedulerFilters, this);
            // mediator.on('setSchedulerCollection', this.setSchedulerCollection, this);
        },

        // setSchedulerCollection: function (response) {
        //     this.collection.reset(response);
        //
        //     this.updateEventsLoadedCache();
        //     this.updateEventsWithoutReload();
        // },

        setSchedulerFilters: function (activeFilters) {
            // activeFilters = _.filter(activeFilters, function(filter) {
            //     switch (filter.name) {
            //         case 'city':
            //         case 'supportType':
            //         case 'lightingType':
            //             return filter.value != 'All';
            //             break;
            //         default:dd
            //             return true;
            //     }
            // });

            this.filters = activeFilters;

            this.updateEventsLoadedCache();
            this.updateEventsWithoutReload();
        },

        onWindowResize: function() {
            this.setTimeline();
            this.updateLayout();
        },

        /**
         * @inheritDoc
         */
        dispose: function() {
            if (this.disposed) {
                return;
            }
            if (this.layout === 'fullscreen') {
                // fullscreen layout has side effects, need to clean up
                this.setLayout('default');
            }
            clearInterval(this.timelineUpdateIntervalId);
            if (this.getCalendarElement().data('fullCalendar')) {
                this.getCalendarElement().fullCalendar('destroy');
            }
            if (this.connectionsView) {
                this.connectionsView.dispose.call(this);
            }
            SchedulerView.__super__.dispose.call(this);
        },

        getEventView: function(eventModel) {
            if (!this.eventView) {
                var options =  {};
                // create a view for event details
                this.eventView = new EventView(_.extend({}, options, {
                    model: eventModel,
                    viewTemplateSelector: this.options.eventsOptions.itemViewTemplateSelector,
                    formTemplateSelector: this.options.eventsOptions.itemFormTemplateSelector,
                }));
                // subscribe to event view collection events
                this.listenTo(this.eventView, 'addEvent', this.handleEventViewAdd);
                this.listenTo(this.eventView, 'remove', this.handleEventViewRemove);
            }
            return this.eventView;
        },

        handleEventViewRemove: function() {
            this.eventView = null;
        },

        /**
         * Init and get a loading mask control
         *
         * @returns {Element}
         */
        getLoadingMask: function() {
            if (!this.loadingMask) {
                this.loadingMask = new LoadingMask({
                    container: this.getCalendarElement()
                });
            }
            return this.loadingMask;
        },

        getCollection: function() {
            return this.collection;
        },

        getCalendarElement: function() {
            if (!this.fullCalendar) {
                this.fullCalendar = this.$el.find(this.selectors.calendar);
            }
            return this.fullCalendar;
        },

        handleEventViewAdd: function(eventModel) {
            this.collection.add(eventModel);
        },

        visibleDefaultCalendar: function(eventModel) {

        },

        addEventToCalendar: function(eventModel) {
            var fcEvent = this.createViewModel(eventModel);
            this.getCalendarElement().fullCalendar('renderEvent', fcEvent);
            // this.getCalendarElement().fullCalendar('refetchEvents');
        },

        getCalendarEvents: function(calendarUid) {
            return this.getCalendarElement().fullCalendar('clientEvents', function(fcEvent) {
                return fcEvent.calendarUid === calendarUid;
            });
        },

        onEventAdded: function(eventModel) {
            if(eventModel.get('client')) {
                var client = this.clientCollection.get(eventModel.attributes.client);
                var resource = this.resourceCollection.get(eventModel.attributes.panelView);

                _.extend(eventModel.attributes, {
                    title: client.get('title'),
                    resourceId: resource.id,
                    resourceName: resource.get('name')
                });
            }

            // trigger before view update
            this.trigger('event:added', eventModel);

            this.addEventToCalendar(eventModel);
        },

        onEventChanged: function(eventModel) {
            var calendarElement = this.getCalendarElement();
            var fcEvent;

            // find and update fullCalendar event model
            fcEvent = calendarElement.fullCalendar('clientEvents', eventModel.id)[0];

            var client = this.clientCollection.get(eventModel.attributes.client);
            var resource = this.resourceCollection.get(eventModel.attributes.panelView);

            _.extend(eventModel.attributes, {
                title: client.get('title'),
                resourceId: resource.id,
                resourceName: resource.get('name')
            });

            _.extend(fcEvent, this.createViewModel(eventModel));

            // trigger before view update
            this.trigger('event:changed', eventModel);

            // notify fullCalendar about update
            // NOTE: cannot update single event due to fullcalendar bug
            //       please check that after updating fullcalendar
            //       calendarElement.fullCalendar('updateEvent', fcEvent);
            // calendarElement.fullCalendar('rerenderEvents');

            calendarElement.fullCalendar('updateEvent', fcEvent);
        },

        onEventDeleted: function(eventModel) {
            this.getCalendarElement().fullCalendar('removeEvents', eventModel.id);
            this.trigger('event:deleted', eventModel);
        },

        /**
         * @param attrs object with properties to set on model before dialog creation
         *              dates must be in utc
         */
        showAddEventDialog: function(attrs) {
            var eventModel;

            // need to be able to accept native moments here
            // convert arguments
            if (!attrs.start._fullCalendar) {
                attrs.start = $.fullCalendar.moment(attrs.start.clone().utc().format());
            }
            if (attrs.end && !attrs.end._fullCalendar) {
                attrs.end = $.fullCalendar.moment(attrs.end.clone().utc().format());
            }
            if (!this.eventView) {
                try {
                    attrs.start = attrs.start.clone().utc().format(this.MOMENT_BACKEND_FORMAT);
                    attrs.end = attrs.end.clone().utc().format(this.MOMENT_BACKEND_FORMAT);

                    _.extend(attrs, {
                        editable: this.options.newEventEditable,
                        removable: this.options.newEventRemovable
                    });
                    eventModel = new EventModel(attrs);
                    this.getEventView(eventModel).render();
                } catch (err) {
                    this.showMiscError(err);
                }
            }
        },

        onFcSelect: function(start, end, jsEvent, calendar, resource) {
            var attrs = {
                // allDay: start.time().as('ms') === 0 && end.time().as('ms') === 0,
                start: start.clone().tz(this.options.timezone, true),
                end: end.clone().tz(this.options.timezone, true),
                panelView: resource.id
            };
            this.showAddEventDialog(attrs);
        },

        onFcEventClick: function(fcEvent) {
            if (!this.eventView) {
                try {
                    var eventModel = this.collection.get(fcEvent.id);

                    this.getEventView(eventModel).render();
                } catch (err) {
                    this.showMiscError(err);
                }
            }
        },

        onFcEventResize: function(fcEvent, newDuration, undo) {
            this.saveFcEvent(fcEvent, undo);
        },

        onFcEventDragStart: function(fcEvent) {
            fcEvent._beforeDragState = {
                // allDay: fcEvent.allDay,
                start: fcEvent.start.clone(),
                end: fcEvent.end ? fcEvent.end.clone() : null
            };
        },

        onFcEventDrop: function(fcEvent, dateDiff, undo, jsEvent) {
            var realDuration;
            var currentView = this.getCalendarElement().fullCalendar('getView');
            var oldState = fcEvent._beforeDragState;
            var isDroppedOnDayGrid =
                fcEvent.start.time().as('ms') === 0 &&
                (fcEvent.end === null || fcEvent.end.time().as('ms') === 0);

            // when on week view all-day event is dropped at 12AM to hour view
            // previous condition gives false positive result
            if (fcEvent.end === null && isDroppedOnDayGrid === true && fcEvent.start.time().as('ms') === 0) {
                isDroppedOnDayGrid = !$(jsEvent.target).parents('.fc-time-grid-event').length;
            }

            // fcEvent.allDay = (currentView.name === 'month') ? oldState.allDay : isDroppedOnDayGrid;
            if (isDroppedOnDayGrid) {
                if (oldState.allDay) {
                    if (fcEvent.end === null && oldState.end === null) {
                        realDuration = this.options.eventsOptions.defaultAllDayEventDuration;
                    } else {
                        realDuration = oldState.end.diff(oldState.start);
                    }
                } else {
                    if (currentView.name === 'month') {
                        realDuration = oldState.end ? oldState.end.diff(oldState.start) : 0;
                    } else {
                        realDuration = this.options.eventsOptions.defaultAllDayEventDuration;
                    }
                }
            } else {
                if (oldState.allDay) {
                    realDuration = this.options.eventsOptions.defaultTimedEventDuration;
                } else {
                    realDuration = oldState.end ? oldState.end.diff(oldState.start) : 0;
                }
            }
            fcEvent.end = fcEvent.start.clone().add(realDuration);
            this.saveFcEvent(fcEvent);
        },

        saveFcEvent: function(fcEvent) {
            var promises = [];
            var eventModel;
            var attrs;
            attrs = {
                // allDay: fcEvent.allDay,
                start: fcEvent.start.clone().tz(this.options.timezone, true),
                end: (fcEvent.end !== null) ? fcEvent.end.clone().tz(this.options.timezone, true) : null
            };

            attrs.start = attrs.start.utc().format(this.MOMENT_BACKEND_FORMAT);
            if (attrs.end) {
                attrs.end = attrs.end.utc().format(this.MOMENT_BACKEND_FORMAT);
            }

            eventModel = this.collection.get(fcEvent.id);

            this.trigger('event:beforeSave', eventModel, promises, attrs);

            // wait for promises execution before save
            $.when.apply($, promises)
                .done(_.bind(this._saveFcEvent, this, eventModel, attrs));
        },

        _saveFcEvent: function(eventModel, attrs) {
            this.showSavingMask();
            try {
                eventModel.save(
                    attrs,
                    {
                        success: _.bind(this._hideMask, this),
                        error: _.bind(function(model, response) {
                            this.showSaveEventError(response.responseJSON || {});
                            this._hideMask();
                        }, this),
                        wait: true
                    }
                );
            } catch (err) {
                this.showSaveEventError(err);
            }
        },

        updateEvents: function() {
            try {
                this.showLoadingMask();
                // load events from a server
                this.collection.fetch({
                    reset: true,
                    success: _.bind(function() {
                        this.updateEventsLoadedCache();
                        this.updateEventsWithoutReload();
                    }, this),
                    error: _.bind(function(collection, response) {
                        this.showLoadEventsError(response.responseJSON || {});
                        this._hideMask();
                    }, this)
                });
            } catch (err) {
                this.showLoadEventsError(err);
            }
        },

        updateEventsLoadedCache: function() {
            this.eventsLoaded = {};
        },

        updateEventsWithoutReload: function() {
            var supportType = this.filters.filter( function (obj) {
                return obj.name == 'supportType';
            });
            var lightingType = this.filters.filter( function (obj) {
                return obj.name == 'lightingType';
            });
            var city = this.filters.filter( function (obj) {
                return obj.name == 'city';
            });

            if (supportType.length==1)
                this.supportType = supportType[0].value;
            else
                this.supportType = null;

            if (lightingType.length==1)
                this.lightingType = lightingType[0].value;
            else
                this.lightingType = null;

            if (city.length==1)
                this.city = city[0].value;
            else
                this.city = null;

            this.getCalendarElement().fullCalendar('refetchResources');

            var oldEnableEventLoading = this.enableEventLoading;
            this.enableEventLoading = true;
            this.getCalendarElement().fullCalendar('refetchEvents');
            this.enableEventLoading = oldEnableEventLoading;
        },

        loadEvents: function(start, end, timezone, callback) {
            var onEventsLoad = _.bind(function() {
                var fcEvents;

                if (this.enableEventLoading || _.size(this.eventsLoaded) === 0) {
                    this.updateEventsLoadedCache();
                }

                // prepare them for full calendar
                fcEvents = _.map(this.filterEvents(this.collection.models), function(eventModel) {
                    return this.createViewModel(eventModel);
                }, this);

                this._hideMask();
                callback(fcEvents);
            }, this);

            try {
                this.collection.setRange(
                    start.format(this.MOMENT_BACKEND_FORMAT),
                    end.format(this.MOMENT_BACKEND_FORMAT)
                );
                if (this.enableEventLoading) {
                    // load events from a server
                    this.collection.fetch({
                        reset: true,
                        success: onEventsLoad,
                        error: _.bind(function(collection, response) {
                            callback({});
                            this.showLoadEventsError(response.responseJSON || {});
                        }, this)
                    });
                } else {
                    // use already loaded events
                    onEventsLoad();
                }
            } catch (err) {
                callback({});
                this.showLoadEventsError(err);
            }
        },

        loadResources: function(callback) {
            var onResourcesLoad = _.bind(function() {
                var fcResources;

                // prepare them for full calendar
                fcResources = _.map(this.resourceCollection.models, function(resourceModel) {
                    return this.createResourceViewModel(resourceModel);
                }, this);

                this._hideMask();
                callback(fcResources);
            }, this);

            try {
                this.resourceCollection.setFilters(
                    this.offer,
                    this.panel,
                    this.panelView,
                    this.supportType,
                    this.lightingType,
                    this.city
                );

                // load events from a server
                this.resourceCollection.fetch({
                    reset: true,
                    success: onResourcesLoad,
                    error: _.bind(function(collection, response) {
                        callback({});
                        this.showLoadResourcesError(response.responseJSON || {});
                    }, this)
                });
            } catch (err) {
                callback({});
                this.showLoadResourcesError(err);
            }
        },

        /**
         * Performs filtration of calendar events before they are rendered
         *
         * @param {Array} events
         * @returns {Array}
         */
        filterEvents: function(events) {
            _.each(this.filters, function (filter) {
                events = _.filter(events, function(event) {
                    if (filter.name=='status' || filter.name=='client')
                        return event.get(filter.name)==filter.value;
                    else
                        return $.inArray(event.get(filter.name),filter.value);
                })
            });

            return events;
        },

        /**
         * Creates event entry for rendering in calendar plugin from the given event model
         *
         * @param {Object} eventModel
         */
        createViewModel: function(eventModel) {
            var fcEvent = _.pick(
                eventModel.attributes,
                ['id', 'title', 'start', 'end', 'resourceId', 'resourceName', 'panelView', 'client', 'offer','panel', 'supportType', 'lightingType', 'status', 'city','editable', 'removable']
            );

            fcEvent.backgroundColor = this.getBackgroundColor(fcEvent.status);
            fcEvent.textColor = colorUtil.getContrastColor(fcEvent.backgroundColor);

            if (fcEvent.start !== null && !moment.isMoment(fcEvent.start)) {
                fcEvent.start = $.fullCalendar.moment(fcEvent.start).tz(this.options.timezone);
            }
            if (fcEvent.end !== null && !moment.isMoment(fcEvent.end)) {
                fcEvent.end = $.fullCalendar.moment(fcEvent.end).tz(this.options.timezone);
            }

            if (fcEvent.end && fcEvent.end.diff(fcEvent.start) === 0) {
                fcEvent.end = null;
            }

            return fcEvent;
        },

        /**
         * Creates resource entry for rendering in calendar plugin from the given resource model
         *
         * @param {Object} resourceModel
         */
        createResourceViewModel: function(resourceModel) {
            var fcResource = _.pick(
                resourceModel.attributes,
                ['id', 'name']
            );

            return fcResource;
        },

        createClientViewModel: function(clientModel) {
            var fcClient = _.pick(
                clientModel.attributes,
                ['id', 'name']
            );

            return fcClient;
        },

        showSavingMask: function() {
            this.getLoadingMask().show(__('Saving...'));
        },

        showLoadingMask: function() {
            this.getLoadingMask().show(__('Loading...'));
        },

        _hideMask: function() {
            if (this.loadingMask) {
                this.loadingMask.hide();
            }
        },

        showLoadEventsError: function(err) {
            this._showError(__('Sorry, calendar events were not loaded correctly'), err);
        },

        showSaveEventError: function(err) {
            this._showError(__('Sorry, calendar event was not saved correctly'), err);
        },

        showMiscError: function(err) {
            this._showError(__('Sorry, unexpected error was occurred'), err);
        },

        showUpdateError: function(err) {
            this._showError(__('Sorry, the calendar updating was failed'), err);
        },

        _showError: function(message, err) {
            this._hideMask();
            messenger.showErrorMessage(message, err);
        },

        showLoadResourcesError: function(err) {
            this._showError(__('Sorry, calendar resources were not loaded correctly'), err);
        },

        showLoadClientsError: function(err) {
            this._showError(__('Sorry, calendar clients were not loaded correctly'), err);
        },

        initCalendarContainer: function() {
            // init events container
            var eventsContainer = this.$el.find(this.options.eventsOptions.containerSelector);
            if (eventsContainer.length === 0) {
                throw new Error('Cannot find container selector "' +
                    this.options.eventsOptions.containerSelector + '" element.');
            }
            eventsContainer.empty();
            eventsContainer.append($(this.eventsTemplate()));
        },

        _prepareFullCalendarOptions: function() {
            var options;
            var keys;
            var self;
            var scrollTime;
            // prepare options for jQuery FullCalendar control
            options = { // prepare options for jQuery FullCalendar control
                timezone: this.options.timezone,
                selectHelper: true,
                events: _.bind(this.loadEvents, this),
                resources: _.bind(this.loadResources, this),
                // select: _.bind(this.onFcSelect, this),
                // eventClick: _.bind(this.onFcEventClick, this),
                loading: _.bind(function(show) {
                    if (show) {
                        this.showLoadingMask();
                    } else {
                        this._hideMask();
                    }
                }, this)
            };
            keys = [
                'defaultDate', 'defaultView', 'editable', 'selectable',
                'header', 'allDayText', 'allDaySlot', 'buttonText',
                'titleFormat', 'columnFormat', 'timeFormat', 'axisFormat',
                'slotMinutes', 'snapMinutes', 'minTime', 'maxTime', 'scrollTime', 'slotEventOverlap',
                'firstDay', 'firstHour', 'monthNames', 'monthNamesShort', 'dayNames', 'dayNamesShort',
                'aspectRatio', 'defaultAllDayEventDuration', 'defaultTimeEventDuration',
                'fixedWeekCount', 'displayEventTime', 'weekNumbers', 'allDayDefault', 'slotDuration',
                'schedulerLicenseKey', 'resourceAreaWidth', 'resourceColumns', 'eventDurationEditable',
                'eventStartEditable', 'nextDayThreshold', 'lazyFetching'
            ];
            _.extend(options, _.pick(this.options.eventsOptions, keys));
            if (!_.isUndefined(options.defaultDate)) {
                options.defaultDate = moment(options.defaultDate);
            }

            if (!options.aspectRatio) {
                options.contentHeight = 'auto';
                options.height = 'auto';
            }

            if (this.options.scrollToCurrentTime) {
                scrollTime = moment.tz(this.options.timezone);
                if (scrollTime.minutes() < 10 && scrollTime.hours() !== 0) {
                    scrollTime.subtract(1, 'h');
                }
                options.scrollTime = scrollTime.startOf('hour').format('HH:mm:ss');
            }

            var dateFormat = localeSettings.getVendorDateTimeFormat('moment', 'date', 'MMM D, YYYY');
            var timeFormat = localeSettings.getVendorDateTimeFormat('moment', 'time', 'h:mm A');
            // prepare FullCalendar specific date/time formats
            var isDateFormatStartedWithDay = dateFormat[0] === 'D';
            var weekFormat = isDateFormatStartedWithDay ? 'D MMMM YYYY' : 'MMMM D YYYY';

            options.titleFormat = {
                month: 'MMMM YYYY',
                week: weekFormat,
                day: 'dddd, ' + dateFormat
            };
            options.columnFormat = {
                month: 'ddd',
                week: 'ddd ' + dateFormat,
                day: 'dddd ' + dateFormat
            };
            options.timeFormat = {
                default: timeFormat,
                agenda: timeFormat
            };
            options.axisFormat = timeFormat;

            self = this;
            options.eventAfterAllRender = function() {
                _.delay(_.bind(self.setTimeline, self));
                clearInterval(self.timelineUpdateIntervalId);
                self.timelineUpdateIntervalId = setInterval(function() { self.setTimeline(); }, 60 * 1000);
            };

            options.eventAfterRender = _.bind(function(fcEvent, $el) {
                var event = this.collection.get(fcEvent.id);
                eventDecorator.decorate(event, $el);
            }, this);

            return options;
        },

        initializeFullCalendar: function() {
            var fullCalendar;
            var calendarElement = this.getCalendarElement();
            var options = this._prepareFullCalendarOptions();

            // create jQuery FullCalendar control
            calendarElement.fullCalendar(options);
            fullCalendar = calendarElement.data('fullCalendar');
            // to avoid scroll blocking on mobile remove dragstart event listener that is added in calendar view
            if (_.isObject(fullCalendar) && tools.isMobile()) {
                $(document).off('dragstart', fullCalendar.getView().documentDragStartProxy);
            }
            this.updateLayout();
            this.enableEventLoading = true;
        },

        render: function() {
            // init views
            this.initCalendarContainer();
            // initialize jQuery FullCalendar control
            this.initializeFullCalendar();

            return this;
        },

        setTimeline: function() {
            var todayElement;
            var timeGrid;
            var timelineElement;
            var percentOfDay;
            var curSeconds;
            var timelineTop;
            var dayCol;
            var calendarElement = this.getCalendarElement();
            var currentView = calendarElement.fullCalendar('getView');
            // shown interval in calendar timezone
            var shownInterval = {
                start: currentView.intervalStart.clone().utc(),
                end: currentView.intervalEnd.clone().utc()
            };
            // current time in calendar timezone
            var now = moment.tz(this.options.timezone);

            if (currentView.name === 'month') {
                // nothing to do
                return;
            }

            // this function is called every 1 minute
            if (now.hours() === 0 && now.minutes() <= 2) {
                // the day has changed
                todayElement = calendarElement.find('.fc-today');
                todayElement.removeClass('fc-today');
                todayElement.removeClass('fc-state-highlight');
                todayElement.next().addClass('fc-today');
                todayElement.next().addClass('fc-state-highlight');
            }

            timeGrid = calendarElement.find('.fc-time-grid');
            timelineElement = timeGrid.children('.timeline-marker');
            if (timelineElement.length === 0) {
                // if timeline isn't there, add it
                timelineElement = $('<hr class="timeline-marker">');
                timeGrid.prepend(timelineElement);
            }

            if (shownInterval.start.isBefore(now) && shownInterval.end.isAfter(now)) {
                timelineElement.show();
            } else {
                timelineElement.hide();
            }

            curSeconds = (now.hours() * 3600) + (now.minutes() * 60) + now.seconds();
            percentOfDay = curSeconds / 86400; //24 * 60 * 60 = 86400, # of seconds in a day
            timelineTop = Math.floor(timeGrid.height() * percentOfDay);
            timelineElement.css('top', timelineTop + 'px');

            if (currentView.name === 'agendaWeek') {
                // week view, don't want the timeline to go the whole way across
                dayCol = calendarElement.find('.fc-today:visible');
                if (dayCol.length !== 0 && dayCol.position() !== null) {
                    timelineElement.css({
                        left: (dayCol.position().left) + 'px',
                        width: (dayCol.width() + 3) + 'px'
                    });
                }
            }
        },

        getAvailableHeight: function() {
            var $fcView = this.getCalendarElement().find('.fc-view:first');
            return mediator.execute('layout:getAvailableHeight', $fcView);
        },

        /**
         * Chooses layout on resize or during creation
         */
        updateLayout: function() {
            if (this.options.eventsOptions.aspectRatio) {
                this.setLayout('default');
                // do nothing
                return;
            }
            var $fcView = this.getCalendarElement().find('.fc-view:first');
            var $sidebar = $('.oro-page-sidebar');
            var preferredLayout = mediator.execute('layout:getPreferredLayout', $fcView);
            if (preferredLayout === 'fullscreen' &&
                $sidebar.height() > mediator.execute('layout:getAvailableHeight', $sidebar)) {
                preferredLayout = 'scroll';
            }
            this.setLayout(preferredLayout);

            // Pentru scroll la ziua curenta.
            $('.fc-scroller').animate({scrollLeft: $('.fc-scroller').scrollLeft() + $('.fc-past').width() * new Date().getDate() - 1 });
        },

        /**
         * Sets layout and perform all required operations
         */
        setLayout: function(newLayout) {
            if (newLayout === this.layout) {
                if (newLayout === 'fullscreen') {
                    this.getCalendarElement().fullCalendar('option', 'contentHeight', this.getAvailableHeight());
                }
                return;
            }
            this.layout = newLayout;
            var $calendarEl = this.getCalendarElement();
            var contentHeight = '';
            var height = '';
            switch (newLayout) {
                case 'fullscreen':
                    mediator.execute('layout:disablePageScroll', $calendarEl);
                    contentHeight = this.getAvailableHeight();
                    break;
                case 'scroll':
                    height = 'auto';
                    contentHeight = 'auto';
                    mediator.execute('layout:enablePageScroll');
                    break;
                case 'default':
                    mediator.execute('layout:enablePageScroll');
                    // default values
                    break;
                default:
                    throw new Error('Unknown calendar layout');
            }
            $calendarEl.fullCalendar('option', 'height', height);
            $calendarEl.fullCalendar('option', 'contentHeight', contentHeight);
        },

        getBackgroundColor: function(status) {
            switch (parseInt(status))
            {
                case 1:
                    return '#84e184';
                    break;
                default:
                    return '#ffff99';
            }
        }
    });

    return SchedulerView;
});