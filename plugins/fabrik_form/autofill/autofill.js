/**
 * @author Robert
 */
var Autofill =  new Class({
	
	getOptions:function(){
		return {
			'observe':''
		};
	},
	
	initialize: function(options, lang) {
		this.setOptions(this.getOptions(), options);
		this.lang = Object.extend({
			'norecordsfound':'no records found'
		}, arguments[1] || {});
	
		head.ready(function() {
			(function(){this.setUp()}.bind(this)).delay(1000);
		}.bind(this))
	},
	
	setUp: function()
	{
		try{
  		this.form = eval('form_' + this.options.formid);
  	}catch(err){
			//form_x not found (detailed view perhaps)
  		return;
  	}
		var evnt = this.lookUp.bind(this);
		this.element = this.form.formElements.get(this.options.observe);
		this.form.dispatchEvent('', this.options.observe, 'blur', evnt);
	},
	
	// perform ajax lookup when the observer element is blurred
	
	lookUp: function(){
		
		if(!confirm(Joomla.JText._('PLG_FORM_AUTOFILL_DO_UPDATE'))){
			return;
		}
		Fabrik.loader.start(Joomla.JText._('PLG_FORM_AUTOFILL_SEARCHING'), 'form_' + this.options.formid);
		
		var v = this.element.get('value');
		var formid = this.options.formid;
		var observe = this.options.observe;
		var url = Fabrik.liveSite + 'index.php?option=com_fabrik&format=raw&view=plugin&task=pluginAjax';
		
		var myAjax = new Request({url:url, method:'post', 
		'data':{
			'plugin':'fabrikautofill',
			'method':'ajax_getAutoFill',
			'g':'form',
			'v':v, 
			'formid':formid,
			'observe':observe
			},
		onComplete: function(json){
			Fabrik.loader.stop();
			this.updateForm(json);
		}.bind(this)}).send();
	},
	
	//update the form from the ajax request returned data
	updateForm:function(json){
		json = $H(JSON.decode(json));
		if(json.length == 0){
			alert(Joomla.JText._('PLG_FORM_AUTOFILL_NORECORDS_FOUND'));
		}
		json.each(function(val, key){
			var k2 = key.substr(-4);
			if(k2 == '_raw'){
				key = key.replace('_raw', '');
				var el = this.form.formElements.get(key);
				if (typeOf(el) !== 'null') {
					el.update(val);
				}
			}
		}.bind(this));
	}
	
});

Autofill.implement(new Events);
Autofill.implement(new Options);