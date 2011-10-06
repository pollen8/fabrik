/**
 * @author Robert
 */

/*jshint mootools: true */
/*global Fabrik:true, fconsole:true, Joomla:true, CloneObject:true, $A:true, $H:true,unescape:true,Asset:true */

var FbElementList =  new Class({
	
	Extends: FbElement,
		
	initialize: function (element, options) {
		this.parent(element, options);
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
	
	addNewEvent: function (action, js) {
		if (action === 'load') {
			this.loadEvents.push(js);
			this.runLoadEvent(js);
		} else {
			this._getSubElements();
			this.subElements.each(function (el) {
				el.addEvent(action, function (e) {
					$type(js) === 'function' ? js.delay(0) : eval(js);
				});
			});
		}
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
	}
});