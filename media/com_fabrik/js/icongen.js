/*jshint mootools: true */
/*global Fabrik:true, fconsole:true, Joomla:true, CloneObject:true, $A:true, $H:true,unescape:true,ART:true */

var IconGenerator = new Class({
	
	Implements: [Options],
	
	options: {
		size: {width: 32, height: 32},
		rotate: 0,
		scale: 1,
		shadow: {
			color: '#fff',
			translate: {x: 0, y: 1}
		},
		fill: {
			color: ['#C92804', '#9E1E04']
		}
	},

	initialize: function (options) {
		this.setOptions(options);
	},
	
	create: function (icon, options) {
		if (typeOf(options) !== 'object') {
			options = {};
		}
		var opts = Object.clone(this.options);
		Object.append(opts, options);

		var art = new ART(opts.size.width * opts.scale, opts.size.height * opts.scale);
		var group = new ART.Group();
	  
	  // cache the path
		var iconPath = new ART.Path(icon);
	  
	  // create the white shadow
		var iconShadow = new ART.Shape(iconPath);
		iconShadow.scale(opts.scale, opts.scale);

		iconShadow.fill(opts.shadow.color);
		iconShadow.translate(opts.shadow.translate.x, opts.shadow.translate.y);
		
		// create an icon with the gradient
		icon = new ART.Shape(iconPath);
	  
		icon.scale(opts.scale, opts.scale);
	 
		icon.fill(opts.fill.color[0], opts.fill.color[1]);
		
		//test stroke (border)
		if (options.stroke) {
			icon.stroke(options.stroke.color, options.stroke.width);
		}
		
		group.grab(iconShadow, icon);
		group.rotate(opts.rotate, 16 * opts.scale, 16 * opts.scale);
		group.inject(art);
		return art;
	}
});