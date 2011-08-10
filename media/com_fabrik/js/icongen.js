IconGenerator = new Class({
	
	Implements:[Options],
	
	options:{scale:1,
		shadow:{
			color:'#fff',
			translate:{x:0, y:1}
		},
		fill:{
			color:['#C92804', '#9E1E04']
		}},

	initialize: function(options){
		this.setOptions(options);
	},
	
	create:function(icon, options){
		if(typeOf(options) !== 'object'){
			options = {};
		}
		var opts = Object.clone(this.options);
		Object.append(opts, options);

	  var art = new ART(32 * opts.scale, 32 * opts.scale);
	  var group = new ART.Group;
	  
	  // cache the path
	  var iconPath = new ART.Path(icon);
	  
	  // create the white shadow
	  var iconShadow = new ART.Shape(iconPath);
	  iconShadow.scale(opts.scale, opts.scale);

	  iconShadow.fill(opts.shadow.color);
	  iconShadow.translate(opts.shadow.translate.x, opts.shadow.translate.y);
	  
	  // create an icon with the gradient
	  var icon = new ART.Shape(iconPath);
	  
	  icon.scale(opts.scale, opts.scale);
	 
	  icon.fill(opts.fill.color[0], opts.fill.color[1]);
	  //icon.rotate(184);
	  group.grab(iconShadow, icon);    
	  
	  group.rotate(opts.rotate, 16 * opts.scale, 16 * opts.scale);
	  group.inject(art);
	  return art;

	}
});