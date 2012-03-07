var FbListWebservice = new Class({
	Extends: FbListPlugin,
	initialize: function (options) {
		this.parent(options);
	},
	
	buttonAction: function () {
		this.list.submit('list.doPlugin');
	}
});