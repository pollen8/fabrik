var FbListJs = new Class({
	Extends: FbListPlugin,
	
	options: {
		'statusMsg': ''
	},
	
	initialize: function (options) {
		this.parent(options);
	},
	
	buttonAction: function () {
		var statusMsg;
		var chxs = this.list.getForm().getElements('input[name^=ids]').filter(function (i) {
			return i.checked;
		});
		var ids = chxs.map(function (chx) {
			return chx.get('value');
		});
		if (this.options.js_code !== '') {
			if (eval(this.options.js_code) === false) {
				return;
			}
		}
		if (statusMsg === undefined) {
			statusMsg = this.options.statusMsg;
		}
		if (statusMsg !== '') {
			alert(statusMsg);
		}
	}
});