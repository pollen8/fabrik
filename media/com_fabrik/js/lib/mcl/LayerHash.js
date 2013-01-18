/*
---
script: LayerHash.js
description: LayerHash, manages layers.
license: MIT-style
authors:
 - Martin Tillmann
requires:
  core/1.2.4: '*'
provides: [LayerHash]
...
*/

var LayerHash = new Class({
	Implements : [Events,Options],
	options : {
		onAdd : function () {},
		onRemove : function () {},
		onSwap : function () {},
		onPromote : function () {},
		onDemote : function () {},
		onDraw : function () {}
	},
	tables : {
		pos : [],
		id : {}
	},
	length : 0,
	layers : [],
	initialize : function(options){
		this.setOptions(options);
		return this;
	},
	add : function(layer)
	{
		var at = this.layers.length;
		return this.addAt(layer, at);
	},
	addAt : function(layer, pos)
	{
		layer = layer.options?layer:new Layer(layer);
		if(typeOf(this.tables.id[layer.options.id]) == 'number')
		{
			throw new Error('LayerHash.addAt: Layer-ID can only be used once: ``'+layer.options.id+'´´');
		}
		
		var tmp = this.layers.splice(
			pos,
			this.layers.length - pos,
			layer
		);
	
		this.layers = this.layers.concat(tmp);
		this.rebuildTables();
		this.fireEvent('add');
		return this.getAt( pos );
	},
	
	rebuildTables : function()
	{
		this.tables = { pos : [], id : {} };
		for(var i = 0, lyr; lyr = this.layers[i]; i++)
		{
			id = lyr.options.id;
			this.tables.pos.push(id);
			this.tables.id[id] = i;
		}	
		this.length = this.layers.length;
	},
	
	addAfter : function(layer, siblingId)
	{
		return this.addAt(layer,this.tables.id[siblingId] + 1);
	},
	
	addBefore : function(layer, siblingId)
	{
		return this.addAt(layer,this.tables.id[siblingId]);
	},
	
	replace : function(layer, replacee)
	{
		var pos = this.tables.id[replacee];
		this.remove(replacee);
		return this.addAt(layer,pos);
	},
	
	removeAt : function(pos)
	{
		this.remove( this.tables.pos[pos] );
		return this;
	},
	
	remove : function(id)
	{
		this.layers.splice( this.tables.id[ id ],1 );
		this.rebuildTables();
		this.fireEvent('remove');
		return this;
	},
	promote : function(id)
	{
		var from = this.tables.id[ id ];
		var to = from + 1;
		this.fireEvent('promote');
		return this.swapByPos(from,to);
	},	
	demote : function(id)
	{
		var from = this.tables.id[ id ];
		var to = from - 1;
		this.fireEvent('demote');
		return this.swapByPos(from,to);		
	},
	
	swapByPos : function(from,to)
	{
		var fromItem = this.layers[ to ];
		var toItem = this.layers[ from ];
		if(fromItem && toItem)
		{
			this.layers[from] = fromItem;
			this.layers[to] = toItem;
			this.rebuildTables();	
			return toItem;
		}
		return false;
	},
	swap : function(from, to){
		this.swapByPos(
			this.tables.id[ from ],
			this.tables.id[ to ]
		);
		
		this.fireEvent('swap');
		return this.get( from );
	},
	
	getByPos : function( pos )
	{
		return this.layers[ pos ];
	},
	getAt : function( pos )
	{
		return this.getByPos( pos );
	},
	get : function( id )
	{
		return this.layers[ this.tables.id[ id ] ];
	},
	draw : function( id )
	{
		if(!id){
			for(var i = 0, layer; layer = this.layers[ i ]; i++)
			{
				this.drawLayer(layer);
			}
		}
		else
		{
			this.drawLayer( id );
		}
		this.fireEvent('draw');
		return this;
	},
	
	drawLayer : function(layer){
		if(layer.options.visible)
		layer.draw();
	},
	
	getOrder : function()
	{
		return this.tables.pos;
	},
	
	setOrder : function(newOrder)
	{
		var tmp = [];
		for(var i = 0, layerId; layerId = newOrder[i]; i++)
		{
			tmp.push(
				this.layers[ this.tables.id[ layerId ] ]
			);
		}
		this.layers = tmp;
		this.rebuildTables();
		return this;		
	}
});
