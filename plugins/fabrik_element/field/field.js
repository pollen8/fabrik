var FbField = new Class({
	Extends: FbElement,
	initialize: function (element, options) {
		this.plugin = 'fabrikfield';
		this.parent(element, options);
	},
	
	select: function () {
		this.getElement().select();
	},
	
	focus: function () {
		this.getElement().focus();
	}
});