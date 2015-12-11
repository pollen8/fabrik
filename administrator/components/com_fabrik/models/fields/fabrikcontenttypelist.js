/**
 * Content Type Ajax Preview
 *
 * @copyright: Copyright (C) 2005-2015, fabrikar.com - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

'use strict';

var FabrikContentTypeList = new Class({

    options: {},

    initialize: function (id) {
        var showUpdate = this.showUpdate;
        showUpdate(jQuery('#' + id).val());
        jQuery('#' + id).on('change', function () {
            showUpdate(jQuery(this).val());
        });
    },

    showUpdate: function (contentType) {
        jQuery.ajax({
            dataType: 'json',
            url: 'index.php',
            data: {
                option: 'com_fabrik',
                task: 'contenttype.preview',
                contentType: contentType
            }
        }).done(function (data) {
            jQuery('#contentTypeListPreview').empty().html(data.preview);
            jQuery('#contentTypeListAclUi').empty().html(data.aclMap);
        });
    }

});