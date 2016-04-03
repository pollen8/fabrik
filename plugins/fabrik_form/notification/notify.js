/**
 * List Notification
 *
 * @copyright: Copyright (C) 2005-2013, fabrikar.com - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

define(['jquery', 'fab/fabrik'], function (jQuery, Fabrik) {
	'use strict';
	var Notify = new Class({

		initialize: function (el, options) {
			this.options = options;
			var target = document.id(el),
				notify;
			if (target.getStyle('display') === 'none') {
				target = target.getParent();
			}

			target.addEvent('change', function (e) {
				notify = document.id(el).checked ? 1 : 0;
				Fabrik.loader.start(target, Joomla.JText._('COM_FABRIK_LOADING'));
				var myAjax = new Request({
					url : 'index.php?option=com_fabrik&task=plugin.pluginAjax&plugin=notification&method=toggleNotification',
					data: {
						g                  : 'form',
						format             : 'raw',
						fabrik_notification: 1,
						listid             : this.options.listid,
						formid             : this.options.formid,
						rowid              : this.options.rowid,
						notify             : notify
					},

					onComplete: function (r) {
						window.alert(r);
						Fabrik.loader.stop(target);
					}.bind(this)
				}).send();

			}.bind(this));
		}
	});

	return Notify;
});