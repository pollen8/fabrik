var FbField = new Class({
	Extends: FbElement,
	initialize: function (element, options) {
		this.plugin = 'fabrikfield';
		this.parent(element, options);
	},
	
	select: function () {
		var element = this.getElement();
		if (element) {
			this.getElement().select();
		}
	},
	
	focus: function () {
		var element = this.getElement();
		if (element) {
			this.getElement().focus();
		}
	}
});