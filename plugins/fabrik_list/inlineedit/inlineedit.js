var FbListInlineEdit = new Class({
	Extends: FbListPlugin,
	
	initialize: function (options) {
		this.parent(options);
		this.defaults = {};
		this.editors = {};
		this.inedit = false;
		this.saving = false;
		head.ready(function () {
			//assigned in list.js fabrik3
			if (typeOf(this.getList().getForm()) === 'null') {
				return false;
			}
			this.listid = this.options.listid;
			this.setUp();
		}.bind(this));
		
		Fabrik.addEvent('fabrik.list.clearrows', function () {
			this.cancel();			
		}.bind(this));
		
		Fabrik.addEvent('fabrik.list.inlineedit.stopEditing', function () {
			this.stopEditing();
		}.bind(this));
		
		Fabrik.addEvent('fabrik.list.updaterows', function () {
			this.watchCells();
		}.bind(this));
		
		Fabrik.addEvent('fabrik.list.ini', function () {
			var table = this.getList();
			var formData = table.form.toQueryString().toObject();
			formData.format = 'raw';
			formData.listref = this.options.ref;
			var myFormRequest = new Request.JSON({'url': '',
				data: formData,
				onComplete: function () {
					console.log('complete');
				},
				onSuccess: function (json) {
					console.log('success');
					json = Json.evaluate(json.stripScripts());
					table.options.data = json.data;
				}.bind(this),
				'onFailure': function (xhr) {
					console.log('ajax inline edit failure', xhr);
				},
				'onException': function (headerName, value) {
					console.log('ajax inline edit exception', headerName, value);
				}
			}).send(); 
		}.bind(this));
		
		//check for a single element whose click value should trigger the save (ie radio buttons)
		Fabrik.addEvent('fabrik.element.click', function () {
			if (Object.getLength(this.options.elements) === 1 && this.options.showSave === false) {
				this.save(null, this.editing);
			}
		}.bind(this));
	},
	
	setUp: function () {
		if (typeOf(this.getList().getForm()) === 'null') {
			return;
		}
		this.scrollFx = new Fx.Scroll(window, {
			'wait': false
		});
		this.watchCells();
		document.addEvent('keydown', this.checkKey.bindWithEvent(this));
	},

	watchCells: function () {
		var firstLoaded = false;
		this.getList().getForm().getElements('.fabrik_element').each(function (td, x) {
			if (this.canEdit(td)) {
				if (!firstLoaded && this.options.loadFirst) {
					firstLoaded = this.edit(null, td);
					if (firstLoaded) {
						this.select(null, td);
					}
				}
				if (!this.isEditable(td)) {
					return;
				}
				this.setCursor(td);
				td.removeEvents();
				td.addEvent(this.options.editEvent, this.edit.bindWithEvent(this, [td]));
				td.addEvent('click', this.select.bindWithEvent(this, [td]));
			
				td.addEvent('mouseenter', function (e) {
					if (!this.isEditable(td)) {
						td.setStyle('cursor', 'pointer');
					}
				}.bind(this));
				td.addEvent('mouseleave', function (e) {
					td.setStyle('cursor', '');
				});
			}
		}.bind(this));
	},
	
	checkKey: function (e) {
		var nexttds, row, index;
		if (typeOf(this.td) !== 'element') {
			return;
		}
		switch (e.code) {
		case 39:
			//right
			if (this.inedit) {
				return;
			}
			if (typeOf(this.td.getNext()) === 'element') {
				e.stop();
				this.select(e, this.td.getNext());
			}
			break;
		case 9:
			//tab
			if (this.inedit) {
				if (this.options.tabSave) {
					if (typeOf(this.editing) === 'element') {
						this.save(e, this.editing);
					} else {
						this.edit(e, this.td);
					}
				}
				//var next = e.shift ? this.td.getPrevious() : this.td.getNext();
				var next = e.shift ? this.getPreviousEditable(this.td) : this.getNextEditable(this.td);
				if (typeOf(next) === 'element') {
					e.stop();
					this.select(e, next);
					this.edit(e, this.td);
				}
				return;
			}
			e.stop();
			if (e.shift) {
				if (typeOf(this.td.getPrevious()) === 'element') {
					this.select(e, this.td.getPrevious());
				}
			} else {
				if (typeOf(this.td.getNext()) === 'element') {
					this.select(e, this.td.getNext());
				}
			}
			break;
		case 37: //left
			if (this.inedit) {
				return;
			}
			if (typeOf(this.td.getPrevious()) === 'element') {
				e.stop();
				this.select(e, this.td.getPrevious());
			}
			break;
		case 40:
			//down
			if (this.inedit) {
				return;
			}
			row = this.td.getParent();
			if (typeOf(row) === 'null') {
				return;
			}
			index = row.getElements('td').indexOf(this.td);
			if (typeOf(row.getNext()) === 'element') {
				e.stop();
				nexttds = row.getNext().getElements('td');
				this.select(e, nexttds[index]);
			}
			break;
		case 38:
			//up
			if (this.inedit) {
				return;
			}
			row = this.td.getParent();
			if (typeOf(row) === 'null') {
				return;
			}
			index = row.getElements('td').indexOf(this.td);
			if (typeOf(row.getPrevious()) === 'element') {
				e.stop();
				nexttds = row.getPrevious().getElements('td');
				this.select(e, nexttds[index]);
			}
			break;
		case 27:
			//escape
			e.stop();
			this.select(e, this.editing);
			this.cancel(e);
			break;
		case 13:
			//enter
			e.stop();
			if (typeOf(this.editing) === 'element') {
				// stop textarea elements from submitting when you press enter
				if (this.editors[this.activeElementId].contains('<textarea')) {
					return;
				}
				this.save(e, this.editing);
			} else {
				this.edit(e, this.td);
			}
			break;
		}
	},
	
	select: function (e, td) {
		if (!this.isEditable(td)) {
			return;
		}
		var element = this.getElementName(td);
		var opts = this.options.elements[element];
		if (typeOf(opts) === false) {
			return;
		}
		if (typeOf(this.td) === 'element') {
			this.td.removeClass(this.options.focusClass);
		}
		this.td = td;
		if (typeOf(this.td) === 'element') {
			this.td.addClass(this.options.focusClass);
		}
		if (typeOf(this.td) === 'null') {
			return;
		}
		if (e && (e.type !== 'click' && e.type !== 'mouseover')) {
			//if using key nav scroll the cell into view
			var p = this.td.getPosition();
			var x = p.x - (window.getSize().x / 2) - (this.td.getSize().x / 2);
			var y = p.y - (window.getSize().y / 2) + (this.td.getSize().y / 2);
			this.scrollFx.start(x, y);
		}
	},
	
	getElementName: function (td) {
		var c = td.className.split(' ').filter(function (item, index) {
			return item !== 'fabrik_element' && item !== 'fabrik_row';
		});
		var element = c[0].replace('fabrik_row___', '');
		return element;
	},
	
	setCursor: function (td) {
		var element = this.getElementName(td);
		var opts = this.options.elements[element];
		if (typeOf(opts) === 'null') {
			return;
		}
		td.addEvent('mouseover', function (e) {
			if (this.isEditable(e.target)) {
				e.target.setStyle('cursor', 'pointer');
			}
		});
		td.addEvent('mouseleave', function (e) {
			if (this.isEditable(e.target)) {
				e.target.setStyle('cursor', '');
			}
		});
	},
	
	isEditable: function (cell) {
		if (cell.hasClass('fabrik_uneditable') || cell.hasClass('fabrik_ordercell') || cell.hasClass('fabrik_select') || cell.hasClass('fabrik_actions')) {
			return false;
		}
		return true;
	},
	
	getPreviousEditable: function (active) {
		var found = false;
		var tds = this.getList().getForm().getElements('.fabrik_element');
		for (var i = tds.length; i >= 0; i--) {
			if (found) {
				if (this.canEdit(tds[i])) {
					return tds[i];
				}
			}
			if (tds[i] === active) {
				found = true;
			}
		}
		return false;
	},
	
	getNextEditable: function (active) {
		var found = false;
		var next = this.getList().getForm().getElements('.fabrik_element').filter(function (td, i) {
			if (found) {
				if (this.canEdit(td)) {
					found = false;
					return true;
				} 
			}
			if (td === active) {
				found = true;
			}
			return false;
		}.bind(this));
		return next.getLast();
	},
	
	canEdit: function (td) {
		if (!this.isEditable(td)) {
			return false;
		}
		var element = this.getElementName(td);
		var opts = this.options.elements[element];
		if (typeOf(opts) === 'null') {
			return false;
		}
		return true;
	},
	
	edit: function (e, td) {
		if (this.saving) {
			return;
		}
		Fabrik.fireEvent('fabrik.plugin.inlineedit.editing');
		
		//only one field can be edited at a time
		if (this.inedit) {
			// if active event is mouse over - close the current editor
			if (this.options.editEvent === 'mouseover') {
				if (td === this.editing) {
					return;
				}
				this.select(e, this.editing);
				this.cancel();
			} else {
				return;
			}
		}
		if (!this.canEdit(td)) {
			return false;
		}
		if (typeOf(e) !== 'null') {
			e.stop();
		}
		var element = this.getElementName(td);
		var rowid = this.getRowId(td);
		var opts = this.options.elements[element];
		if (typeOf(opts) === 'null') {
			return;
		}
		this.inedit = true;
		this.editing = td;
		this.activeElementId = opts.elid;
		this.defaults[rowid + '.' + opts.elid] = td.innerHTML;
		
		var data = this.getDataFromTable(td);
		
		if (typeOf(this.editors[opts.elid]) === 'null' || typeOf(Fabrik['inlineedit_' + opts.elid]) === 'null') {
			// need to load on parent otherwise in table td size gets monged
			Fabrik.loader.start(td.getParent());
			var inline = this.options.showSave ? 1 : 0;
			
			var editRequest = new Request({
				'evalScripts': function (script, text) {
						this.javascript = script;
					}.bind(this),
				'evalResponse': false,
				'url': '',
				'data': {
					'element': element,
					'elid': opts.elid,
					'elementid': Object.values(opts.plugins),
					'rowid': rowid,
					'listref': this.options.ref,
					'formid': this.options.formid,
					'listid': this.options.listid,
					'inlinesave': inline,
					'inlinecancel': this.options.showCancel,
					'option': 'com_fabrik',
					'task': 'form.inlineedit',
					'format': 'raw'
				},

				'onSuccess': function (r) {
					// need to load on parent otherwise in table td size gets monged
					Fabrik.loader.stop(td.getParent());
					
					//don't use evalScripts = true as we reuse the js when tabbing to the next element. 
					// so instead set evalScripts to a function to store the js in this.javascript.
					//Previously js was wrapped in delay
					//but now we want to use it with and without the delay
	
					//delay the script to allow time for the dom to be updated
					(function () {
						$exec(this.javascript);
					}.bind(this)).delay(1000);
					td.empty().set('html', r);
					this._animate(td, 'in');
					r = r + '<script type="text/javascript">' + this.javascript + '</script>';
					this.editors[opts.elid] = r;
					this.watchControls(td);
					this.setFocus(td);
				}.bind(this),
				
				'onFailure': function (xhr) {
					this.saving = false;
					this.inedit = false;
					Fabrik.loader.stop(td.getParent());
					alert(editRequest.getHeader('Status'));
				}.bind(this),
				
				'onException': function (headerName, value) {
					this.saving = false;
					this.inedit = false;
					Fabrik.loader.stop(td.getParent());
					alert('ajax inline edit exception ' + headerName + ':' + value);
				}.bind(this)
				
			}).send();
		} else {
			//testing trying to re-use old form
			this.javascript;
			var html = this.editors[opts.elid].stripScripts(function (script) {
				this.javascript = script;
			}.bind(this));
			td.empty().set('html', html);
			//make a new instance of the element js class which will use the new html
			eval(this.javascript);
			//tell the element obj to update its value
			///triggered from element model
			Fabrik.addEvent('fabrik.list.inlineedit.setData', function () {
				$H(opts.plugins).each(function (fieldid) {
					var e = Fabrik['inlineedit_' + opts.elid].elements[fieldid];
					delete e.element;
					e.update(data[fieldid]);
					e.select();
				});
				this.watchControls(td);
				this.setFocus(td);	
			}.bind(this));
			
		}
		return true;
	},
	
	_animate: function (cell, d) {
		return;
	/*	//needs more work!
		var tip = cell.getChildren()[0];
		this.options.showDelay  = 0;
		this.options.hideDelay = 0;
		this.options.motion = 6;
		this.options.motionOnShow = true;
		this.options.motionOnHide = true;
		this.options.position = 'right';
		tip.store('options', this.options);
		
		tip.store('position', tip.getPosition(cell));
		
		clearTimeout(tip.retrieve('timeout'));
		tip.store('timeout', (function(t) { 
			var o = tip.retrieve('options'), din = (d == 'in');
			var m = { 'opacity': din ? 1 : 0 };
			
			if ((o.motionOnShow && din) || (o.motionOnHide && !din)) {
				var pos =  t.retrieve('position');
				if (!pos) return;
				switch (o.position) {
					case 'inside': 
					case 'top':
						m['top'] = din ? [pos.y - o.motion, pos.y] : pos.y - o.motion;
						break;
					case 'right':
						m['left'] = din ? [pos.x + o.motion, pos.x] : pos.x + o.motion;
						break;
					case 'bottom':
						m['top'] = din ? [pos.y + o.motion, pos.y] : pos.y + o.motion;
						break;
					case 'left':
						m['left'] = din ? [pos.x - o.motion, pos.x] : pos.x - o.motion;
						break;
				}
			}
			
			t.morph(m);
			if (!din) t.get('morph').chain(function () { this.dispose(); }.bind(t)); 
			
		}).delay((d == 'in') ? this.options.showDelay : this.options.hideDelay, this, tip));
		
		return this;
		*/
	},
	
	getDataFromTable: function (td) {
		var groupedData = this.getList().options.data;
		var element = this.getElementName(td);
		var ref = td.getParent('.fabrik_row').id;
		var v = {};
		this.vv = [];
		// $$$rob $H needed when group by applied
		if (typeOf(groupedData) === 'object') {
			groupedData = $H(groupedData);
		}
		//$H(groupedData).each(function (data) {
		groupedData.each(function (data) {
			if (typeOf(data) === 'array') {//groued by data in forecasting slotenweb app. Where groupby table plugin applied to data.
				for (var i = 0; i < data.length; i++) {
					if (data[i].id === ref) {
						this.vv.push(data[i]);
					}
				}
			} else {
				var vv = data.filter(function (row) {
					return row.id === ref;
				});
			}
		}.bind(this));
		var opts = this.options.elements[element];
		if (this.vv.length > 0) {
			$H(opts.plugins).each(function (elid, elementName) {
				v[elid] = this.vv[0].data[elementName + '_raw'];
			}.bind(this));
		}
		return v;
	},
	
	setTableData: function (row, element, val) {
		ref = row.id;
		var groupedData = this.getList().options.data;
		// $$$rob $H needed when group by applied
		if (typeOf(groupedData) === 'object') {
			groupedData = $H(groupedData);
		}
		groupedData.each(function (data, gkey) {
			data.each(function (tmpRow, dkey) {
				if (tmpRow.id === ref) {
					tmpRow.data[element + '_raw'] = val;
					this.currentRow = tmpRow;
				}
			}.bind(this));
		}.bind(this));
	},
	
	setFocus : function (td) {
		if (typeOf(td.getElement('.fabrikinput')) !== 'null') {
			td.getElement('.fabrikinput').focus();
		}
	},
	
	watchControls : function (td) {
		if (typeOf(td.getElement('.inline-save')) !== 'null') {
			td.getElement('.inline-save').removeEvents('click').addEvent('click', this.save.bindWithEvent(this, [td]));
		}
		if (typeOf(td.getElement('.inline-cancel')) !== 'null') {
			td.getElement('.inline-cancel').removeEvents('click').addEvent('click', this.cancel.bindWithEvent(this, [td]));
		}
	},
	
	save: function (e, td) {
		if (!this.editing) {
			return;
		}
		this.saving = true;
		this.inedit = false;
		if (e) {
			e.stop();
		}
		var element = this.getElementName(td);
		var opts = this.options.elements[element];
		
		// need to load on parent otherwise in table td size gets monged
		Fabrik.loader.start(td.getParent());
		
		var row = this.editing.getParent('.fabrik_row');
		var rowid = this.getRowId(row);
		td.removeClass(this.options.focusClass);
		var eObj = Fabrik['inlineedit_' + opts.elid];
		if (typeOf(eObj) === 'null') {
			fconsole('issue saving from inline edit: eObj not defined');
			this.cancel(e);
			return false;
		}
		//set package id to return js string
		var data = {
			'option': 'com_fabrik',
			'task': 'form.process',
			'format': 'raw',
			'_packageId': 1,
			'fabrik_ajax': 1,
			'element': element,
			'listref': this.options.ref,
			'elid': opts.elid,
			'plugin': opts.plugin,
			'rowid': rowid,
			'listid': this.options.listid,
			'formid': this.options.formid,
			'fabrik_ignorevalidation': 1
		};
		data.join = {};
		$H(eObj.elements).each(function (el) {
			el.getElement();
			var v = el.getValue();
			var jid = el.options.joinId;
			this.setTableData(row, el.options.element, v);
			if (el.options.isJoin) {
				if (typeOf(data.join[jid]) !== 'object') {
					data.join[jid] = {};
				}
				data.join[jid][el.options.elementName] = v;
			} else {
				data[el.options.element] = v;
			}
			
		}.bind(this));
		//post all the rows data to form.process
		data = Object.append(this.currentRow.data, data);
		data[eObj.token] = 1;

		td.empty();
		new Request({url: '',
			'data': data,
			'evalScripts': true,
			'onComplete': function (r) {
				td.empty().set('html', r);
				// need to load on parent otherwise in table td size gets monged
				Fabrik.loader.stop(td.getParent());
				Fabrik.fireEvent('fabrik.list.updaterows');
				this.saving = false;
			}.bind(this)
		}).send();
		//this.editing = null;
		this.stopEditing();
	},
	
	stopEditing: function (e) {
		var td = this.editing;
		if (td !== false) {
			td.removeClass(this.options.focusClass);
		}
		//this._animate(td, 'out');
		this.editing = null;
		this.inedit = false;
	},
	
	cancel: function (e) {
		if (e) {
			e.stop();
		}
		if (typeOf(this.editing) !== 'element') {
			return;
		}
		var row = this.editing.getParent('.fabrik_row');
		if (row === false) {
			return;
		}
		var rowid = this.getRowId(row);
		var td = this.editing;
		if (td !== false) {
			var element = this.getElementName(td);
			var opts = this.options.elements[element];
			var c = this.defaults[rowid + '.' + opts.elid];
			td.set('html', c);
		}
		this.stopEditing();
	}
});