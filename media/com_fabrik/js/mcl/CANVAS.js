/*
---
script: CANVAS.js
description: CANVAS, static object.
license: MIT-style
authors:
 - Martin Tillmann
requires:
  core/1.2.4: '*'
provides: [CANVAS]
...
*/
(function(){
	var CANVAS = this.CANVAS = {
		
		layers : [],
		ctx : null,
		ctxEl : null,
		lastMouseOverTarget : null,
		dragTarget : null,
		threads : null,
		ctxPos : null,
		cacheCtxPos : true,
		
		init : function( options ){
			if(options.canvasElement) this.setCtx( options.canvasElement );
			this.layers = new LayerHash();
			
			if(options.enableMouse)this.setupMouse();
			if(options.cacheCtxPos)this.cacheCtxPos = this.options.cacheCtxPos;
			
			this.threads = new Hash();
			
			this.ctxPos = $(this.ctxEl).getPosition();
		
			return this;
		},
		
		setDrag : function( item )
		{
			this.dragTarget = item.fullid;
		},
		
		clearDrag : function(  )
		{
			this.dragTarget = null;
		},

		getMouse : function(e){
			var ctxPos = this.cacheCtxPos ? this.ctxPos : $(this.ctxEl).getPosition();
			return [
				e.event.pageX - ctxPos.x,
				e.event.pageY - ctxPos.y
			];
		},
		
		setupMouse : function(){
			$(this.ctxEl).addEvents({	
				click : function(e)
				{
					var p = CANVAS.getMouse(e);
					if(item = CANVAS.findTarget(p))
					{
						item.fireEvent('click',p);
					}
				},
				mousedown : function(e)
				{
					var p = CANVAS.getMouse(e);
					if(CANVAS.dragTarget)
					{
						CANVAS.fromPath(CANVAS.dragTarget).fireEvent('mousedown',p);
						return;
					}
					if(item = CANVAS.findTarget(p))
					{
						item.fireEvent('mousedown',p);
					}
				},

				mouseup : function(e)
				{
					var p = CANVAS.getMouse(e);
					if(CANVAS.dragTarget)
					{
						CANVAS.fromPath(CANVAS.dragTarget).fireEvent('mouseup',p);
						return;
					}

					if(item = CANVAS.findTarget(p))
					{
						item.fireEvent('mouseup',p);
					}
				},
				
				mousemove : function(e)
				{
					var p = CANVAS.getMouse(e);
					if(CANVAS.dragTarget)
					{
						CANVAS.fromPath(CANVAS.dragTarget).fireEvent('mousemove',p);
						return
					}
					
					if(item = CANVAS.findTarget(p))
					{
						if(item.fullid != CANVAS.lastMouseOverTarget)
						{
							item.fireEvent('mouseover',p);
							if(CANVAS.lastMouseOverTarget)
							{
								CANVAS.fromPath(CANVAS.lastMouseOverTarget).fireEvent('mouseout',p);
							}
							CANVAS.lastMouseOverTarget = item.fullid;
						}
						else
						{
							item.fireEvent('mousemove',p);
						}
					}
					else
					{
						if(CANVAS.lastMouseOverTarget)
						{
							CANVAS.fromPath(CANVAS.lastMouseOverTarget).fireEvent('mouseout',p);
							CANVAS.lastMouseOverTarget = null;
						}
					}
				},
				
				dblclick : function(e)
				{
					var p = CANVAS.getMouse(e);
					if(item = CANVAS.findTarget(p))
						item.fireEvent('dblclick',p);
				},
				
				mouseleave : function(e){
					var p = CANVAS.getMouse(e);
					
					if(CANVAS.dragTarget){
						CANVAS.fromPath(CANVAS.dragTarget).fireEvent('mouseup',p);
						CANVAS.dragTarget = null;
					}
					
					if(CANVAS.lastMouseOverTarget){
						CANVAS.fromPath(CANVAS.lastMouseOverTarget).fireEvent('mouseout',p);
						CANVAS.lastMouseOverTarget = null;
					}
				}
			});
		},
				
		setCtx : function( el ){
			this.ctxEl = el;
			this.ctx = $(el).getContext('2d');
		},
		
		getCtx : function(){
			return this.ctx;
		},
		
		contains : function(r,p){
			return p[0] >= r[0] && p[1] >= r[1] && p[0] <= r[0] + r[2] && p[1] <= r[1] + r[3]; 
		},
		
		fromPath : function( path )
		{
			path = path.split('/');
			return CANVAS.layers.get(path[0]).get(path[1]);				
		},
		
		layerFromPath : function( path )
		{
			path = path.split('/');
			return CANVAS.layers.get(path[0]);							
		},
		
		findTarget : function(p)
		{
			for(var i = CANVAS.layers.layers.length - 1, layer, items; layer = CANVAS.layers.layers[i]; i--)
			{
				var items = layer.items.getClean();
				for(var item in items)
				{
					if(items[item].interactive && items[item].dims)
					{
						if(CANVAS.contains(items[item].dims,p)){
							return items[item];
						}
					}
				}
			}
			return false;
		},

		addThread : function( thread )
		{
			if(!thread.options)thread = new Thread(thread);
			this.threads.set(
				thread.options.id,
				thread
			);
			return thread;
		},
		
		removeThread : function( id )
		{
				this.threads.get( id ).destroy();
				this.items.erase( id );		
		},
		
		clear : function(rect)
		{
			rect = rect || [
					0,
					0,
					$(this.ctxEl).get('width'),
					$(this.ctxEl).get('height')
				];
			this.ctx.clearRect(rect[0],rect[1],rect[2],rect[3]);
			return this;
		},
		
		draw : function()
		{
			this.layers.draw();
		}
	};
})();
