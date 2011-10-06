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
	
	addNewEvent : function (action, js) {
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
	}
});