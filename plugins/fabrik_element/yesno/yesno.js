console.log('yesno');
FbYesno = new Class({
	Extends: FbRadio,
	initialize: function (element, options) {
		console.log('ini');
		this.plugin = 'fabrikyesno';
		this.parent(element, options);
		// Seems slighly skewy in admin as the j template does the same code
		this.btnGroup();
	},
	
	btnGroup: function () {
		// Turn radios into btn-group
		var c = this.getContainer();
		c.getElements('.radio.btn-group label').addClass('btn');
		c.addEvent('mouseup:relay(.btn-group label)', function (e, label) {
			var id = label.get('for', ''), input;
			if (id !== '') {
				input = document.id(id);
			}
			if (typeOf(input) === 'null') {
				input = label.getElement('input');
			}
			var v = input.get('value');
			if (!input.get('checked')) {
				label.getParent('.btn-group').getElements('label').removeClass('active').removeClass('btn-success').removeClass('btn-danger').removeClass('btn-primary');
				if (v === '') {
					label.addClass('active btn-primary');
				} else if (v.toInt() === 0) {
					label.addClass('active btn-danger');
				} else {
					label.addClass('active btn-success');
				}
				input.set('checked', true);
			}
		});
		c.getElements(".btn-group input[checked=checked]").each(function (input) {
			var label = document.getElement('label[for=' + input.id + ']'),
			v = input.get('value');
			if (v === '') {
				label.addClass('active btn-primary');
			} else if (v === '0') {
				label.addClass('active btn-danger');
			} else {
				label.addClass('active btn-success');
			}
		});
	}
});
	