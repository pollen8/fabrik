var FabrikModalRepeat = new Class({

	initialize: function (el, names, field) {
		this.names = names;
		this.field = field;
		this.content = false;
		this.setup = false;
		this.elid = el;
		
		// If the parent field is inserted via js then we delay the loading untill the html is present
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
			e.stop();
			if (!this.win) {
				this.win = new Element('div', {'styles': {'padding': '5px', 'background-color': '#fff', 'display': 'none', 'z-index': 9999}}).inject(document.body);
				this.win.adopt(this.el);
				var close = new Element('button.btn.button.btn-primary').set('text', 'close');
				close.addEvent('click', function (e) {
					e.stop();
					this.store();
					this.close();
				}.bind(this));
				var controls = new Element('div.controls.form-actions', {'styles': {'text-align': 'right', 'margin-bottom': 0}}).adopt(close);
				this.win.adopt(controls);
				this.win.position();
				this.mask = new Mask(document.body, {style: {'background-color': '#000', 'opacity': 0.4, 'z-index': 9998}});
				this.content = this.el;
				this.build();
				this.watchButtons();
			}
			this.win.show();
			this.win.position();
			this.resizeWin(true);
			this.win.position();
			this.mask.show();
		}.bind(this));
	},
	
	resizeWin: function (setup) {
		var size = this.el.getDimensions(true);
		var wsize = this.win.getDimensions(true);
		var y = setup ? wsize.y : size.y + 30;
		//this.win.setStyles({'width': size.x + 'px', 'height': (y) + 'px'});
	},
	
	close: function () {
		this.win.hide();
		this.mask.hide();
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
		// Reapply radio button selections
		this.getTrs().each(function (tr, i) {
			if (r = tr.getElement('input[type=radio][value=' + radiovals[i] + ']')) {
				r.checked = 'checked';
			}
		});
	},
	
	watchButtons: function () {
		if (this.buttonsWatched) {
			return;
		}
		this.buttonsWatched = true;
		this.content.addEvent('click:relay(a.add)', function (e) {
			if (tr = this.findTr(e)) {
				
				// Store radio button selections
				var radiovals = this._getRadioValues(); 
				
				if (tr.getChildren('th').length !== 0) {
					clone = this.tmpl.clone(); 
				} else {
					clone = tr.clone();
				}
				clone.inject(tr, 'after');
				this.stripe();
				
				// Reapply values as renaming radio buttons 
				this._setRadioValues(radiovals);
				this.resizeWin();
				
				if (jQuery) {
					
					// Chosen reset 
					clone.getElements('select').removeClass('chzn-done');
					clone.getElements('.chzn-container').destroy();
					
					jQuery('select').chosen({
						disable_search_threshold : 10,
						allow_single_deselect : true
					});
				}
			}
			this.win.position();
			e.stop();
		}.bind(this));
		this.content.addEvent('click:relay(a.remove)', function (e) {
			
			// If only one row -don't remove
			var rows = this.content.getElements('tbody tr');
			if (rows.length <= 1) {
				// return;
			}
			
			if (tr = this.findTr(e)) {
				tr.dispose();
			}
			this.resizeWin();
			this.win.position();
			e.stop();
		}.bind(this));
	},
	
	getTrs: function () {
		return this.content.getElement('tbody').getElements('tr');
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
		var newrow = keys.length === 0 || a[keys[0]].length === 0 ? true : false;
		//var rowcount = keys.length === 0 ? 1 : a[keys[0]].length;
		var rowcount = newrow ? 1 : a[keys[0]].length;
		
		// Build the rows from the json object
		for (var i = 1; i < rowcount; i ++) {
			tr.clone().inject(tr, 'after');
		}
		this.stripe();
		var trs = this.getTrs();
		
		// Populate the cloned fields with the json values
		for (i = 0; i < rowcount; i++) {
			keys.each(function (k) {
				trs[i].getElements('*[name*=' + k + ']').each(function (f) {
					if (f.get('type') === 'radio') {
						if (f.value === a[k][i]) {
							f.checked = true;
						}
					} else {
						// Works for input,select and textareas
						f.value = a[k][i];
					}	
				});
			});
		}
		if (newrow || typeOf(this.tmpl) === 'null') {
			this.tmpl = tr;
		}
		if (newrow) {
			tr.dispose();
		}
		
	},
	
	findTr: function (e) {
		var tr = e.target.getParents().filter(function (p) {
			return p.get('tag') === 'tr';
		});
		return (tr.length === 0) ? false : tr[0];
	},
	
	store: function () {
		
		// Get the current values 
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
		// Store them in the parent field.
		this.field.value = JSON.encode(json);
		return true;
	}

});