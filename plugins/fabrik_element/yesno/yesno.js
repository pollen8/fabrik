/**
 * Yes/No Element
 *
 * @copyright: Copyright (C) 2005-2013, fabrikar.com - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

FbYesno = new Class({
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
        str += ', div.fb_el_school___private label';

        return str;
    },

    getChangeEvent: function () {
        return this.options.changeEvent;
    }

});
