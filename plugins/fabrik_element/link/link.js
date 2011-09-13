var FbLink = new Class({

	Extends: FbElement,
	initialize: function (element, options) {
		this.plugin = 'fabrikLink';
		this.parent(element, options);
		this.subElements = this._getSubElements();
	},

	update: function (val) {
		var subs = this.element.getElements('.fabrikinput');
		if (typeOf(val) === 'object') {
			subs[0].value = val.label;
			subs[1].value = val.link;
		} else {
			subs.each(function (i) {
				i.value = val;
			});
		}
	},

	addNewEvent: function (action, js) {
		if (action === 'load') {
			this.loadEvents.push(js);
			this.runLoadEvent(js);
		} else {
			this.subElements.each(function (el) {
				el.addEvent(action, function (e) {
					eval(js);
				});
			});
		}
	},
	// get the sub element which are the fields themselves

	_getSubElements: function () {
		if (!this.element) {
			this.subElements = $A();
		} else {
			this.subElements = this.element.getElements('input');
		}
		return this.subElements;
	},

	getValue : function () {
		var s = this._getSubElements();
		var a = [];
		s.each(function (v) {
			a.push(v.get('value'));
		});
		return a;
	}

});