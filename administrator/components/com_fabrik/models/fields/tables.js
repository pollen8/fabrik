var tablesElement = new Class({
	
	Implements: [Options, Events],
	
	options:{
		conn:null
	},
	
	initialize: function(el, options){
		this.el = el;
		this.setOptions(options);
		this.updateMeEvent = this.updateMe.bindWithEvent(this);
		//if loading in a form plugin then the connect is not yet avaiable in the dom
		if(typeOf($(this.options.conn)) === 'null') {
			this.periodical = this.getCnn.periodical(500, this);
		}else{
			this.setUp();
		}
	},
	
	cloned:function()
	{
		
	},
	
	getCnn:function(){
		if(typeOf($(this.options.conn)) === 'null') {
			return;
		}
		this.setUp();
		clearInterval(this.periodical);
	},
	
	setUp:function(){
		this.el = $(this.el);
		$(this.options.conn).addEvent('change', this.updateMeEvent);
		//see if there is a connection selected
		var v = $(this.options.conn).get('value');
		if(v != '' && v != -1){
			this.updateMe();
		}
	},
	
	updateMe: function(e){
		if(e){
			e.stop();
		}
		if($(this.el.id+'_loader')){
			$(this.el.id+'_loader').show();
		}
		var cid = $(this.options.conn).get('value');
		// $$$ rob 09/09/2011 changed to call admin page, seems better to not cross call between admin and front end for this
		//var url = this.options.livesite + 'index.php?option=com_fabrik&format=raw&view=plugin&task=pluginAjax&g=element&plugin=field&method=ajax_tables&cid=' + cid;
		var url = 'index.php?option=com_fabrik&format=raw&task=plugin.pluginAjax&g=element&plugin=field&method=ajax_tables&cid=' + cid;
		// $$$ hugh - changed this to 'get' method, because some servers barf (Length Required) if
		// we send it a POST with no postbody.
		var myAjax = new Request({url:url, method:'get', 
			onComplete: function(r){
				var opts = JSON.decode(r);
				if(typeOf(opts) !== 'null'){
					if(opts.err){
						alert(opts.err);
					}else{
						this.el.empty();
						opts.each( function(opt){
							//var o = {'value':opt.value};//wrong for calendar
							var o = {'value':opt};
							if(opt == this.options.value){
								o.selected = 'selected';
							}
							if($(this.el.id+'_loader')){
								$(this.el.id+'_loader').setStyle('display','none');
							}
							new Element('option', o).appendText(opt).inject(this.el);
						}.bind(this));
					}
				}
			}.bind(this),
			onFailure:function(r){
				if($(this.el.id+'_loader')){
					$(this.el.id+'_loader').hide();
				}
			}.bind(this)
		}).send();
	}
});