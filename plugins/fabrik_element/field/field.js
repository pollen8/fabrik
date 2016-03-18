/**
 * Field Element
 *
 * @copyright: Copyright (C) 2005-2013, fabrikar.com - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// Define variable outside of require js so that the form class can initialize it

// Wrap in require js to ensure we always load the same version of jQuery
// Multiple instances can be loaded an ajax pages are added and removed. However we always want
// to get the same version as plugins are only assigned to this jQuery instance
define(['jquery', 'fab/element'], function (jQuery, FbElement) {
    function geolocateLoad() {
        if (document.body) {
            window.fireEvent('google.geolocate.loaded');
        } else {
            console.log('no body');
        }
    }

    window.FbField = new Class({
        Extends: FbElement,

        options: {
            use_input_mask        : false,
            input_mask_definitions: ''
        },

        initialize: function (element, options) {
            var definitions;
            this.setPlugin('fabrikfield');
            this.parent(element, options);
            /*
             * $$$ hugh - testing new masking feature, uses this jQuery widget:
             * http://digitalbush.com/projects/masked-input-plugin/
             */
            if (this.options.use_input_mask) {
                if (this.options.input_mask_definitions !== '') {
                    definitions = JSON.parse(this.options.input_mask_definitions);
                    jQuery.extend(jQuery.mask.definitions, definitions);
                }
                jQuery('#' + element).mask(this.options.input_mask);
            }
            if (this.options.geocomplete) {
                this.gcMade = false;
                this.loadFn = function () {
                    if (this.gcMade === false) {
                        jQuery('#' + this.element.id).geocomplete();
                        this.gcMade = true;
                    }
                }.bind(this);
                window.addEvent('google.geolocate.loaded', this.loadFn);
                Fabrik.loadGoogleMap(false, 'geolocateLoad');
            }
        },

        select: function () {
            var element = this.getElement();
            if (element) {
                this.getElement().select();
            }
        },

        focus: function () {
            var element = this.getElement();
            if (element) {
                this.getElement().focus();
            }
        },

        cloned: function (c) {
            var element = this.getElement();
            if (this.options.use_input_mask) {
                if (element) {
                    if (this.options.input_mask_definitions !== '') {
                        var definitions = JSON.parse(this.options.input_mask_definitions);
                        $H(definitions).each(function (v, k) {
                            jQuery.mask.definitions[k] = v;
                        });
                    }
                    jQuery('#' + element.id).mask(this.options.input_mask);
                }
            }
            if (this.options.geocomplete) {
                if (element) {
                    jQuery('#' + element.id).geocomplete();
                }
            }
            this.parent(c);
        }

    });

    return window.FbField;
});