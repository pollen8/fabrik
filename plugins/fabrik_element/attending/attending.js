var FbAttending = new Class({
	Extends : FbElement,
	initialize : function (element, options) {
		this.parent(element, options);
		this.watchJoin();
		this.spinner = new Asset.image(Fabrik.liveSite + 'media/com_fabrik/images/ajax-loader.gif', {
			'alt': 'loading',
			'class': 'ajax-loader'
		});
		this.message = this.element.getElement('.msg');
	},

	watchJoin: function () {
		if (c = this.getContainer()) {
			var b = c.getElement('*[data-action="add"]');

			// If duplicated remove old events

			b.removeEvent('click', function (e) {
				this.join(e);
			}.bind(this));

			b.addEvent('click', function (e) {
				this.join(e);
			}.bind(this));
		}
	},

	join: function () {
		this.save('join');
	},

	leave: function () {
		this.save('leave');
	},

	save: function (state) {
		this.spinner.inject(this.message);
		var data = {
			'option': 'com_fabrik',
			'format': 'raw',
			'task': 'plugin.pluginAjax',
			'plugin': 'attending',
			'method': state,
			'g': 'element',
			'element_id': this.options.elid,
			'formid': this.options.formid,
			'row_id': this.options.row_id,
			'elementname': this.options.elid,
			'userid': this.options.userid,
			'rating': this.rating,
			'listid': this.options.listid
		};
		console.log(data);

		var closeFn = new Request({
			url: '',
			'data': data,
			onComplete: function () {
				this.spinner.dispose();
			}.bind(this)
		}).send();
	}
});