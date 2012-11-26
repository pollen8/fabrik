requirejs(['fab/elementlist'], function () {
	FbRadio = new Class({
		Extends: FbElementList,
		
		type: 'radio', // sub element type
		
		initialize: function (element, options) {
			this.plugin = 'fabrikradiobutton';
			this.parent(element, options);
		},
	
		watchAddToggle: function () {
			var c = this.getContainer();
			var d = c.getElement('div.addoption');
			var a = c.getElement('.toggle-addoption');
			if (this.mySlider) {
				// Copied in repeating group so need to remove old slider html first
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
	
		getValue: function () {
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
	
		setValue: function (v) {
			if (!this.options.editable) {
				return;
			}
			this._getSubElements().each(function (sub) {
				if (sub.value === v) {
					sub.checked = 'checked';
				}
			});
		},
	
		update: function (val) {
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
	
		cloned: function (c) {
			if (this.options.allowadd === true && this.options.editable !== false) {
				this.watchAddToggle();
				this.watchAdd();
			}
			this.parent(c);
		}
	});
});