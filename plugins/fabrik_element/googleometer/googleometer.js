

define(['jquery', 'fab/element'], function (jQuery, FbElement) {
	window.FbGoogleometer = new Class({
		Extends   : FbElement,
		initialize: function (element, options) {
			this.parent(element, options);
		}
	});

	return window.FbGoogleometer;
});