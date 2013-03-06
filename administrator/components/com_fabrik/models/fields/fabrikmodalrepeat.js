var FabrikModalRepeat = new Class({

	initialize: function (el, names, field) {
		this.names = names;
		this.field = field;
		this.content = false;
		this.setup = false;
		this.elid = el;
		this.win = {};
		this.el = {};
		this.field = {};
		
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
		console.log(document.id(this.elid));
		this.button = document.id(this.elid + '_button');
		
		
		
		document.addEvent('click:relay(*[data-modal=' + this.elid + '])', function (e, target) {
			var id = target.getNext('input').id; // correct when in repeating group
			this.field[id] = target.getNext('input');
			if (!this.el[id]) {
				var c = target.getParent('li');
				
				if (c.getElement('table')) {
					this.el[id] = c.getElement('table');
					target.store('table', this.el[id]);
				} else {
					this.el[id] = target.retrieve('table');
				}
				//this.el[id].id = this.elid + '-table';
			}
			console.log('click', id);
			this.openWindow(id);
		}.bind(this));
	},
	
	openWindow: function (target) {
		if (!this.win[target]) {
			this.win[target] = new Element('div', {'data-modal-content': target, 'styles': {'padding': '5px', 'background-color': '#fff', 'display': 'none', 'z-index': 9999}}).inject(document.body);
			this.win[target].adopt(this.el[target]);
			var close = new Element('button.btn.button').set('text', 'close');
			close.addEvent('click', function (e) {
				e.stop();
				this.store();
				this.close();
			}.bind(this));
			var controls = new Element('div.controls', {'styles': {'text-align': 'right'}}).adopt(close);
			this.win[target].adopt(controls);
			this.win[target].position();
			this.mask = new Mask(document.body, {style: {'background-color': '#000', 'opacity': 0.4, 'z-index': 9998}});
			this.content = this.el[target];
			this.build(target);
			this.watchButtons(this.win[target], target);
		}
		this.win[target].show();
		this.win[target].position();
		this.resizeWin(true);
		this.win[target].position();
		this.mask.show();
	},
	
	resizeWin: function (setup) {
		Object.each(this.win, function (win, key) {
			console.log(this.el, key);
			var size = this.el[key].getDimensions(true);
			var wsize = win.getDimensions(true);
			var y = setup ? wsize.y : size.y + 30;
			win.setStyles({'width': size.x + 'px', 'height': (y) + 'px'});	
		}.bind(this));
		
	},
	
	close: function () {
		Object.each(this.win, function (win, key) {
			win.hide();
		});
		this.mask.hide();
	},

	_getRadioValues: function (target) {
		var radiovals = [];
		this.getTrs(target).each(function (tr) {
			var v = (sel = tr.getElement('input[type=radio]:checked')) ? sel.get('value') : v = '';
			radiovals.push(v);
		});
		return radiovals;
	},
	
	_setRadioValues: function (radiovals, target) {
		// Reapply radio button selections
		this.getTrs(target).each(function (tr, i) {
			if (r = tr.getElement('input[type=radio][value=' + radiovals[i] + ']')) {
				r.checked = 'checked';
			}
		});
	},
	
	watchButtons: function (win, target) {
		/*if (this.buttonsWatched) {
			return;
		}
		this.buttonsWatched = true;*/
		win.addEvent('click:relay(a.add)', function (e) {
			if (tr = this.findTr(e)) {
				
				// Store radio button selections
				var radiovals = this._getRadioValues(target); 
				
				if (tr.getChildren('th').length !== 0) {
					this.tmpl.clone().inject(tr, 'after');
				} else {
					tr.clone().inject(tr, 'after');
				}
				this.stripe(target);
				
				// Reapply values as renaming radio buttons 
				this._setRadioValues(radiovals, target);
				this.resizeWin(win);
			}
			win.position();
			e.stop();
		}.bind(this));
		win.addEvent('click:relay(a.remove)', function (e) {
			
			// If only one row -don't remove
			var rows = this.content.getElements('tbody tr');
			if (rows.length <= 1) {
				// return;
			}
			
			if (tr = this.findTr(e)) {
				tr.dispose();
			}
			this.resizeWin(win);
			win.position();
			e.stop();
		}.bind(this));
	},
	
	getTrs: function (target) {
		return this.win[target].getElement('tbody').getElements('tr');
	},
	
	stripe: function (target) {
		trs = this.getTrs(target);
		for (var i = 0; i < trs.length; i ++) {
			trs[i].removeClass('row1').removeClass('row0');
			trs[i].addClass('row' + i % 2);
			
			var chx = trs[i].getElements('input[type=radio]');
			chx.each(function (r) {
				r.name = r.name.replace(/\[([0-9])\]/, '[' + i + ']');
			});
		}
	},
	
	build: function (target) {
		
		var a = JSON.decode(this.field[target].get('value'));
		if (typeOf(a) === 'null') {
			a = {};
		}
		var tr = this.win[target].getElement('tbody').getElement('tr');
		var keys = Object.keys(a);
		var newrow = keys.length === 0 || a[keys[0]].length === 0 ? true : false;
		var rowcount = newrow ? 1 : a[keys[0]].length;
		
		// Build the rows from the json object
		for (var i = 1; i < rowcount; i ++) {
			tr.clone().inject(tr, 'after');
		}
		this.stripe(target);
		var trs = this.getTrs(target);
		
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