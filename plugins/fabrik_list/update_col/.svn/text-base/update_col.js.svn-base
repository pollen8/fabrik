var FbListUpdateCol2 = new Class({});

var FbListUpdateCol = new Class({
	Extends : FbListPlugin,
	initialize: function(options) {
		this.parent(options);
		head.ready(function() {
			var l = this.getList().getForm().getElement('input[name=listid]');
			// in case its in a viz
			if(typeOf(l) === 'null'){
				return;
			};
			this.listid = l.value;
		}.bind(this));
	}
});


