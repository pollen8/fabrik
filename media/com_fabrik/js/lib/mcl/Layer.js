/*
---
script: Layer.js
description: Layer, holds items.
license: MIT-style
authors:
 - Martin Tillmann
requires:
  core/1.2.4: '*'
provides: [Layer]
...
*/
var Layer = new Class({
	
	Implements : [Options, Events],
	options : {
		visible : true
	},	
	initialize : function(options)
	{
		if(!options.id)
		{
			throw new Error("Layer.initialize: options.id must not be blank!");
		}
		this.items = new Hash();
		this.setOptions(options);
		return this;
	},
	add : function(item)
	{
		item = item.options?item:new CanvasItem(item);
		item.fullid = this.options.id+"/"+item.id;
		this.items.set(item.id, item);
		return item;
	},
	get : function(id)
	{
		return this.items.get(id);
	},	
	remove : function(id)
	{
		this.items.get('id').fireEvent('destroy');
		this.items.erase('id');
	},
	toggle : function()
	{
		this.options.visible = !this.options.visible;
		return this;
	},	
	draw : function()
	{
		if(!this.options.visible)return false;
		this.items.each(function(item){
			item.draw()
		});
		return this;
	},
	promote : function()
	{
		return CANVAS.layers.promote( this.options.id );
	},
	demote : function()
	{
		return CANVAS.layers.demote( this.options.id );
	},
	swap : function( targetId )
	{
		return CANVAS.layers.swap( this.options.id, targetId );
	}
});
