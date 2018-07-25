var fbLockrow = FbElement.extend({
	initialize: function(element, options) {
		this.parent(element, options);
		this.plugin = 'fbLockrow';
		this.setOptions(element, options);
	}
});