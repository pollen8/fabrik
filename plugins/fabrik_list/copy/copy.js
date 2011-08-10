var fbTableCopy = new Class({
	Extends:FbListPlugin,
	initialize: function(options) {
		this.setOptions(options);
		head.ready(function() {
			var l = this.getList().getForm().getElement('input[name=listid]');
			//incase its in a viz
			if(typeOf(l) === 'null'){
				return false;
			}
			this.listid = l.value;
			this.watchButton();
		}.bind(this));
	},
	
	watchButton:function() {
		var button = this.list.getForm().getElement('input[name='+this.options.name+']');
		if(!button){
			return;
		}
		button.addEvent('click', function(e){
			e.stop();
			var ok = false;
			this.list.getForm().getElements('input[name^=ids]').each(function(c){
				if(c.checked){
					ok = true;
				}
			});
			if(!ok){
				alert('Please select a row!');
				return;
			}
			this.list.getForm().getElement('input[name=fabrik_listplugin_name]').value = 'copy';
			this.list.getForm().getElement('input[name=fabrik_listplugin_renderOrder]').value = button.name.replace('copy-', '');
			this.list.submit('doPlugin');
		}.bind(this));
	}
	});