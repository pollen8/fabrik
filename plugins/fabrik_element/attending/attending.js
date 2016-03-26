
define(['jquery', 'fab/element'], function (jQuery, FbElement) {
    window.FbAttending = new Class({
        Extends   : FbElement,
        initialize: function (element, options) {
            this.parent(element, options);
            this.watchJoin();
            this.spinner = new Asset.image(Fabrik.liveSite + 'media/com_fabrik/images/ajax-loader.gif', {
                'alt'  : 'loading',
                'class': 'ajax-loader'
            });
            this.message = jQuery(this.element).find('.msg');
        },

        watchJoin: function () {
            var self = this,
                c = jQuery(this.getContainer()),
                b = c.find('*[data-action="add"]');

            // If duplicated remove old events

            b.off('click', function (e) {
                self.join(e);
            });

            b.on('click', function (e) {
                self.join(e);
            });
        },

        join: function () {
            this.save('join');
        },

        leave: function () {
            this.save('leave');
        },

        save: function (state) {
            this.spinner.inject(this.message);
            var self = this,
                data = {
                    'option'     : 'com_fabrik',
                    'format'     : 'raw',
                    'task'       : 'plugin.pluginAjax',
                    'plugin'     : 'attending',
                    'method'     : state,
                    'g'          : 'element',
                    'element_id' : this.options.elid,
                    'formid'     : this.options.formid,
                    'row_id'     : this.options.row_id,
                    'elementname': this.options.elid,
                    'userid'     : this.options.userid,
                    'rating'     : this.rating,
                    'listid'     : this.options.listid
                };

            jQuery.ajax({
                url   : '',
                'data': data,
            }).done(function () {
                self.spinner.remove();
            });
        }
    });

    return window.FbAttending;
});