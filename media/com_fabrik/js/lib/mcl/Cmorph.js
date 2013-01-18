/*
---
script: Cmorph.js
description: Cmorph, generic Morph object.
license: MIT-style
authors:
 - Martin Tillmann
requires:
  core/1.2.4: '*'
provides: [Cmorph]
...
*/
var Cmorph = new Class({
	
	Extends : Fx,
	item : null,
	properties : null,
	
	initialize : function(xitem, options)
	{
		this.parent(options);
		this.item = xitem;
		
		this.properties = {};
		return this;
	},
	
	morph : function(properties)
	{
		var v;
		for(var prop in properties)
		{
			v = properties[prop];
			if(typeOf(v) != 'array') v = [this.item[prop], v];
			this.properties[prop] = [v[0],v[1],v[1] - v[0]];
		}
		
		this.start(0,1);
		return this;
	},
	
	set : function(now)
	{
		for(var prop in this.properties)
		{
			this.item[prop] = this.properties[prop][0] + this.properties[prop][2] * now;			
		}
	}
	
	
});
