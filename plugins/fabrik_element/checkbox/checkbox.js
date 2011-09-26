var FbCheckBox = new Class({
	Extends: FbElement,
	initialize: function (element, options) {
		this.plugin = 'fabrikcheckbox';
		this.parent(element, options);
		this._getSubElements();
		this.watchAdd();
	},
	
	watchAddToggle : function () {
		var c = this.getContainer();
		var d = c.getElement('div.addoption');
		var a = c.getElement('.toggle-addoption');
		if (this.mySlider) {
			//copied in repeating group so need to remove old slider html first
			var clone = d.clone();
			var fe = c.getElement('.fabrikElement');
			d.getParent().destroy();
			fe.adopt(clone);
			d = c.getElement('div.addoption');
			d.setStyle('margin', 0);
		}
		this.mySlider = new Fx.Slide(d, {
			duration : 500
		});
		this.mySlider.hide();
		a.addEvent('click', function (e) {
			e.stop();
			this.mySlider.toggle();
		}.bind(this));
	},
	
	watchAdd: function () {
		var val;
		if (this.options.allowadd === true && this.options.editable !== false) {
			var id = this.options.element;
			var c = this.getContainer();
			c.getElement('input[type=button]').addEvent('click', function (e) {
				var l = c.getElement('input[name=addPicklistLabel]');
				var v = c.getElement('input[name=addPicklistValue]');
				var label = l.value;
				if (v) {
					val = v.value;
				} else {
					val = label;
				}
				if (val === '' || label === '') {
					alert(Joomla.JText._('PLG_ELEMENT_CHECKBOX_ENTER_VALUE_LABEL'));
				}
				else {
					var r = this.subElements.getLast().findUp('li').clone();
					r.getElement('input').value = val;
					var lastid = r.getElement('input').id.replace(id + '_', '').toInt();
					lastid++;
					r.getElement('input').checked = 'checked';
					r.getElement('input').id = id + '_' + lastid;
					r.getElement('label').setProperty('for', id + '_' + lastid);
					r.getElement('span').set('text', label);
					r.inject(this.subElements.getLast().findUp('li'), 'after');
					this._getSubElements();
					e.stop();
					if (v) {
						v.value = '';
					}
					l.value = '';
					this.addNewOption(val, label);
					this.mySlider.toggle();
				}
			}.bind(this));
		}
	},
	
	getValue: function () {
		if (!this.options.editable) {
			return this.options.value;
		}
		var ret = [];
		if (!this.options.editable) {
			return this.options.value;
		}
		this._getSubElements().each(function (el) {
			if (el.checked) {
				ret.push(el.get('value'));
			}
		});
		return ret;
	},
	
	addNewEvent: function (action, js) {
		if (action === 'load') {
			this.loadEvents.push(js);
			this.runLoadEvent(js);
		} else {
			this._getSubElements();
			this.subElements.each(function (el) {
				el.addEvent(action, function (e) {
					eval(js);
				});
			});
		}
	},
	
		//get the sub element which are the checkboxes themselves
	
	_getSubElements: function () {
		if (!this.element) {
			this.subElements = $A();
		} else {
			this.subElements = this.element.getElements('input');
		}
		return this.subElements;
	},
	
	numChecked: function () {
		return this._getSubElements().filter(function (c) {
			return c.checked;
		}).length;
	},
	
	update: function (val) {
		if (typeOf(val) === 'string') {
			//val = val.split(this.options.splitter);
			val = val === '' ? [] : JSON.decode(val);
		}
		if (!this.options.editable) {
			this.element.innerHTML = '';
			if (val === '') {
				return;
			}
			var h = $H(this.options.data);
			val.each(function (v) {
				this.element.innerHTML += h.get(v) + "<br />";	
			}.bind(this));
			return;
		}
		this._getSubElements();
		this.subElements.each(function (el) {
			var chx = false;
			val.each(function (v) {
				if (v === el.value) {
					chx = true;
				}
			}.bind(this));
			el.checked = chx;
		}.bind(this));
	},
	
	cloned: function () {
		if (this.options.allowadd === true && this.options.editable !== false) {
			this.watchAddToggle();
			this.watchAdd();
		}
	}

});