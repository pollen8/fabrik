/**
 * List Download
 *
 * @copyright: Copyright (C) 2005-2016, fabrikar.com - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
define(['jquery', 'fab/list-plugin'], function (jQuery, FbListPlugin) {
	var FbListDownload = new Class({
		Extends   : FbListPlugin,
		initialize: function (options) {
			this.parent(options);
		}
	});

	return FbListDownload;
});