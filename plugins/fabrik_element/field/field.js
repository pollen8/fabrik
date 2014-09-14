/**
 * Field Element
 *
 * @copyright: Copyright (C) 2005-2013, fabrikar.com - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

var FbField = new Class({
	Extends: FbElement,
	initialize: function (element, options) {
		this.plugin = 'fabrikfield';
		this.parent(element, options);
		/*
		 * $$$ hugh - testing new masking feature, uses this jQuery widget:
		 * http://digitalbush.com/projects/masked-input-plugin/
		 */
		if (this.options.use_input_mask) {
			jQuery('#' + element).mask(this.options.input_mask);
		}
	},

	select: function () {
		var element = this.getElement();
		if (element) {
			this.getElement().select();
		}
	},

	focus: function () {
		var element = this.getElement();
		if (element) {
			this.getElement().focus();
		}
	}
});