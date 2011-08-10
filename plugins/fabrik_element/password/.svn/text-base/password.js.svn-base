var FbPassword = new Class({
	Extends : FbElement,
	initialize : function(element, options) {
		this.parent(element, options);
		if (this.element) {
			this.element.addEvent('keyup', this.passwordChanged.bindWithEvent(this))
		}
		if (this.options.ajax_validation == true) {
			this.getConfirmationField().addEvent('blur', this.callvalidation.bindWithEvent(this));
		}

		if (this.getConfirmationField().get('value') == '') {
			this.getConfirmationField().value = this.element.value;
		}
	},

	callvalidation : function(e) {
		this.form.doElementValidation(e, false, '_check');
	},

	passwordChanged : function() {
		var strength = this.element.getParent().getElement('.strength');
		if (typeOf(strength) == 'null') {
			return;
		}
		var strongRegex = new RegExp("^(?=.{8,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$", "g");
		var mediumRegex = new RegExp("^(?=.{7,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
		var enoughRegex = new RegExp("(?=.{6,}).*", "g");
		var pwd = this.element;
		if (pwd.value.length == 0) {
			strength.innerHTML = Joomla.JText._('PLG_ELEMENT_PASSWORD_TYPE_PASSWORD');
		} else if (false == enoughRegex.test(pwd.value)) {
			strength.innerHTML = Joomla.JText._('PLG_ELEMENT_PASSWORD_MORE_CHARACTERS');
		} else if (strongRegex.test(pwd.value)) {
			strength.innerHTML = '<span style="color:green">' + Joomla.JText._('PLG_ELEMENT_PASSWORD_STRONG') + '</span>';
		} else if (mediumRegex.test(pwd.value)) {
			strength.innerHTML = '<span style="color:orange">' + Joomla.JText._('PLG_ELEMENT_PASSWORD_MEDIUM') + '</span>';
		} else {
			strength.innerHTML = '<span style="color:red">' + Joomla.JText._('PLG_ELEMENT_PASSWORD_WEAK') + '</span>';
		}
	},

	getConfirmationField : function() {
		var name = this.element.name + '_check';
		return this.element.findClassUp('fabrikElement').getElement('input[name=' + name + ']');
	}
});