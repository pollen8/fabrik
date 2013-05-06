var FbListCanEditRow = new Class({
	Extends: FbListPlugin,
	
	initialize: function (options) {
		this.parent(options);
		Fabrik.addEvent('onCanEditRow', function (list, args) {
			this.onCanEditRow(list, args);
		}.bind(this));
	},
	
	onCanEditRow: function (list, rowid) {
		rowid = rowid[0];
		list.result = this.options.acl[rowid];
	}
});