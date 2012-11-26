requirejs(['element/radiobutton/radiobutton', 'fab/elementlist'], function () {

	FbYesno =  new Class({
		Extends: FbRadio,
		initialize: function (element, options) {
			this.plugin = 'fabrikyesno';
			this.parent(element, options);
		}
	});
	
});
