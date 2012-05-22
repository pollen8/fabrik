var FabrikModalRepeat = new Class({

	initialize: function (el, names, field) {
		this.names = names;
		this.field = field;
		this.content = false;
		this.setup = false;
		this.elid = el;
		//if the parent field is inserted via js then we delay the loading untill the html is present
		if (!this.ready()) {
			this.timer = this.testReady.periodical(500, this);
		} else {
			this.setUp();
		}
	},

	ready: function () {
		return typeOf(document.id(this.elid)) === 'null' ? false : true;
	},


	testReady: function () {
		if (!this.ready()) {
			return;
		}
		if (this.timer) {
			clearInterval(this.timer);
		}
		this.setUp();
	},

	setUp: function () {
		this.button = document.id(this.elid + '_button');
		this.el = document.id(this.elid).getElement('table');
		this.el.id = this.elid + '-table';
		this.field = document.id(this.field);
		this.button.addEvent('click', function (e) {
			if (!this.setup) {
				//seems that trying to inject a <form> as a string causes issues
				SqueezeBox.open(this.el, {handler: 'adopt', 
					onClose: function (c) {
						this.onClose(c);
					}.bind(this),
					onOpen: function () {
						this.content = this.el;
						this.build();
						this.watchButtons();
						this.setup = true;
					}.bind(this)
				});
			} else {
				var c = this.content;
				SqueezeBox.open(null, {handler: 'string', content: c,
					onClose: function (c) {
						this.onClose(c);
					}.bind(this),
					onOpen: function () {
						this.content = c;
						this.watchButtons();
						this.resizeModal();
					}.bind(this)
				});
			}
		}.bind(this));
	},
	
	_getRadioValues: function () {
		var radiovals = [];
		this.getTrs().each(function (tr) {
			var v = (sel = tr.getElement('input[type=radio]:checked')) ? sel.get('value') : v = '';
			radiovals.push(v);
		});
		return radiovals;
	},
	
	_setRadioValues: function (radiovals) {
		//reapply radio button selections
		this.getTrs().each(function (tr, i) {
			if (r = tr.getElement('input[type=radio][value=' + radiovals[i] + ']')) {
				r.checked = 'checked';
			}
		});
	},
	
	watchButtons: function () {
		this.content.addEvent('click:relay(a.add)', function (e) {
			if (tr = this.findTr(e)) {
				//store radio button selections
				var radiovals = this._getRadioValues(); 
				tr.clone().inject(tr, 'after');
				this.stripe();
				//reapply values as renaming radio buttons 
				this._setRadioValues(radiovals);
				this.resizeModal();
			}
			e.stop();
		}.bind(this));
		this.content.addEvent('click:relay(a.remove)', function (e) {
			if (tr = this.findTr(e)) {
				tr.dispose();
				this.resizeModal();
			}
			e.stop();
		}.bind(this));
	},
	
	getTrs: function () {
		return this.content.getElement('tbody').getElements('tr');
	},
	
	resizeModal: function () {
		var s = this.content.getSize();
		s.x = s.x + 20;
		if (s.y + 50 > document.window.getSize().y) {
			s.y = document.window.getSize().y - 50;
		}
		SqueezeBox.resize(s, false);
	},
	
	stripe: function () {
		trs = this.getTrs();
		for (var i = 0; i < trs.length; i ++) {
			trs[i].removeClass('row1').removeClass('row0');
			trs[i].addClass('row' + i % 2);
			
			var chx = trs[i].getElements('input[type=radio]');
			chx.each(function (r) {
				r.name = r.name.replace(/\[([0-9])\]/, '[' + i + ']');
			});
		}
	},
	
	build: function () {
		if (this.setup) {
			return;
		}
		var a = JSON.decode(this.field.get('value'));
		if (typeOf(a) === 'null') {
			a = {};
		}
		var tr = this.content.getElement('tbody').getElement('tr');
		var keys = Object.keys(a);
		var rowcount = keys.length === 0 ? 1 : a[keys[0]].length;
		//build the rows from the json object
		for (var i = 1; i < rowcount; i ++) {
			tr.clone().inject(tr, 'after');
		}
		this.stripe();
		var trs = this.getTrs();
		//populate the cloned fields with the json values
		for (i = 0; i < rowcount; i++) {
			keys.each(function (k) {
				trs[i].getElements('*[name*=' + k + ']').each(function (f) {
					if (f.get('type') === 'radio') {
						if (f.value === a[k][i]) {
							f.checked = true;
						}
					} else {
						f.value = a[k][i]; //works for input,select and textareas
					}	
				});
			});
		}
		this.resizeModal();
	},
	
	findTr: function (e) {
		var tr = e.target.getParents().filter(function (p) {
			return p.get('tag') === 'tr';
		});
		return (tr.length === 0) ? false : tr[0];
	},
	
	onClose: function (c) {
		this.content = c.getElement('table').clone();
		//get the current values 
		var json = {};
		for (var i = 0; i < this.names.length; i++) {
			var n = this.names[i];
			var fields = this.content.getElements('*[name*=' + n + ']');
			json[n] = [];	
			fields.each(function (field) {
				if (field.get('type') === 'radio') {
					if (field.get('checked') === true) {
						json[n].push(field.get('value'));
					}
				} else {
					json[n].push(field.get('value'));
				}
			}.bind(this));		
		}
		//store them in the parent field.
		this.field.value = JSON.encode(json);
		return true;
	}

});