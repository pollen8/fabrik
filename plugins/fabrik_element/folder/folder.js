/**
 * Folder Element
 *
 * @copyright: Copyright (C) 2005-2015, fabrikar.com - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

define(['jquery', 'fab/element'], function (jQuery, FbElement) {
    window.FbFolder = new Class({
        Extends   : FbElement,
        initialize: function (element, options) {
            this.setPlugin('fabrikfolder');
            this.parent(element, options);
        }
    });

    return window.FbFolder;
});