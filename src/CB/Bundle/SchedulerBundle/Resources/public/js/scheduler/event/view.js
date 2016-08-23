define([
    'underscore',
    'backbone',
    'orotranslation/js/translator',
    'routing',
    'oro/dialog-widget',
    'oroui/js/app/views/loading-mask-view',
    'cbscheduler/js/form-validation',
    'oroui/js/delete-confirmation',
    'oroform/js/formatter/field',
    'oroactivity/js/app/components/activity-context-activity-component'
], function(
    _,
    Backbone,
    __,
    routing,
    DialogWidget,
    LoadingMask,
    FormValidation,
    DeleteConfirmation,
    fieldFormatter,
    ActivityContextComponent
) {
    'use strict';

    var $ = Backbone.$;

    /**
     * @export  cbscheduler/js/scheduler/event/view
     * @class   cbscheduler.scheduler.event.View
     * @extends Backbone.View
     */
    return Backbone.View.extend({
        /** @property {Object} */
        options: {
            scheduler: null,
            connections: null,
            colorManager: null,
            widgetRoute: null,
            widgetOptions: null
        },

        /** @property {Object} */
        selectors: {
            loadingMaskContent: '.loading-content',
            backgroundColor: 'input[name$="[backgroundColor]"]',
            schedulerUid: '[name*="schedulerUid"]',
            invitedUsers: 'input[name$="[invitedUsers]"]',
            contexts: 'input[name$="[contexts]"]'
        },

        /** @property {Array} */
        userSchedulerOnlyFields: [
            {fieldName: 'reminders', emptyValue: {}, selector: '.reminders-collection'},
            {fieldName: 'invitedUsers', emptyValue: '', selector: 'input[name$="[invitedUsers]"]'}
        ],

        initialize: function(options) {
            this.options = _.defaults(_.pick(options || {}, _.keys(this.options)), this.options);
            this.viewTemplate = _.template($(options.viewTemplateSelector).html());
            this.template = _.template($(options.formTemplateSelector).html());

            this.listenTo(this.model, 'sync', this.onModelSave);
            this.listenTo(this.model, 'destroy', this.onModelDelete);
        },

        remove: function() {
            this.trigger('remove');
            this._hideMask();
            if (this.activityContext) {
                this.activityContext.dispose();
                delete this.activityContext;
            }
            Backbone.View.prototype.remove.apply(this, arguments);
        },

        onModelSave: function() {
            this.trigger('addEvent', this.model);
            this.eventDialog.remove();
            this.remove();
        },

        onModelDelete: function() {
            this.eventDialog.remove();
            this.remove();
        },

        render: function() {
            var widgetOptions = this.options.widgetOptions || {};
            var defaultOptions = {
                    title: this.model.isNew() ? __('Add New Event') : __('View Event'),
                    stateEnabled: false,
                    incrementalPosition: false,
                    dialogOptions: _.defaults(widgetOptions.dialogOptions || {}, {
                        modal: true,
                        resizable: false,
                        width: 475,
                        autoResize: true,
                        close: _.bind(this.remove, this)
                    }),
                    submitHandler: _.bind(this.saveModel, this)
                };
            var onDelete = _.bind(function(e) {
                    var $el = $(e.currentTarget);
                    var deleteUrl = $el.data('url');
                    var confirm = new DeleteConfirmation({
                        content: $el.data('message')
                    });
                    e.preventDefault();
                    confirm.on('ok', _.bind(function() {
                        this.deleteModel(deleteUrl);
                    }, this));
                    confirm.open();
                }, this);
            var onEdit = _.bind(function(e) {
                    this.eventDialog.setTitle(__('Edit Event'));
                    this.eventDialog.setContent(this.getEventForm());
                    // subscribe to 'delete event' event
                    this.eventDialog.getAction('delete', 'adopted', function(deleteAction) {
                        deleteAction.on('click', onDelete);
                    });
                }, this);

            if (this.options.widgetRoute) {
                defaultOptions.el = $('<div></div>');
                defaultOptions.url = routing.generate(this.options.widgetRoute, {id: this.model.originalId});
                defaultOptions.type = 'Scheduler';
            } else {
                defaultOptions.el = this.model.isNew() ? this.getEventForm() : this.getEventView();
                defaultOptions.loadingMaskEnabled = false;
            }

            this.eventDialog = new DialogWidget(_.defaults(
                _.omit(widgetOptions, ['dialogOptions']),
                defaultOptions
            ));
            this.eventDialog.render();

            // subscribe to 'delete event' event
            this.eventDialog.getAction('delete', 'adopted', function(deleteAction) {
                deleteAction.on('click', onDelete);
            });
            // subscribe to 'switch to edit' event
            this.eventDialog.getAction('edit', 'adopted', function(editAction) {
                editAction.on('click', onEdit);
            });

            // init loading mask control
            this.loadingMask = new LoadingMask({
                container: this.eventDialog.$el.closest('.ui-dialog')
            });

            return this;
        },

        saveModel: function() {
            var errors;
            this.model.set(this.getEventFormData());
            if (this.model.isValid()) {
                this.showSavingMask();
                try {
                    this.model.save(null, {
                        wait: true,
                        error: _.bind(this._handleResponseError, this)
                    });
                } catch (err) {
                    this.showError(err);
                }
            } else {
                errors = _.map(this.model.validationError, function(message) {
                    return __(message);
                });
                this.showError({errors: errors});
            }
        },

        deleteModel: function(deleteUrl) {
            this.showDeletingMask();
            try {
                var options = {
                    wait: true,
                    error: _.bind(this._handleResponseError, this)
                };
                if (deleteUrl) {
                    options.url = deleteUrl;
                }
                this.model.destroy(options);
            } catch (err) {
                this.showError(err);
            }
        },

        showSavingMask: function() {
            this._showMask(__('Saving...'));
        },

        showDeletingMask: function() {
            this._showMask(__('Deleting...'));
        },

        showLoadingMask: function() {
            this._showMask(__('Loading...'));
        },

        _showMask: function(message) {
            if (this.loadingMask) {
                this.loadingMask.show(message);
            }
        },

        _hideMask: function() {
            if (this.loadingMask) {
                this.loadingMask.hide();
            }
        },

        _handleResponseError: function(model, response) {
            this.showError(response.responseJSON || {});
        },

        showError: function(err) {
            this._hideMask();
            if (this.eventDialog) {
                FormValidation.handleErrors(this.eventDialog.$el.parent(), err);
            }
        },

        fillForm: function(form, modelData) {
            var self = this;
            form = $(form);

            self.buildForm(form, modelData);

            var inputs = form.find('[name]');
            var fieldNameRegex = /\[(\w+)\]/g;

            // show loading mask if child events users should be updated
            if (!_.isEmpty(modelData.invitedUsers)) {
                this.eventDialog.once('renderComplete', function() {
                    self.showLoadingMask();
                });
            }

            _.each(inputs, function(input) {
                input = $(input);
                var name = input.attr('name');
                var matches = [];
                var match;

                while ((match = fieldNameRegex.exec(name)) !== null) {
                    matches.push(match[1]);
                }

                if (matches.length) {
                    var value = self.getValueByPath(modelData, matches);
                    if (input.is(':checkbox')) {
                        if (value === false || value === true) {
                            input.prop('checked', value);
                        } else {
                            input.prop('checked', input.val() === value);
                        }
                    } else {
                        input.val(value);
                    }
                    input.change();
                }

                // hide loading mask if child events users should be updated
                if (name.indexOf('[invitedUsers]') !== -1 && !_.isEmpty(modelData.invitedUsers)) {
                    input.on('select2-data-loaded', function() {
                        self._hideMask();
                    });
                }
            });

            return form;
        },

        buildForm: function(form, modelData) {
            var self = this;
            form = $(form);
            _.each(modelData, function(value, key) {
                if (typeof value === 'object') {
                    var container = form.find('.' + key + '-collection');
                    if (container) {
                        var prototype = container.data('prototype');
                        if (prototype) {
                            _.each(value, function(collectionValue, collectionKey) {
                                container.append(prototype.replace(/__name__/g, collectionKey));
                            });
                        }
                    }

                    self.buildForm(form, value);
                }
            });
        },

        getEventView: function() {
            // fetch scheduler related connection
            var connection = this.options.connections.findWhere({schedulerUid: this.model.get('schedulerUid')});
            var $element = $(this.viewTemplate(_.extend(this.model.toJSON(), {
                formatter: fieldFormatter,
                connection: connection ? connection.toJSON() : null
            })));

            var $contextsSource = $element.find('.activity-context-activity');
            this.activityContext = new ActivityContextComponent({
                _sourceElement: $contextsSource,
                checkTarget: false,
                activityClassAlias: 'schedulerevents',
                entityId: this.model.originalId,
                editable: this.model.get('editable')
            });

            return $element;
        },

        getEventForm: function() {
            var modelData = this.model.toJSON();
            var templateData = _.extend(this.getEventFormTemplateData(!modelData.id), modelData);
            var form = this.fillForm(this.template(templateData), modelData);
            var schedulerColors = this.options.colorManager.getSchedulerColors(this.model.get('schedulerUid'));

            form.find(this.selectors.backgroundColor)
                .data('page-component-options').emptyColor = schedulerColors.backgroundColor;
            if (modelData.schedulerAlias !== 'user') {
                this._showUserSchedulerOnlyFields(form, false);
            }
            this._toggleSchedulerUidByInvitedUsers(form);

            form.find(this.selectors.schedulerUid).on('change', _.bind(function(e) {
                var $emptyColor = form.find('.empty-color');
                var $selector = $(e.currentTarget);
                var tagName = $selector.prop('tagName').toUpperCase();
                var schedulerUid = tagName === 'SELECT' || $selector.is(':checked') ?
                    $selector.val() : this.model.get('schedulerUid');
                var colors = this.options.colorManager.getSchedulerColors(schedulerUid);
                var newScheduler = this.parseSchedulerUid(schedulerUid);
                $emptyColor.css({'background-color': colors.backgroundColor, 'color': colors.color});
                if (newScheduler.schedulerAlias === 'user') {
                    this._showUserSchedulerOnlyFields(form);
                } else {
                    this._showUserSchedulerOnlyFields(form, false);
                }
            }, this));
            form.find(this.selectors.invitedUsers).on('change', _.bind(function(e) {
                this._toggleSchedulerUidByInvitedUsers(form);
            }, this));

            // Adds scheduler event activity contexts items to the form
            if (this.model.originalId) {
                var contexts = form.find(this.selectors.contexts);
                $.ajax({
                    url: routing.generate('oro_api_get_activity_context', {
                        activity: 'schedulerevents', id: this.model.originalId
                    }),
                    type: 'GET',
                    success: function(targets) {
                        var targetsStrArray = [];
                        targets.forEach(function(target) {
                            var targetData = {
                                entityClass: target.targetClassName.split('_').join('\\'),
                                entityId: target.targetId
                            };
                            targetsStrArray.push(JSON.stringify(targetData));
                        });
                        contexts.val(targetsStrArray.join(';'));
                        contexts.trigger('change');
                    }
                });
            }

            return form;
        },

        getEventFormData: function() {
            var fieldNameFilterRegex = /^oro_scheduler_event_form/;
            var fieldNameRegex = /\[(\w+)\]/g;
            var data = {};
            var formData = this.eventDialog.form.serializeArray().filter(function(item) {
                return fieldNameFilterRegex.test(item.name);
            });
            formData = formData.concat(this.eventDialog.form.find('input[type=checkbox]:not(:checked)')
                .map(function() {
                    return {name: this.name, value: false};
                }).get());
            _.each(formData, function(dataItem) {
                var matches = [];
                var match;
                while ((match = fieldNameRegex.exec(dataItem.name)) !== null) {
                    matches.push(match[1]);
                }

                if (matches.length) {
                    this.setValueByPath(data, dataItem.value, matches);
                }
            }, this);

            if (data.hasOwnProperty('schedulerUid')) {
                if (data.schedulerUid) {
                    _.extend(data, this.parseSchedulerUid(data.schedulerUid));
                    if (data.schedulerAlias !== 'user') {
                        _.each(this.userSchedulerOnlyFields, function(item) {
                            if (item.fieldName) {
                                data[item.fieldName] = item.emptyValue;
                            }
                        });
                    }
                }
                delete data.schedulerUid;
            }

            if (data.hasOwnProperty('invitedUsers')) {
                data.invitedUsers = _.map(data.invitedUsers ? data.invitedUsers.split(',') : [], function(item) {
                    return parseInt(item);
                });
            }

            if (!data.hasOwnProperty('reminders')) {
                data.reminders = {};
            }

            return data;
        },

        parseSchedulerUid: function(schedulerUid) {
            return {
                schedulerAlias: schedulerUid.substr(0, schedulerUid.lastIndexOf('_')),
                scheduler: parseInt(schedulerUid.substr(schedulerUid.lastIndexOf('_') + 1))
            };
        },

        _showUserSchedulerOnlyFields: function(form, visible) {
            _.each(this.userSchedulerOnlyFields, function(item) {
                if (item.selector) {
                    if (_.isUndefined(visible) || visible) {
                        form.find(item.selector).closest('.control-group').show();
                    } else {
                        form.find(item.selector).closest('.control-group').hide();
                    }
                }
            });
        },

        _toggleSchedulerUidByInvitedUsers: function(form) {
            var $schedulerUid = form.find(this.selectors.schedulerUid);
            if (!$schedulerUid.length) {
                return;
            }
            if (form.find(this.selectors.invitedUsers).val()) {
                $schedulerUid.attr('disabled', 'disabled');
                $schedulerUid.parent().attr('title', __('The scheduler cannot be changed because the event has guests'));
                // fix select2 dynamic change disabled
                if (!$schedulerUid.parent().hasClass('disabled')) {
                    $schedulerUid.parent().addClass('disabled');
                }
                if ($schedulerUid.prop('tagName').toUpperCase() !== 'SELECT') {
                    $schedulerUid.parent().find('label').addClass('disabled');
                }
            } else {
                $schedulerUid.removeAttr('disabled');
                $schedulerUid.removeAttr('title');
                // fix select2 dynamic change disabled
                if ($schedulerUid.parent().hasClass('disabled')) {
                    $schedulerUid.parent().removeClass('disabled');
                }
                if ($schedulerUid.prop('tagName').toUpperCase() !== 'SELECT') {
                    $schedulerUid.parent().find('label').removeClass('disabled');
                }
            }
        },

        setValueByPath: function(obj, value, path) {
            var parent = obj;
            var i;

            for (i = 0; i < path.length - 1; i++) {
                if (parent[path[i]] === undefined) {
                    parent[path[i]] = {};
                }
                parent = parent[path[i]];
            }

            parent[path[path.length - 1]] = value;
        },

        getValueByPath: function(obj, path) {
            var current = obj;
            var i;

            for (i = 0; i < path.length; i++) {
                if (current[path[i]] === undefined || current[path[i]] === null) {
                    return undefined;
                }
                current = current[path[i]];
            }

            return current;
        },

        getEventFormTemplateData: function(isNew) {
            var templateType = '';
            var schedulers = [];
            var ownScheduler = null;
            var isOwnScheduler = function(item) {
                return (item.get('schedulerAlias') === 'user' && item.get('scheduler') === item.get('targetScheduler'));
            };

            this.options.connections.each(function(item) {
                var scheduler;
                if (item.get('canAddEvent')) {
                    scheduler = {uid: item.get('schedulerUid'), name: item.get('schedulerName')};
                    if (!ownScheduler && isOwnScheduler(item)) {
                        ownScheduler = scheduler;
                    } else {
                        schedulers.push(scheduler);
                    }
                }
            }, this);

            if (schedulers.length) {
                if (isNew && schedulers.length === 1) {
                    templateType = 'single';
                } else {
                    if (ownScheduler) {
                        schedulers.unshift(ownScheduler);
                    }
                    templateType = 'multiple';
                }
            }

            return {
                schedulerUidTemplateType: templateType,
                schedulers: schedulers
            };
        }
    });
});
