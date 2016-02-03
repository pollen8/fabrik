/**
 * Yes/No Element
 *
 * @copyright: Copyright (C) 2005-2013, fabrikar.com - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

var FbYesno = new Class({
    Extends   : FbRadio,
    initialize: function (element, options) {
        this.setPlugin('fabrikyesno');
        this.parent(element, options);
    },

    /**
     * Get the dom selector that events should be attached to. Attach to labels as well
     * @returns {string}
     */
    eventDelegate: function () {
        var str = 'input[type=' + this.type + '][name^=' + this.options.fullName + ']';
        str += ', [class*=fb_el_' + this.options.fullName + '] .fabrikElement label';

        return str;
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
