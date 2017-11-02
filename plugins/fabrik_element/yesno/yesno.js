/**
 * Yes/No Element
 *
 * @copyright: Copyright (C) 2005-2013, fabrikar.com - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

define(['jquery', 'element/radiobutton/radiobutton'], function (jQuery, FbRadio) {
    window.FbYesno = new Class({
        Extends   : FbRadio,
        initialize: function (element, options) {
            this.setPlugin('fabrikyesno');
            this.parent(element, options);
        },

        /**
         * Convert event actions on a per element basis.
         * @param {string} action
         * @returns {string}
         */
        checkEventAction: function (action) {
            // Change events wont fire on labels.
            if (action === 'change') {
                action = 'click';
            }

            return action;
        },

        getChangeEvent: function () {
            return this.options.changeEvent;
        }

    });

    return window.FbYesno;
});