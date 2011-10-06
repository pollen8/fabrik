var FbRadio = new Class({
	Extends : FbElementList,
	initialize : function (element, options) {
		this.plugin = 'fabrikradiobutton';
		this.parent(element, options);
		this._getSubElements();
		if (this.options.allowadd === true && this.options.editable !== false) {
			this.watchAddToggle();
			this.watchAdd();
		}
	},

	watchAddToggle : function () {
		var c = this.getContainer();
		var d = c.getElement('div.addoption');

		var a = c.getElement('.toggle-addoption');
		if (this.mySlider) {
			// copied in repeating group so need to remove old slider html first
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
			new Event(e).stop();
			this.mySlider.toggle();
		}.bind(this));
	},

	watchAdd : function () {
		var val;
		if (this.options.allowadd === true && this.options.editable !== false) {
			var id = this.options.element;
			var c = this.getContainer();
			c.getElement('input[type=button]').addEvent('click', function (event) {
				var l = c.getElement('input[name=addPicklistLabel]');
				var v = c.getElement('input[name=addPicklistValue]');
				var label = l.value;
				if (v) {
					val = v.value;
				} else {
					val = label;
				}
				if (val === '' || label === '') {
					alert(Joomla.JText._('PLG_ELEMENT_RADIO_ENTER_VALUE_LABEL'));
				} else {
					var r = this.subElements.getLast().findUp('div').clone();
					r.getElement('input').value = val;
					var lastid = r.getElement('input').id.replace(id + '_', '').toInt();
					lastid++;
					r.getElement('input').checked = 'checked';
					r.getElement('input').id = id + '_' + lastid;
					r.getElement('span').set('text', label);
					r.inject(this.subElements.getLast().findUp('div'));
					this._getSubElements();
					e.stop();
					if (v) {
						v.value = '';
					}
					l.value = '';
					this.addNewOption(val, label);
				}
			}.bind(this));
		}
	},

	getValue : function () {
		if (!this.options.editable) {
			return this.options.value;
		}
		var v = '';
		this._getSubElements().each(function (sub) {
			if (sub.checked) {
				v = sub.get('value');
				return v;
			}
			return null;
		});
		return v;
	},

	setValue : function (v) {
		if (!this.options.editable) {
			return;
		}
		this._getSubElements().each(function (sub) {
			if (sub.value === v) {
				sub.checked = 'checked';
			}
		});
	},

	// get the sub element which are the checkboxes themselves

	_getSubElements : function () {
		if (!this.element) {
			this.subElements = $A([]);
		} else {
			this.subElements = this.element.getElements('input');
		}
		return this.subElements;
	},

	update : function (val) {
		if (!this.options.editable) {
			if (val === '') {
				this.element.innerHTML = '';
				return;
			}
			this.element.innerHTML = $H(this.options.data).get(val);
			return;
		} else {
			var els = this._getSubElements();
			if (typeOf(val) === 'array') {
				els.each(function (el) {
					if (val.contains(el.value)) {
						el.setProperty('checked', 'checked');
					}
				});
			} else {
				els.each(function (el) {
					if (el.value === val) {
						el.setProperty('checked', 'checked');
					}
				});
			}
		}
	},

	cloned : function () {
		if (this.options.allowadd === true && this.options.editable !== false) {
			this.watchAddToggle();
			this.watchAdd();
		}
	}
});