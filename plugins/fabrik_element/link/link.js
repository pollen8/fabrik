var FbLink = new Class({

	Extends: FbElementList,
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

	getValue : function () {
		var s = this._getSubElements();
		var a = [];
		s.each(function (v) {
			a.push(v.get('value'));
		});
		return a;
	}

});