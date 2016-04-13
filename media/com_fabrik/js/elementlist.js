/**
 * Element List
 *
 * @copyright: Copyright (C) 2005-2013, fabrikar.com - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

/*jshint mootools: true */
/*global Fabrik:true, fconsole:true, Joomla:true, CloneObject:true, $H:true,unescape:true,Asset:true */

define(['jquery', 'fab/element'], function (jQuery, FbElement) {
    window.FbElementList = new Class({

        Extends: FbElement,

        type: 'text', // Sub element type

        initialize: function (element, options) {
            this.parent(element, options);
            this.addSubClickEvents();
            this._getSubElements();
            if (this.options.allowadd === true && this.options.editable !== false) {
                this.watchAddToggle();
                this.watchAdd();
            }
        },

        // Get the sub element which are the checkboxes themselves

        _getSubElements: function () {
            var element = this.getElement();
            if (!element) {
                this.subElements = [];
            } else {
                this.subElements = element.getElements('input[type=' + this.type + ']');
            }
            return this.subElements;
        },

        addSubClickEvents: function () {
            this._getSubElements().each(function (el) {
                el.addEvent('click', function (e) {
                    Fabrik.fireEvent('fabrik.element.click', [this, e]);
                });
            });
        },

        /**
         * Get the dom selector that events should be attached to
         * @returns {string}
         */
        eventDelegate: function () {
            return 'input[type=' + this.type + '][name^=' + this.options.fullName + ']';
        },

        /**
         * Convert event actions on a per element basis.
         * @param {string} action
         * @returns {string}
         */
        checkEventAction: function (action) {
            return action;
        },

        /**
         * Add an event
         * @param {string} action
         * @param {string|function} js
         */
        addNewEvent: function (action, js) {
            var r, delegate, uid, c;
            action = this.checkEventAction(action);
            if (action === 'load') {
                this.loadEvents.push(js);
                this.runLoadEvent(js);
            } else {
                c = this.form.form;

                // Added name^= for http://fabrikar.com/forums/showthread.php?t=30563
                // (js events to show hide multiple groups)
                delegate = this.eventDelegate();
                if (typeOf(this.form.events[action]) === 'null') {
                    this.form.events[action] = {};
                }

                // Could be added via a custom js file.
                if (typeof(js) === 'function') {
                    uid = Math.random(100) * 1000;
                } else {
                    r = new RegExp('[^a-z|0-9]', 'gi');
                    uid = delegate + js.replace(r, '');
                }
                if (typeOf(this.form.events[action][uid]) === 'null') {
                    this.form.events[action][uid] = true;

                    jQuery(c).on(action, delegate, function (event) {
                        // Changed from preventDefault() to stopPropagation() as the
                        // former prevents radios from selecting
                        event.stopPropagation();
                        // Don't use the usual jQuery this, as we need to bind the plugin as 'this' to the event.
                        var target = jQuery(event.currentTarget), elid, that, subEls;
                        if (target.prop('tagName') === 'LABEL') {
                            target = target.find('input');
                        }
                        // As we are delegating the event, and reference to 'this'
                        // in the js will refer to the first element
                        // When in a repeat group we want to replace that with a reference to the current element.
                        elid = target.closest('.fabrikSubElementContainer').prop('id');
                        that = this.form.formElements[elid];
                        subEls = that._getSubElements();
                        if (target.length > 0 && subEls.contains(target[0])) {

                            // Replace this with that so that the js code runs on the correct element
                            if (typeof(js) !== 'function') {
                                js = js.replace(/this/g, 'that');
                                eval(js);
                            } else {
                                js.delay(0);
                            }
                        }
                    }.bind(this));
                }
            }
        },

        checkEnter: function (e) {
            if (e.key === 'enter') {
                e.stop();
                this.startAddNewOption();
            }
        },

        startAddNewOption: function () {
            debugger;
            var c = jQuery(this.getContainer()), val,
                l = c.find('input[name=addPicklistLabel]'),
                v = c.find('input[name=addPicklistValue]'),
                label = l.val(), r, i, last;
            if (v.length > 0) {
                val = v.val();
            } else {
                val = label;
            }
            if (val === '' || label === '') {
                window.alert(Joomla.JText._('PLG_ELEMENT_CHECKBOX_ENTER_VALUE_LABEL'));
            }
            else {
                last = jQuery(this.subElements[this.subElements.length - 1])
                    .closest('fabrikgrid_' + this.type);
                r = jQuery(last).clone();
                i = r.find('input');
                i.val(val);
                i.prop('checked', 'checked');
                if (this.type === 'checkbox') {

                    // Remove the last [*] from the checkbox sub option name
                    // (seems only these use incremental []'s)
                    var name = i.prop('name').replace(/^(.*)\[.*\](.*?)$/, '$1$2');
                    i.name = name + '[' + (this.subElements.length) + ']';
                }
                r.find('.' + this.type + ' span').text(label);
                r.after(last);

                var index = 0;
                if (this.type === 'radio') {
                    index = this.subElements.length;
                }
                var is = jQuery('input[name=' + i.name + ']');
                jQuery(this.form.form).trigger('change', {target: is[index]});

                this._getSubElements();
                if (v) {
                    v.value = '';
                }
                l.value = '';
                this.addNewOption(val, label);
                if (this.mySlider) {
                    this.mySlider.toggle();
                }
            }
        },

        watchAdd: function () {
            var self = this,
                c = jQuery(this.getContainer());
            if (this.options.allowadd === true && this.options.editable !== false) {
                c.find('input[name=addPicklistLabel], input[name=addPicklistValue]')
                    .on('keypress', function (e) {
                    self.checkEnter(e);
                });
                c.find('input[type=button]').on('click', function (e) {
                    e.preventDefault();
                    self.startAddNewOption();
                });
                jQuery(document).on('keypress', function (e) {
                    if (e.key === 'esc' && this.mySlider) {
                        self.mySlider.slideUp();
                    }
                });
            }
        }

    });

    return window.FbElementList;
});
