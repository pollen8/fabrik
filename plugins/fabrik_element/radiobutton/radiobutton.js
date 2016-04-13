/**
 * Radio Button Element
 *
 * @copyright: Copyright (C) 2005-2015, fabrikar.com - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

define(['jquery', 'fab/elementlist'], function (jQuery, FbElementList) {
    window.FbRadio = new Class({
        Extends: FbElementList,

        mySlider: false,

        options: {
            btnGroup: true
        },

        type: 'radio', // sub element type

        initialize: function (element, options) {
            this.setPlugin('fabrikradiobutton');
            this.parent(element, options);
            this.btnGroup();
        },

        btnGroup: function () {
            // Seems slightly screwy in admin as the j template does the same code
            if (!this.options.btnGroup) {
                return;
            }
            // Turn radios into btn-group
            this.btnGroupRelay();
            var c = jQuery(this.getContainer());
            c.find('.radio.btn-group label').addClass('btn');
            c.find('.btn-group input[checked=checked]').each(function () {
                var input = jQuery(this),
                    label = input.closest('label'), v;
                if (label.length === 0) {
                    // J3.2 button group markup - label is after input (no longer the case)
                    label = input.next();
                }
                v = input.val();
                if (v === '') {
                    label.addClass('active btn-primary');
                } else if (v === '0') {
                    label.addClass('active btn-danger');
                } else {
                    label.addClass('active btn-success');
                }
            });
        },

        btnGroupRelay: function () {
            var c = jQuery(this.getContainer()), self = this;
            c.find('.radio.btn-group label').addClass('btn');
            c.on('click', '.btn-group label', function () {
                var label = jQuery(this),
                    input = [],
                    id = label.prop('for');
                if (id !== '') {
                    input = jQuery('#' + id);
                }
                if (input.length === 0) {
                    input = label.find('input');
                }
                self.setButtonGroupCSS(input);
            });
        },

	    /**
         *
         * @param {jQuery} input
         */
        setButtonGroupCSS: function (input) {
            var label = [];
            if (input.prop('id') !== '') {
                label = jQuery('label[for=' + input.id + ']');
            }
            if (label.length === 0) {
                label = input.closest('label.btn');
            }
            var v = input.val();
            var fabChecked = parseInt(input.prop('fabchecked'), 10);

            // Protostar in J3.2 adds its own btn-group js code -
            // need to thus apply this section even after input has been unchecked
            if (!input.prop('checked') || fabChecked === 1) {
                if (label) {
                    label.closest('.btn-group').find('label').removeClass('active').removeClass('btn-success')
                        .removeClass('btn-danger').removeClass('btn-primary');
                    if (v === '') {
                        label.addClass('active btn-primary');
                    } else if (parseInt(v, 10) === 0) {
                        label.addClass('active btn-danger');
                    } else {
                        label.addClass('active btn-success');
                    }
                }
                input.prop('checked', true);
                input.trigger('change');
                input.trigger('click');

                if (fabChecked === null) {
                    input.prop('fabchecked', 1);
                }
            }
        },

        watchAddToggle: function () {
            var c = jQuery(this.getContainer()),
                d = c.find('div.addoption'),
                a = c.find('.toggle-addoption');
            if (this.mySlider) {
                // Copied in repeating group so need to remove old slider html first
                var clone = d.clone();
                var fe = c.find('.fabrikElement');
                d.parent().remove();
                fe.append(clone);
                d = c.find('div.addoption');
                d.css('margin', 0);
            }
            d.slideToggle();
            this.mySlider = d;

            a.on('click', function (e) {
                e.preventDefault();
                d.slideToggle();
            });
        },

        getValue: function () {
            if (!this.options.editable) {
                return this.options.value;
            }
            var v = '';
            this._getSubElements().each(function (sub) {
                if (sub.checked) {
                    v = sub.get('value');
                    return v;
                }
                return null;
            });
            return v;
        },

	    /**
         * Set Value
         * @param {string} v
         */
        setValue: function (v) {
            if (!this.options.editable) {
                return;
            }
            this._getSubElements().each(function (sub) {
                if (sub.value === v) {
                    sub.checked = 'checked';
                }
            });
        },

        update: function (val) {
            var self = this;
            if (!this.options.editable) {
                if (val === '') {
                    this.element.innerHTML = '';
                    return;
                }
                this.element.innerHTML = this.options.data[val];
                return;
            } else {
                var els = this._getSubElements();
                if (typeOf(val) === 'array') {
                    els.each(function (el) {
                        if (val.contains(el.value)) {
                            self.setButtonGroupCSS(el);
                        }
                    });
                } else {
                    els.each(function (el) {
                        if (el.value === val) {
                            self.setButtonGroupCSS(el);
                        }
                    });
                }
            }
        },

        cloned: function (c) {
            if (this.options.allowadd === true && this.options.editable !== false) {
                this.watchAddToggle();
                this.watchAdd();
            }
            this.parent(c);
            this.btnGroup();
        },

        getChangeEvent: function () {
            return this.options.changeEvent;
        }

    });

    return window.FbRadio;
});
