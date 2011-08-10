 var FbCalc = new Class({
	 Extends: FbElement,
	initialize: function(element, options) {
		this.plugin = 'calc';
		this.parent(element, options);
	},
	
	attachedToForm : function() {
		if (this.options.ajax) {
			this.ajaxCalc = this.calc.bindWithEvent(this);
			var form = this.form;
			this.options.observe.each(function(o){
				if (this.form.formElements[o]) {
					this.form.formElements[o].addNewEvent('change', this.ajaxCalc);
				}
			}.bind(this));
		}
	},
	
		calc	: function(){
		this.element.getParent().getElement('.loader').setStyle('display', '');
		var url = Fabrik.liveSite + 'index.php?option=com_fabrik&format=raw&view=plugin&task=pluginAjax&plugin=calc&method=ajax_calc&element_id=' + this.options.id;
		var formdata = this.form.getFormElementData();
		var data = Object.append(formdata, {'formid':this.form.id});
		var myAjax = new Request({'url':url, method:'post', 'data': data,
		onComplete: function(r){
			this.element.getParent().getElement('.loader').setStyle('display', 'none');
			this.update(r);
		}.bind(this)}).send();
	}
});