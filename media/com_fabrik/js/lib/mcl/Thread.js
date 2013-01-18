/*
---
script: Thread.js
description: Thread, timer object.
license: MIT-style
authors:
 - Martin Tillmann
requires:
  core/1.2.4: '*'
provides: [Thread]
...
*/
var Thread = new Class({

	Implements : [Options,Events],
	
	options : {
		fps : (1000 / 31).round(), //31 = fps
		expires : -1,
		onExec : function () {},
		onExpire : function () {},
		onBeforeexpire : function () {},
		onDestroy : function () {},
		instant : true
	},
	timer : null,
	
	morphs : [],
	
	initialize : function(options){
	
		if(!options.id){
			throw new Error('Thread.initialize: options.id must not be blank!');
		}		
		if(options.fps)options.fps = (1000 / options.fps).round();
		this.setOptions(options);
		if(this.options.instant)this.start();
		return this;
	},
	
	start : function()
	{
		this.tick();
		this.timer = this.tick.periodical(this.options.fps,this);
		return this;
	},
	
	stop : function()
	{
		$clear(this.timer);
		return this;
	},
		
	tick : function()
	{
		this.fireEvent('exec');	
		if(this.options.expires > 0)this.options.expires--;
		
		//FIXME: why is this fired before exec?
		if(this.options.expires === 1)this.fireEvent('beforeexpire');
		
		if(this.options.expires === 0)
		{
			this.stop();
			this.fireEvent('expire');
		}
	},
	
	destroy : function()
	{
		this.stop();
		this.fireEvent('destroy');
	}
});
