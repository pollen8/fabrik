var FbField = new Class({
	Extends: FbElement,
	initialize: function (element, options) {
		this.plugin = 'fabrikfield';
		this.parent(element, options);
	},
	
	select: function () {
		this.element.select();
	},
	
	focus: function () {
		this.element.focus();
	}
});