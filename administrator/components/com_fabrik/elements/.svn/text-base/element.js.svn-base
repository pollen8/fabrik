var elementElement = new Class({
	
	initialize: function(el){
		this.el = el;
		this.options = Object.extend({
			'plugin':'chart',
			'excludejoined':0
		}, arguments[1] || {});
		//this.updateMeEvent = this.updateMe.bindWithEvent(this);
		//if loading in a form plugin then the connect is not yet avaiable in the dom
		if(typeOf($(this.options.conn)) === false){
			this.cnnperiodical = this.getCnn.periodical(500, this);
		}else{
			this.setUp();
		}
	},
	
	getCnn:function(){
		if(typeOf($(this.options.conn)) === false){
			return;
		}
		this.setUp();
		clearInterval(this.cnnperiodical);
	},
	
	setUp:function(){
		this.el = $(this.el);
		Fabrik.model.fields.fabriktable[this.options.table].registerElement(this);
	},
	
	getOpts:function(){
		return $H({
			'calcs':this.options.include_calculations,
			'showintable':this.options.showintable,
			'published':this.options.published,
			'excludejoined':this.options.excludejoined
		});
	},
	
	//only called from repeat viz admin interface i think
	cloned:function(newid, counter)
	{
		this.el = newid;
		var t = this.options.table.split('-');
		t.pop();
		this.options.table = t.join('-') + '-' + counter;
		this.setUp();
	}
});