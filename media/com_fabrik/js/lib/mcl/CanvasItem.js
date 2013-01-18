/*
---
script: CanvasItem.js
description: CanvasItem, item that is drawn.
license: MIT-style
authors:
 - Martin Tillmann
requires:
  core/1.2.4: '*'
provides: [CanvasItem]
...
*/
var CanvasItem = new Class({
	
	Implements : [Options,Events],
	
	options : {
		onDraw :  function () {},
		onDestroy : function () {}
	},
	
	dims : null,
	
	initialize : function(options){

		if(!options.id)
		{
			throw new Error("CanvasItem.initialize: options.id must not be blank!");
		}

		if(options.dims)
		{
			throw new Error("CanvasItem.initialize: options.dims must not be used, interactivity and your code may break.");			
		}

		for(var i in options){
			if(!['events'].contains(i))this[i] = options[i];
		}
		
		this.setOptions(options.events);
	},
	
	setDims : function(){
		if(arguments.length == 4)
		{
			this.dims = arguments;
		}
		else if(arguments.length == 1)
		{
			this.dims = arguments[0];
		}
		else
		{
			//attempt to find the values
			var x,y,w,h;
			if(!(x = $pick(this.x,this.left)))return false;
			if(!(y = $pick(this.y,this.top)))return false;
			if(!(w = $pick(this.w,this.width)))return false;
			if(!(h = $pick(this.h,this.height)))return false;
			this.dims = [x,y,w,h];
		}
		
	},
	
	getLayer : function()
	{
		return CAVNAS.layerFromPath( this.fullid );
	},
	
	draw : function(ctx){
		this.fireEvent('draw');
	},
	
	destroy : function()
	{
			this.fireEvent('destroy');		
	}	
});
