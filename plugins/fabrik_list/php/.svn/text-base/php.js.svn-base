var fbTableRunPHP = new Class({
	Extends : FbListPlugin,
	initialize: function(options) {
		this.setOptions(options);
		head.ready(function() {
			var l = this.getList().getForm().getElement('input[name=listid]');
			if(typeOf(l) === 'null') {
				return;
			}
			this.listid = l.value;
			this.watchButton();
		}.bind(this));
	},
	
	
	watchButton:function() {
		var button = this.list.getForm().getElement('input[name='+this.options.name+']');
		if(!button) {
			return;
		}
		button.addEvent('click', function(e) {
			e.stop();
			var ok = false;
			var additional_data = this.options.additional_data;
			var hdata = $H({});
			this.list.getForm().getElements('input[name^=ids]').each(function(c) {
				if(c.checked) {
					ok = true;
					if (additional_data) {
						var row_index = c.name.match(/ids\[(\d+)\]/)[1];
						if (!hdata.has(row_index)) {
							hdata.set(row_index, $H({}));
						}
						hdata[row_index]['rowid'] = c.value;
						additional_data.split(',').each(function(elname){
							var cell_data = c.findClassUp('fabrik_row').getElements('td.fabrik_row___' + elname)[0].innerHTML;
							hdata[row_index][elname] = cell_data;
						});
					}
				}
			});
			if(!ok) {
				alert('Please select a row!');
				return;
			}
			if (additional_data) {
				this.list.getForm().getElement('input[name=fabrik_listplugin_options]').value = Json.encode(hdata);
			}
			this.list.getForm().getElement('input[name=fabrik_listplugin_name]').value = 'tablephp';
			this.list.getForm().getElement('input[name=fabrik_listplugin_renderOrder]').value = button.name.replace('tablephp-', '');
			this.list.submit('doPlugin');
		}.bind(this));
	}
});