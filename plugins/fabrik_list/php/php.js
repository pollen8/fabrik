var FbListPHP = new Class({
	Extends: FbListPlugin,
	initialize: function (options) {
		this.parent(options);
	},
	
	buttonAction: function () {
		var additional_data = this.options.additional_data;
		var hdata = $H({});
		this.list.getForm().getElements('input[name^=ids]').each(function (c) {
			if (c.checked) {
				ok = true;
				if (additional_data) {
					var row_index = c.name.match(/ids\[(\d+)\]/)[1];
					if (!hdata.has(row_index)) {
						hdata.set(row_index, $H({}));
					}
					hdata[row_index].rowid = c.value;
					additional_data.split(',').each(function (elname) {
						var cell_data = c.getParent('.fabrik_row').getElements('td.fabrik_row___' + elname)[0].innerHTML;
						hdata[row_index][elname] = cell_data;
					});
				}
			}
		});
		if (additional_data) {
			this.list.getForm().getElement('input[name=fabrik_listplugin_options]').value = Json.encode(hdata);
		}
		this.list.submit('list.doPlugin');
	}
});