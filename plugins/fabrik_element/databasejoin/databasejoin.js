var FbDatabasejoin = new Class({
	Extends: FbElement,
	
	options: {
		'id': 0,
		'formid': 0,
		'key': '',
		'label': '',
		'popwiny': 0,
		'windowwidth': 360,
		'displayType': 'dropdown',
		'popupform': 0,
		'listid': 0
	},
	
	initialize: function (element, options) {
		this.plugin = 'databasejoin';
		this.parent(element, options);
		this.changeEvents = []; // workaround for change events getting zapped on clone
		//if users can add records to the database join drop down
		if (this.options.allowadd === true && this.options.editable !== false) {
			this.startEvent = this.start.bindWithEvent(this);
			this.watchAdd();
			Fabrik.addEvent('fabrik.form.submitted', function (form, json) {
				//fired when form submitted - enables element to update itself with any new submitted data
				if (this.options.popupform === form.id) {
					this.appendInfo(json);
				}
			}.bind(this));
		}
		
		if (this.options.editable) {
			this.watchSelect();
			if (this.options.showDesc === true) {
				this.element.addEvent('change', this.showDesc.bindWithEvent(this));
			}
			if (this.options.displayType === 'checkbox') {
				// $$$rob 15/07/2011 - when selecting checkboxes have to programatically select hidden checkboxes which store the join ids.
				var selector = 'input[name*=' + this.options.elementName + '___' + this.options.elementShortName + ']';
				var idSelector = 'input[name*=' + this.options.elementName + '___id]';
				this.element.addEvent('click:relay(' + selector + ')', function (i) {
					this.element.getElements(selector).each(function (tmp, k) {
						if (tmp === i.target) {
							this.element.getElements(idSelector)[k].checked = i.target.checked;
						}
					}.bind(this));
				}.bind(this));
			}
		}
	},
	
	watchAdd: function () {
		
		if (c = this.getContainer()) {
			var b = c.getElement('.toggle-addoption');
			//if duplicated remove old events
			b.removeEvent('click', this.startEvent);
			b.addEvent('click', this.startEvent);
		}
	},
	
	start: function (e) {
		var url = "index.php?option=com_fabrik&task=form.view&tmpl=component&ajax=1&formid=" + this.options.popupform;
		var id = this.element.id + '-popupwin';
		this.windowopts = {
			'id': id,
			'title': 'Add',
			'contentType': 'xhr',
			'loadMethod': 'xhr',
			'contentURL': url,
			'width': this.options.windowwidth.toInt(),
			'height': 320,
			'y': this.options.popwiny,
			'minimizable': false,
			'collapsible': true,
			'onContentLoaded': function (win) {
				win.fitToContent();
			}
		};
		this.win = Fabrik.getWindow(this.windowopts);
		e.stop();
	},
	
	appendInfo: function (data) {
		var rowid = data.rowid;
		var formid = this.options.formid;
		var key = this.options.key;
		var label = this.options.label;
		var url = Fabrik.liveSite + 'index.php?option=com_fabrik&view=form&format=raw';
		var post = {
			'formid': this.options.popupform,
			'rowid': rowid
		};
		var myajax = new Request.JSON({url: url,
			'data': post,
			onSuccess: function (r) {
				var v = r.data[this.options.key];
				var l = r.data[this.options.label];
					
				switch (this.options.display_type) {
				case 'dropdown':
				/* falls through */
				case 'multilist':
					var o = this.element.getElements('option').filter(function (o, x) {
						if (o.get('value') === v) {
							this.options.display_type === 'dropdown' ? this.element.selectedIndex = x : o.selected = true;
							return true;
						}
					}.bind(this));
					if (o.length === 0) {
						opt = new Element('option', {'value': v, 'selected': 'selected'}).set('text', l);
						$(this.element.id).adopt(opt);
					}
					break;
				case 'auto-complete':
					labelfield = this.element.getParent('.fabrikElement').getElement('input[name=' + this.element.id + '-auto-complete]');
					this.element.value = v;
					labelfield.value = l;
					break;
				case 'checkbox':
					var chxs = this.element.getElements('> .fabrik_subelement');
					var newchx = chxs.getLast().clone();
					newchx.getElement('span').set('text', l);
					newchx.getElement('input').set('value', v);
					var last = chxs.length === 0 ? this.element : chxs.getLast();
					newchx.inject(last, 'after');
					
					var ids = this.element.getElements('.fabrikHide > .fabrik_subelement');
					var newid = ids.getLast().clone();
					newid.getElement('span').set('text', l);
					newid.getElement('input').set('value', 0); // to add a new join record set to 0
					last = ids.length === 0 ? this.element.getElements('.fabrikHide') : ids.getLast();
					newid.inject(last, 'after');
					break;
				case 'radio':
				/* falls through */
				default:
					o = this.element.getElements('.fabrik_subelement').filter(function (o, x) {
						if (o.get('value') === v) {
							o.checked = true;
							return true;
						}
					}.bind(this));
					if (o.length === 0) {
						var opt = new Element('div', {
							'class': 'fabrik_subelement'
						}).adopt(new Element('label').adopt([new Element('input', {
							'class': 'fabrikinput',
							'type': 'radio',
							'checked': true,
							'name': this.options.element + '[]',
							'value': v
						}), new Element('span').set('text', l)]));
						opt.inject($(this.element.id).getElements('.fabrik_subelement').getLast(), 'after');
					}
					break;
				}
					
				if (typeOf(this.element) === 'null') {
					return;
				}
			}.bind(this)
		}).send();
	},
	
	watchSelect: function () {
		if (c = this.getContainer()) {
			var sel = c.getElement('.toggle-selectoption');
			if (typeOf(sel) !== 'null') {
				sel.addEvent('click', this.selectRecord.bindWithEvent(this));
				Fabrik.addEvent('fabrik.list.row.selected', function (json) {
					if (this.options.popupform === json.formid) {
						this.update(json.rowid);
						var winid = this.element.id + '-popupwin-select';
						if (Fabrik.Windows[winid]) {
							Fabrik.Windows[winid].close();
						}
					}
				}.bind(this));
			}
		}
	},
	
	selectRecord: function (e) {
		e.stop();
		var id = this.element.id + '-popupwin-select';
		var url = Fabrik.liveSite + "index.php?option=com_fabrik&view=list&tmpl=component&layout=dbjoinselect&ajax=1&listid=" + this.options.listid;
		url += "&triggerElement=" + this.element.id;
		url += "&resetfilters=1";
		this.windowopts = {
			'id': id,
			'title': 'Select',
			'contentType': 'xhr',
			'loadMethod': 'xhr',
			'evalScripts': true,
			'contentURL': url,
			'width': this.options.windowwidth.toInt(),
			'height': 320,
			'y': this.options.popwiny,
			'minimizable': false,
			'collapsible': true,
			'onContentLoaded': function (win) {
				win.fitToContent();
			}
		};
		var mywin = Fabrik.getWindow(this.windowopts);
	},
	
	update: function (val) {
		this.getElement();
		if (typeOf(this.element) === 'null') {
			return;
		}
		if (!this.options.editable) {
			this.element.set('html', '');
			if (val === '') {
				return;
			}
			val = JSON.decode(val);
			var h = this.form.getFormData();
			if (typeOf(h) === 'object') {
				h = $H(h);
			}
			val.each(function (v) {
				if (typeOf(h.get(v)) !== 'null') {
					this.element.innerHTML += h.get(v) + "<br />";
				} else {
					//for detailed view prev/next pagination v is set via elements 
					//getROValue() method and is thus in the correct format - not sure that
					// h.get(v) is right at all but leaving in incase i've missed another scenario 
					this.element.innerHTML += v + "<br />";
				}	
			}.bind(this));
			return;
		}
		this.setValue(val);
	},
	
	setValue: function (val) {
		var found = false;
		if (typeOf(this.element.options) !== 'null') { //needed with repeat group code
			for (var i = 0; i < this.element.options.length; i++) {
				if (this.element.options[i].value === val) {
					this.element.options[i].selected = true;
					found = true;
					break;
				}
			}
		}
		if (!found && this.options.show_please_select) {
			if (this.element.get('tag') === 'input') {
				this.element.value = val;
				if (this.options.display_type === 'auto-complete') {
					//update the field label as well (do ajax as we dont know what the label should be (may included concat etc))
					var myajax = new Ajax({
						'url': Fabrik.liveSite + 'index.php?option=com_fabrik&view=form&format=raw&fabrik=' + this.form.id + '&rowid=' + val,
						'options': {
							'evalScripts': true
						},
						onSuccess: function (r) {
							r = Json.evaluate(r.stripScripts());
							var v = r.data[this.options.key];
							var l = r.data[this.options.label];
							if (typeOf(l) !== 'null') {
								labelfield = this.element.getParent('.fabrikElement').getElement('.autocomplete-trigger');
								this.element.value = v;
								labelfield.value = l;
							}
						}.bind(this)
					}).send();
				}
			} else {
				if (this.options.displayType === 'dropdown') {
					this.element.options[0].selected = true;
				} else {
					this.element.getElements('input').each(function (i) {
						if (i.get('value') === val) {
							i.checked = true;
						}
					});
				}
			}
		}
		this.options.value = val;
	},
	
	showDesc: function (e) {
		var v = e.target.selectedIndex;
		var c = this.element.getParent('.fabrikElementContainer').getElement('.dbjoin-description');
		var show = c.getElement('.description-' + v);
		c.getElements('.notice').each(function (d) {
			if (d === show) {
				var myfx = new Fx.Tween(show, {'property': 'opacity',
					'duration': 400,
					'transition': Fx.Transitions.linear
				});
				myfx.set(0);
				d.setStyle('display', '');
				myfx.start(0, 1);
			} else {
				d.setStyle('display', 'none');
			}
		});
	},
	
	getValue: function () {
		this.getElement();
		if (!this.options.editable) {
			return this.options.value;
		}
		if (typeOf(this.element) === 'null') {
			return '';
		}
		switch (this.options.display_type) {
		case 'dropdown':
		/* falls through */
		default:
			if (typeOf(this.element.get('value')) === 'null') {
				return '';
			}
			return this.element.get('value');
		case 'auto-complete':
			return this.element.value;
		case 'radio':
			var v = '';
			this._getSubElements().each(function (sub) {
				if (sub.checked) {
					v = sub.get('value');
					return v;
				}
				return null;
			});
			return v;
		}
	},
	
	getValues: function () {
		var v = $A([]);
		var search = (this.options.display_type !== 'dropdown') ? 'input' : 'option';
		$(this.element.id).getElements(search).each(function (f) {
			v.push(f.value);
		});
		return v;
	},
	
	cloned: function (c) {
		//c is the repeat group count
		// @TODO this is going to wipe out any user added change events to the element
		// cant' figure out how to just remove the cdd change events.
		// $$$ hugh - added workaround for change events, by storing them during addNewEvent
		// and re-adding them after we do this.
		this.element.removeEvents('change');
		this.changeEvents.each(function (js) {
			this.addNewEventAux('change', js);
		}.bind(this));
		if (this.options.allowadd === true && this.options.editable !== false) {
			this.startEvent = this.start.bindWithEvent(this);
			this.watchAdd();
		}
		this.watchSelect();
		if (this.options.display_type === 'auto-complete') {
			//update auto-complete fields id and create new autocompleter object for duplicated element
			var f = this.getContainer().getElement('.autocomplete-trigger');
			f.id = this.element.id + '-auto-complete';
			document.id(f.id).value = '';
			new FabAutocomplete(this.element.id, this.options.autoCompleteOpts);
		}
	},
	
	getAutoCompleteLabelField: function () {
		return this.element.findClassUp('fabrikElement').getElement('input[name=' + this.element.id + '-auto-complete]');
	},
	
	addNewEventAux: function (action, js) {
		switch (this.options.displayType) {
		case 'dropdown':
		/* falls through */
		default:
			if (this.element) {
				this.element.addEvent(action, function (e) {
					e.stop();
					(typeOf(js) === 'function') ? js.delay(0) : eval(js);
				});
			}
			break;
		case 'radio':
			this._getSubElements();
			this.subElements.each(function (el) {
				el.addEvent(action, function (e) {
					(typeOf(js) === 'function') ? js.delay(0) : eval(js);
				});
			});
			break;
		case 'auto-complete':
			var f = this.getAutoCompleteLabelField();
			if (typeOf(f) !== 'null') {
				f.addEvent(action, function (e) {
					e.stop();
					(typeOf(js) === 'function') ? js.delay(0) : eval(js);
				});
			}
			break;
		}		
	},
	
	addNewEvent: function (action, js) {
		if (action === 'load') {
			this.loadEvents.push(js);
			this.runLoadEvent(js);
			return;
		}
		// $$$ hugh - workaround for change events getting zapped on clone, where
		// we have to remove change events added by CDD's watching us
		if (action === 'change') {
			this.changeEvents.push(js);
		}
		this.addNewEventAux(action, js);
	}
});