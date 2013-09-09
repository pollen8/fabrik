/**
 * Form Submitter
 *
 * @copyright: Copyright (C) 2005-2013, fabrikar.com - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

/*jshint mootools: true */
/*global Fabrik:true, fconsole:true, Joomla:true, CloneObject:true, $H:true,unescape:true */

var FbFormSubmit = new Class({
	
	/**
	 * Hash of elements js objects
	 */
	elements: $H({}),
	
	/**
	 * Object of key = this.elements key, value = null|true|false. 
	 * Null - no result from onsubmit callback (in a waiting state)
	 * False - callback has returned false - should prevent the form from submitting
	 * True - callback has returned true, once all element callbacks return true the main callback is fired  
	 */
	results: {},
	
	addElement: function (key, element) {
		this.elements[key] = element;
	},
	
	/**
	 * Called from form.js.
	 * 
	 * @param   function  cb  Callback - fired once all elements have completed 
	 *                        their own onsubmit callbacks and return ture
	 */
	submit: function (cb) {
		this.elements.each (function (element, key) {
			this.results[key] = null;
			element.onsubmit(function (res) {
				this.results[key] = res;
			}.bind(this))
		}.bind(this));
		this.checker = this.check.periodical(500, this, [cb]);
	},
	
	/**
	 * Periodical checker on the element callback state (stored in this.results)
	 * 
	 * @param   function  cb  Main submit() callback
	 */
	check: function (cb) {
		var values = Object.values(this.results);
		var allPassed = values.every(function (res) {
			return res === true;
		});
		if (allPassed) {
			clearInterval(this.checker);
			cb();
		}
		
		if (values.contains(false)) {
			clearInterval(this.checker);
		}
		
	}
});