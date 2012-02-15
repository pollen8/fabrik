/*
---
name: Color
description: Class to create and manipulate colors. Includes HSB «-» RGB «-» HEX conversions. Supports alpha for each type.
requires: [Core/Type, Core/Array]
provides: Color
...
*/

(function(){

var colors = {
	maroon: '#800000', red: '#ff0000', orange: '#ffA500', yellow: '#ffff00', olive: '#808000',
	purple: '#800080', fuchsia: "#ff00ff", white: '#ffffff', lime: '#00ff00', green: '#008000',
	navy: '#000080', blue: '#0000ff', aqua: '#00ffff', teal: '#008080',
	black: '#000000', silver: '#c0c0c0', gray: '#808080'
};

var Color = this.Color = function(color, type){
	
	if (color.isColor){
		
		this.red = color.red;
		this.green = color.green;
		this.blue = color.blue;
		this.alpha = color.alpha;

	} else {
		
		var namedColor = colors[color];
		if (namedColor){
			color = namedColor;
			type = 'hex';
		}

		switch (typeof color){
			case 'string': if (!type) type = (type = color.match(/^rgb|^hsb/)) ? type[0] : 'hex'; break;
			case 'object': type = type || 'rgb'; color = color.toString(); break;
			case 'number': type = 'hex'; color = color.toString(16); break;
		}

		color = Color['parse' + type.toUpperCase()](color);
		this.red = color[0];
		this.green = color[1];
		this.blue = color[2];
		this.alpha = color[3];
	}
	
	this.isColor = true;

};

var limit = function(number, min, max){
	return Math.min(max, Math.max(min, number));
};

var listMatch = /([-.\d]+)\s*,\s*([-.\d]+)\s*,\s*([-.\d]+)\s*,?\s*([-.\d]*)/;
var hexMatch = /^#?([a-f0-9]{1,2})([a-f0-9]{1,2})([a-f0-9]{1,2})([a-f0-9]{0,2})$/i;

Color.parseRGB = function(color){
	return color.match(listMatch).slice(1).map(function(bit, i){
		return (i < 3) ? Math.round(((bit %= 256) < 0) ? bit + 256 : bit) : limit(((bit === '') ? 1 : Number(bit)), 0, 1);
	});
};
	
Color.parseHEX = function(color){
	if (color.length == 1) color = color + color + color;
	return color.match(hexMatch).slice(1).map(function(bit, i){
		if (i == 3) return (bit) ? parseInt(bit, 16) / 255 : 1;
		return parseInt((bit.length == 1) ? bit + bit : bit, 16);
	});
};
	
Color.parseHSB = function(color){
	var hsb = color.match(listMatch).slice(1).map(function(bit, i){
		if (i === 0) return Math.round(((bit %= 360) < 0) ? (bit + 360) : bit);
		else if (i < 3) return limit(Math.round(bit), 0, 100);
		else return limit(((bit === '') ? 1 : Number(bit)), 0, 1);
	});
	
	var a = hsb[3];
	var br = Math.round(hsb[2] / 100 * 255);
	if (hsb[1] == 0) return [br, br, br, a];
		
	var hue = hsb[0];
	var f = hue % 60;
	var p = Math.round((hsb[2] * (100 - hsb[1])) / 10000 * 255);
	var q = Math.round((hsb[2] * (6000 - hsb[1] * f)) / 600000 * 255);
	var t = Math.round((hsb[2] * (6000 - hsb[1] * (60 - f))) / 600000 * 255);

	switch (Math.floor(hue / 60)){
		case 0: return [br, t, p, a];
		case 1: return [q, br, p, a];
		case 2: return [p, br, t, a];
		case 3: return [p, q, br, a];
		case 4: return [t, p, br, a];
		default: return [br, p, q, a];
	}
};

var toString = function(type, array){
	if (array[3] != 1) type += 'a';
	else array.pop();
	return type + '(' + array.join(', ') + ')';
};

Color.prototype = {

	toHSB: function(array){
		var red = this.red, green = this.green, blue = this.blue, alpha = this.alpha;

		var max = Math.max(red, green, blue), min = Math.min(red, green, blue), delta = max - min;
		var hue = 0, saturation = (max != 0) ? delta / max : 0, brightness = max / 255;
		if (saturation){
			var rr = (max - red) / delta, gr = (max - green) / delta, br = (max - blue) / delta;
			hue = (red == max) ? br - gr : (green == max) ? 2 + rr - br : 4 + gr - rr;
			if ((hue /= 6) < 0) hue++;
		}

		var hsb = [Math.round(hue * 360), Math.round(saturation * 100), Math.round(brightness * 100), alpha];

		return (array) ? hsb : toString('hsb', hsb);
	},

	toHEX: function(array){

		var a = this.alpha;
		var alpha = ((a = Math.round((a * 255)).toString(16)).length == 1) ? a + a : a;
		
		var hex = [this.red, this.green, this.blue].map(function(bit){
			bit = bit.toString(16);
			return (bit.length == 1) ? '0' + bit : bit;
		});
		
		return (array) ? hex.concat(alpha) : '#' + hex.join('') + ((alpha == 'ff') ? '' : alpha);
	},
	
	toRGB: function(array){
		var rgb = [this.red, this.green, this.blue, this.alpha];
		return (array) ? rgb : toString('rgb', rgb);
	}

};

Color.prototype.toString = Color.prototype.toRGB;

Color.hex = function(hex){
	return new Color(hex, 'hex');
};

if (this.hex == null) this.hex = Color.hex;

Color.hsb = function(h, s, b, a){
	return new Color([h || 0, s || 0, b || 0, (a == null) ? 1 : a], 'hsb');
};

if (this.hsb == null) this.hsb = Color.hsb;

Color.rgb = function(r, g, b, a){
	return new Color([r || 0, g || 0, b || 0, (a == null) ? 1 : a], 'rgb');
};

if (this.rgb == null) this.rgb = Color.rgb;

if (this.Type) new Type('Color', Color);

})();


/*
---
name: Table
description: LUA-Style table implementation.
requires: [Core/Type, Core/Array]
provides: Table
...
*/

(function(){

var Table = this.Table = function(){

	this.length = 0;
	var keys = [], values = [];
	
	this.set = function(key, value){
		var index = keys.indexOf(key);
		if (index == -1){
			var length = keys.length;
			keys[length] = key;
			values[length] = value;
			this.length++;
		} else {
			values[index] = value;
		}
		return this;
	};

	this.get = function(key){
		var index = keys.indexOf(key);
		return (index == -1) ? null : values[index];
	};

	this.erase = function(key){
		var index = keys.indexOf(key);
		if (index != -1){
			this.length--;
			keys.splice(index, 1);
			return values.splice(index, 1)[0];
		}
		return null;
	};

	this.each = this.forEach = function(fn, bind){
		for (var i = 0, l = this.length; i < l; i++) fn.call(bind, keys[i], values[i], this);
	};
	
};

if (this.Type) new Type('Table', Table);

})();



/*
---
name: ART
description: "The heart of ART."
requires: [Core/Class, Color/Color, Table/Table]
provides: [ART, ART.Element, ART.Container]
...
*/

(function(){

this.ART = new Class;

ART.version = '0.87';
ART.build = '37db3609c6e0df4c215737df9c3c5851d1e9c30c';

ART.Element = new Class({
	
	/* dom */

	inject: function(element){
		if (element.element) element = element.element;
		element.appendChild(this.element);
		return this;
	},
	
	eject: function(){
		var element = this.element, parent = element.parentNode;
		if (parent) parent.removeChild(element);
		return this;
	},
	
	/* events */
	
	listen: function(type, fn){
		if (!this._events) this._events = {};
		
		if (typeof type != 'string'){ // listen type / fn with object
			for (var t in type) this.listen(t, type[t]);
		} else { // listen to one
			if (!this._events[type]) this._events[type] = new Table;
			var events = this._events[type];
			if (events.get(fn)) return this;
			var bound = fn.bind(this);
			events.set(fn, bound);
			var element = this.element;
			if (element.addEventListener) element.addEventListener(type, bound, false);
			else element.attachEvent('on' + type, bound);
		}

		return this;
	},
	
	ignore: function(type, fn){
		if (!this._events) return this;
		
		if (typeof type != 'string'){ // ignore type / fn with object
			for (var t in type) this.ignore(t, type[t]);
			return this;
		}
		
		var events = this._events[type];
		if (!events) return this;
		
		if (fn == null){ // ignore every of type
			events.each(function(fn, bound){
				this.ignore(type, fn);
			}, this);
		} else { // ignore one
			var bound = events.get(fn);
			if (!bound) return this;
			var element = this.element;
			if (element.removeEventListener) element.removeEventListener(type, bound, false);
			else element.detachEvent('on' + type, bound);
		}

		return this;
	}

});

ART.Container = new Class({

	grab: function(){
		for (var i = 0; i < arguments.length; i++) arguments[i].inject(this);
		return this;
	}

});

var UID = 0;

ART.uniqueID = function(){
	return (new Date().getTime() + (UID++)).toString(36);
};

Color.detach = function(color){
	color = new Color(color);
	return [Color.rgb(color.red, color.green, color.blue).toString(), color.alpha];
};

})();



/*
---
name: ART.Path
description: "Class to generate a valid SVG path using method calls."
authors: ["[Valerio Proietti](http://mad4milk.net)", "[Sebastian Markbåge](http://calyptus.eu/)"]
provides: ART.Path
requires: ART
...
*/

(function(){

/* private functions */

var parse = function(path){

	var parts = [], index = -1,
	    bits = path.match(/[a-df-z]|[\-+]?(?:[\d\.]e[\-+]?|[^\s\-+,a-z])+/ig);

	for (var i = 0, l = bits.length; i < l; i++){
		var bit = bits[i];
		if (bit.match(/^[a-z]/i)) parts[++index] = [bit];
		else parts[index].push(Number(bit));
	}
	
	return parts;

};

var circle = Math.PI * 2, north = circle / 2, west = north / 2, east = -west, south = 0;

var calculateArc = function(rx, ry, rotation, large, clockwise, x, y, tX, tY){
	var cx = x / 2, cy = y / 2,
		rxry = rx * rx * ry * ry, rycx = ry * ry * cx * cx, rxcy = rx * rx * cy * cy,
		a = rxry - rxcy - rycx;

	if (a < 0){
		a = Math.sqrt(1 - a / rxry);
		rx *= a; ry *= a;
	} else {
		a = Math.sqrt(a / (rxcy + rycx));
		if (large == clockwise) a = -a;
		cx += -a * y / 2 * rx / ry;
		cy +=  a * x / 2 * ry / rx;
	}

	var sa = Math.atan2(cx, -cy), ea = Math.atan2(-x + cx, y - cy);
	if (!+clockwise){ var t = sa; sa = ea; ea = t; }
	if (ea < sa) ea += circle;

	cx += tX; cy += tY;

	return {
		circle: [cx - rx, cy - ry, cx + rx, cy + ry],
		boundsX: [
			ea > circle + west || (sa < west && ea > west) ? cx - rx : tX,
			ea > circle + east || (sa < east && ea > east) ? cx + rx : tX
		],
		boundsY: [
			ea > north ? cy - ry : tY,
			ea > circle + south || (sa < south && ea > south) ? cy + ry : tY
		]
	};
};

var extrapolate = function(parts, precision){
	
	var boundsX = [], boundsY = [];
	
	var ux = (precision != null) ? function(x){
		boundsX.push(x); return Math.round(x * precision);
	} : function(x){
		boundsX.push(x); return x;
	}, uy = (precision != null) ? function(y){
		boundsY.push(y); return Math.round(y * precision);
	} : function(y){
		boundsY.push(y); return y;
	}, np = (precision != null) ? function(v){
		return Math.round(v * precision);
	} : function(v){
		return v;
	};

	var reflect = function(sx, sy, ex, ey){
		return [ex * 2 - sx, ey * 2 - sy];
	};
	
	var X = 0, Y = 0, px = 0, py = 0, r;
	
	var path = '', inX, inY;
	
	for (i = 0; i < parts.length; i++){
		var v = Array.slice(parts[i]), f = v.shift(), l = f.toLowerCase();
		var refX = l == f ? X : 0, refY = l == f ? Y : 0;
		
		if (l != 'm' && inX == null){
			inX = X; inY = Y;
		}

		switch (l){
			
			case 'm':
				path += 'm' + ux(X = refX + v[0]) + ',' + uy(Y = refY + v[1]);
			break;
			
			case 'l':
				path += 'l' + ux(X = refX + v[0]) + ',' + uy(Y = refY + v[1]);
			break;
			
			case 'c':
				px = refX + v[2]; py = refY + v[3];
				path += 'c' + ux(refX + v[0]) + ',' + uy(refY + v[1]) + ',' + ux(px) + ',' + uy(py) + ',' + ux(X = refX + v[4]) + ',' + uy(Y = refY + v[5]);
			break;

			case 's':
				r = reflect(px, py, X, Y);
				px = refX + v[0]; py = refY + v[1];
				path += 'c' + ux(r[0]) + ',' + uy(r[1]) + ',' + ux(px) + ',' + uy(py) + ',' + ux(X = refX + v[2]) + ',' + uy(Y = refY + v[3]);
			break;
			
			case 'q':
				px = refX + v[0]; py = refY + v[1];
				path += 'c' + ux(refX + v[0]) + ',' + uy(refY + v[1]) + ',' + ux(px) + ',' + uy(py) + ',' + ux(X = refX + v[2]) + ',' + uy(Y = refY + v[3]);
			break;
			
			case 't':
				r = reflect(px, py, X, Y);
				px = refX + r[0]; py = refY + r[1];
				path += 'c' + ux(px) + ',' + uy(py) + ',' + ux(px) + ',' + uy(py) + ',' + ux(X = refX + v[0]) + ',' + uy(Y = refY + v[1]);
			break;

			case 'a':
				px = refX + v[5]; py = refY + v[6];

				if (!+v[0] || !+v[1] || (px == X && py == Y)){
					path += 'l' + ux(X = px) + ',' + uy(Y = py);
					break;
				}
				
				v[7] = X; v[8] = Y;
				r = calculateArc.apply(null, v);

				boundsX.push.apply(boundsX, r.boundsX);
				boundsY.push.apply(boundsY, r.boundsY);

				path += (v[4] == 1 ? 'wa' : 'at') + r.circle.map(np) + ',' + ux(X) + ',' + uy(Y) + ',' + ux(X = px) + ',' + uy(Y = py);
			break;

			case 'h':
				path += 'l' + ux(X = refX + v[0]) + ',' + uy(Y);
			break;
			
			case 'v':
				path += 'l' + ux(X) + ',' + uy(Y = refY + v[0]);
			break;
			
			case 'z':
				path += 'x';
				if (inX != null){
					path += 'm' + ux(X = inX) + ',' + uy(Y = inY);
					inX = null;
				}
			break;
			
		}
	}
	
	var right = Math.max.apply(Math, boundsX),
		bottom = Math.max.apply(Math, boundsY),
		left = Math.min.apply(Math, boundsX),
		top = Math.min.apply(Math, boundsY),
		height = bottom - top,
		width = right - left;
	
	return [path, {left: left, top: top, right: right, bottom: bottom, width: width, height: height}];

};

/* Path Class */

ART.Path = new Class({
	
	initialize: function(path){
		if (path instanceof ART.Path){ //already a path, copying
			this.path = Array.slice(path.path);
			this.box = path.box;
			this.vml = path.vml;
			this.svg = path.svg;
		} else {
			this.path = (path == null) ? [] : parse(path);
			this.box = null;
			this.vml = null;
			this.svg = null;
		}

		return this;
	},
	
	push: function(){ //modifying the current path resets the memoized values.
		this.box = null;
		this.vml = null;
		this.svg = null;
		this.path.push(Array.slice(arguments));
		return this;
	},
	
	reset: function(){
		this.box = null;
		this.vml = null;
		this.svg = null;
		this.path = [];
		return this;
	},
	
	/*utility*/
	
	move: function(x, y){
		return this.push('m', x, y);
	},
	
	line: function(x, y){
		return this.push('l', x, y);
	},
	
	close: function(){
		return this.push('z');
	},
	
	bezier: function(c1x, c1y, c2x, c2y, ex, ey){
		return this.push('c', c1x, c1y, c2x, c2y, ex, ey);
	},
	
	arc: function(x, y, rx, ry, large){
		return this.push('a', Math.abs(rx || x), Math.abs(ry || rx || y), 0, large ? 1 : 0, 1, x, y);
	},
	
	counterArc: function(x, y, rx, ry, large){
		return this.push('a', Math.abs(rx || x), Math.abs(ry || rx || y), 0, large ? 1 : 0, 0, x, y);
	},
	
	/* transformation, measurement */
	
	toSVG: function(){
		if (this.svg == null){
			var path = '';
			for (var i = 0, l = this.path.length; i < l; i++) path += this.path[i].join(' ');
			this.svg = path;
		}
		return this.svg;
	},
	
	toVML: function(precision){
		if (this.vml == null){
			var data = extrapolate(this.path, precision);
			this.box = data[1];
			this.vml = data[0];
		}
		return this.vml;
	},
	
	measure: function(precision){
		if (this.box == null){
					
			if (this.path.length){
				var data = extrapolate(this.path, precision);
				this.box = data[1];
				this.vml = data[2];
			} else {
				this.box = {left: 0, top: 0, right: 0, bottom: 0, width: 0, height: 0};
				this.vml = '';
				this.svg = '';
			}
		
		}
		
		return this.box;
	}
	
});

ART.Path.prototype.toString = ART.Path.prototype.toSVG;

})();


/*
---
name: ART.SVG
description: "SVG implementation for ART"
provides: [ART.SVG, ART.SVG.Group, ART.SVG.Shape, ART.SVG.Image]
requires: [ART, ART.Element, ART.Container, ART.Path]
...
*/

(function(){
	
var NS = 'http://www.w3.org/2000/svg', XLINK = 'http://www.w3.org/1999/xlink', UID = 0, createElement = function(tag){
	return document.createElementNS(NS, tag);
};

// SVG Base Class

ART.SVG = new Class({

	Extends: ART.Element,
	Implements: ART.Container,

	initialize: function(width, height){
		var element = this.element = createElement('svg');
		element.setAttribute('xmlns', NS);
		element.setAttribute('version', 1.1);
		var defs = this.defs = createElement('defs');
		element.appendChild(defs);
		if (width != null && height != null) this.resize(width, height);
	},

	resize: function(width, height){
		var element = this.element;
		element.setAttribute('width', width);
		element.setAttribute('height', height);
		return this;
	},
	
	toElement: function(){
		return this.element;
	}

});

// SVG Element Class

ART.SVG.Element = new Class({
	
	Extends: ART.Element,

	initialize: function(tag){
		this.uid = ART.uniqueID();
		var element = this.element = createElement(tag);
		element.setAttribute('id', 'e' + this.uid);
		this.transform = {translate: [0, 0], rotate: [0, 0, 0], scale: [1, 1]};
	},
	
	/* transforms */
	
	_writeTransform: function(){
		var transforms = [];
		for (var transform in this.transform) transforms.push(transform + '(' + this.transform[transform].join(',') + ')');
		this.element.setAttribute('transform', transforms.join(' '));
	},
	
	rotate: function(deg, x, y){
		if (x == null || y == null){
			var box = this.measure();
			x = box.left + box.width / 2; y = box.top + box.height / 2;
		}
		this.transform.rotate = [deg, x, y];
		this._writeTransform();
		return this;
	},

	scale: function(x, y){
		if (y == null) y = x;
		this.transform.scale = [x, y];
		this._writeTransform();
		return this;
	},

	translate: function(x, y){
		this.transform.translate = [x, y];
		this._writeTransform();
		return this;
	},
	
	setOpacity: function(opacity){
		this.element.setAttribute('opacity', opacity);
		return this;
	},
	
	// visibility
	
	hide: function(){
		this.element.setAttribute('display', 'none');
		return this;
	},
	
	show: function(){
		this.element.setAttribute('display', '');
		return this;
	}
	
});

// SVG Group Class

ART.SVG.Group = new Class({
	
	Extends: ART.SVG.Element,
	Implements: ART.Container,
	
	initialize: function(){
		this.parent('g');
		this.defs = createElement('defs');
		this.element.appendChild(this.defs);
		this.children = [];
	},
	
	measure: function(){
		return ART.Path.measure(this.children.map(function(child){
			return child.currentPath;
		}));
	}
	
});

// SVG Base Shape Class

ART.SVG.Base = new Class({
	
	Extends: ART.SVG.Element,

	initialize: function(tag){
		this.parent(tag);
		this.element.setAttribute('fill-rule', 'evenodd');
		this.fill();
		this.stroke();
	},
	
	/* insertions */
	
	inject: function(container){
		this.eject();
		if (container instanceof ART.SVG.Group) container.children.push(this);
		this.container = container;
		this._injectGradient('fill');
		this._injectGradient('stroke');
		this.parent(container);
		return this;
	},
	
	eject: function(){
		if (this.container){
			if (this.container instanceof ART.SVG.Group) this.container.children.erase(this);
			this.parent();
			this._ejectGradient('fill');
			this._ejectGradient('stroke');
			this.container = null;
		}
		return this;
	},
	
	_injectGradient: function(type){
		if (!this.container) return;
		var gradient = this[type + 'Gradient'];
		if (gradient) this.container.defs.appendChild(gradient);
	},
	
	_ejectGradient: function(type){
		if (!this.container) return;
		var gradient = this[type + 'Gradient'];
		if (gradient) this.container.defs.removeChild(gradient);
	},
	
	/* styles */
	
	_createGradient: function(type, style, stops){
		this._ejectGradient(type);

		var gradient = createElement(style + 'Gradient');

		this[type + 'Gradient'] = gradient;

		var addColor = function(offset, color){
			color = Color.detach(color);
			var stop = createElement('stop');
			stop.setAttribute('offset', offset);
			stop.setAttribute('stop-color', color[0]);
			stop.setAttribute('stop-opacity', color[1]);
			gradient.appendChild(stop);
		};

		// Enumerate stops, assumes offsets are enumerated in order
		// TODO: Sort. Chrome doesn't always enumerate in expected order but requires stops to be specified in order.
		if ('length' in stops) for (var i = 0, l = stops.length - 1; i <= l; i++) addColor(i / l, stops[i]);
		else for (var offset in stops) addColor(offset, stops[offset]);

		var id = 'g' + ART.uniqueID();
		gradient.setAttribute('id', id);

		this._injectGradient(type);

		this.element.removeAttribute('fill-opacity');
		this.element.setAttribute(type, 'url(#' + id + ')');
		
		return gradient;
	},
	
	_setColor: function(type, color){
		this._ejectGradient(type);
		this[type + 'Gradient'] = null;
		var element = this.element;
		if (color == null){
			element.setAttribute(type, 'none');
			element.removeAttribute(type + '-opacity');
		} else {
			color = Color.detach(color);
			element.setAttribute(type, color[0]);
			element.setAttribute(type + '-opacity', color[1]);
		}
	},

	fill: function(color){
		if (arguments.length > 1) this.fillLinear(arguments);
		else this._setColor('fill', color);
		return this;
	},

	fillRadial: function(stops, focusX, focusY, radius, centerX, centerY){
		var gradient = this._createGradient('fill', 'radial', stops);

		if (focusX != null) gradient.setAttribute('fx', focusX);
		if (focusY != null) gradient.setAttribute('fy', focusY);

		if (radius) gradient.setAttribute('r', radius);

		if (centerX == null) centerX = focusX;
		if (centerY == null) centerY = focusY;

		if (centerX != null) gradient.setAttribute('cx', centerX);
		if (centerY != null) gradient.setAttribute('cy', centerY);

		gradient.setAttribute('spreadMethod', 'reflect'); // Closer to the VML gradient
		
		return this;
	},

	fillLinear: function(stops, angle){
		var gradient = this._createGradient('fill', 'linear', stops);

		angle = ((angle == null) ? 270 : angle) * Math.PI / 180;

		var x = Math.cos(angle), y = -Math.sin(angle),
			l = (Math.abs(x) + Math.abs(y)) / 2;

		x *= l; y *= l;

		gradient.setAttribute('x1', 0.5 - x);
		gradient.setAttribute('x2', 0.5 + x);
		gradient.setAttribute('y1', 0.5 - y);
		gradient.setAttribute('y2', 0.5 + y);

		return this;
	},

	stroke: function(color, width, cap, join){
		var element = this.element;
		element.setAttribute('stroke-width', (width != null) ? width : 1);
		element.setAttribute('stroke-linecap', (cap != null) ? cap : 'round');
		element.setAttribute('stroke-linejoin', (join != null) ? join : 'round');

		this._setColor('stroke', color);
		return this;
	}
	
});

// SVG Shape Class

ART.SVG.Shape = new Class({
	
	Extends: ART.SVG.Base,
	
	initialize: function(path){
		this.parent('path');
		if (path != null) this.draw(path);
	},
	
	getPath: function(){
		return this.currentPath || new ART.Path;
	},
	
	draw: function(path){
		this.currentPath = (path instanceof ART.Path) ? path : new ART.Path(path);
		this.element.setAttribute('d', this.currentPath.toSVG());
		return this;
	},
	
	measure: function(){
		return this.getPath().measure();
	}

});

ART.SVG.Image = new Class({
	
	Extends: ART.SVG.Base,
	
	initialize: function(src, width, height){
		this.parent('image');
		if (arguments.length == 3) this.draw.apply(this, arguments);
	},
	
	draw: function(src, width, height){
		var element = this.element;
		element.setAttributeNS(XLINK, 'href', src);
		element.setAttribute('width', width);
		element.setAttribute('height', height);
		return this;
	}
	
});

})();


/*
---
name: ART.VML
description: "VML implementation for ART"
authors: ["[Simo Kinnunen](http://twitter.com/sorccu)", "[Valerio Proietti](http://mad4milk.net)", "[Sebastian Markbåge](http://calyptus.eu/)"]
provides: [ART.VML, ART.VML.Group, ART.VML.Shape]
requires: [ART, ART.Element, ART.Container, ART.Path]
...
*/

(function(){

var precision = 100, UID = 0;

// VML Base Class

ART.VML = new Class({

	Extends: ART.Element,
	Implements: ART.Container,
	
	initialize: function(width, height){
		this.vml = document.createElement('vml');
		this.element = document.createElement('av:group');
		this.vml.appendChild(this.element);
		this.children = [];
		if (width != null && height != null) this.resize(width, height);
	},
	
	inject: function(element){
		if (element.element) element = element.element;
		element.appendChild(this.vml);
	},
	
	resize: function(width, height){
		this.width = width;
		this.height = height;
		var style = this.vml.style;
		style.pixelWidth = width;
		style.pixelHeight = height;
		
		style = this.element.style;
		style.width = width;
		style.height = height;
		
		var halfPixel = (0.5 * precision);
		
		this.element.coordorigin = halfPixel + ',' + halfPixel;
		this.element.coordsize = (width * precision) + ',' + (height * precision);

		this.children.each(function(child){
			child._transform();
		});
		
		return this;
	},
	
	toElement: function(){
		return this.vml;
	}
	
});

// VML Initialization

var VMLCSS = 'behavior:url(#default#VML);display:inline-block;position:absolute;left:0px;top:0px;';

var styleSheet, styledTags = {}, styleTag = function(tag){
	if (styleSheet) styledTags[tag] = styleSheet.addRule('av\\:' + tag, VMLCSS);
};

ART.VML.init = function(document){

	var namespaces = document.namespaces;
	if (!namespaces) return false;

	namespaces.add('av', 'urn:schemas-microsoft-com:vml');
	namespaces.add('ao', 'urn:schemas-microsoft-com:office:office');

	styleSheet = document.createStyleSheet();
	styleSheet.addRule('vml', 'display:inline-block;position:relative;overflow:hidden;');
	styleTag('fill');
	styleTag('stroke');
	styleTag('path');
	styleTag('group');

	return true;

};

// VML Element Class

ART.VML.Element = new Class({
	
	Extends: ART.Element,
	
	initialize: function(tag){
		this.uid = ART.uniqueID();
		if (!(tag in styledTags)) styleTag(tag);

		var element = this.element = document.createElement('av:' + tag);
		element.setAttribute('id', 'e' + this.uid);
		
		this.transform = {translate: [0, 0], scale: [1, 1], rotate: [0, 0, 0]};
	},
	
	/* dom */
	
	inject: function(container){
		this.eject();
		this.container = container;
		container.children.include(this);
		this._transform();
		this.parent(container);
		
		return this;
	},

	eject: function(){
		if (this.container){
			this.container.children.erase(this);
			this.container = null;
			this.parent();
		}
		return this;
	},

	/* transform */

	_transform: function(){
		var l = this.left || 0, t = this.top || 0,
		    w = this.width, h = this.height;
		
		if (w == null || h == null) return;
		
		var tn = this.transform,
			tt = tn.translate,
			ts = tn.scale,
			tr = tn.rotate;

		var cw = w, ch = h,
		    cl = l, ct = t,
		    pl = tt[0], pt = tt[1],
		    rotation = tr[0],
		    rx = tr[1], ry = tr[2];
		
		// rotation offset
		var theta = rotation / 180 * Math.PI,
		    sin = Math.sin(theta), cos = Math.cos(theta);
		
		var dx = w / 2 - rx,
		    dy = h / 2 - ry;
				
		pl -= cos * -(dx + l) + sin * (dy + t) + dx;
		pt -= cos * -(dy + t) - sin * (dx + l) + dy;
 
		// scale
		cw /= ts[0];
		ch /= ts[1];
		cl /= ts[0];
		ct /= ts[1];
 
		// transform into multiplied precision space		
		cw *= precision;
		ch *= precision;
		cl *= precision;
		ct *= precision;

		pl *= precision;
		pt *= precision;
		w *= precision;
		h *= precision;
		
		var element = this.element;
		element.coordorigin = cl + ',' + ct;
		element.coordsize = cw + ',' + ch;
		element.style.left = pl;
		element.style.top = pt;
		element.style.width = w;
		element.style.height = h;
		element.style.rotation = rotation;
	},
	
	// transformations
	
	translate: function(x, y){
		this.transform.translate = [x, y];
		this._transform();
		return this;
	},
	
	scale: function(x, y){
		if (y == null) y = x;
		this.transform.scale = [x, y];
		this._transform();
		return this;
	},
	
	rotate: function(deg, x, y){
		if (x == null || y == null){
			var box = this.measure(precision);
			x = box.left + box.width / 2; y = box.top + box.height / 2;
		}
		this.transform.rotate = [deg, x, y];
		this._transform();
		return this;
	},
	
	// visibility
	
	hide: function(){
		this.element.style.display = 'none';
		return this;
	},
	
	show: function(){
		this.element.style.display = '';
		return this;
	}
	
});

// VML Group Class

ART.VML.Group = new Class({
	
	Extends: ART.VML.Element,
	Implements: ART.Container,
	
	initialize: function(){
		this.parent('group');
		this.children = [];
	},
	
	/* dom */
	
	inject: function(container){
		this.parent(container);
		this.width = container.width;
		this.height = container.height;
		this._transform();
		return this;
	},
	
	eject: function(){
		this.parent();
		this.width = this.height = null;
		return this;
	}

});

// VML Base Shape Class

ART.VML.Base = new Class({

	Extends: ART.VML.Element,
	
	initialize: function(tag){
		this.parent(tag);
		var element = this.element;

		var fill = this.fillElement = document.createElement('av:fill');
		fill.on = false;
		element.appendChild(fill);
		
		var stroke = this.strokeElement = document.createElement('av:stroke');
		stroke.on = false;
		element.appendChild(stroke);
	},
	
	/* styles */

	_createGradient: function(style, stops){
		var fill = this.fillElement;

		// Temporarily eject the fill from the DOM
		this.element.removeChild(fill);

		fill.type = style;
		fill.method = 'none';
		fill.rotate = true;

		var colors = [], color1, color2;

		var addColor = function(offset, color){
			color = Color.detach(color);
			if (color1 == null) color1 = color;
			else color2 = color;
			colors.push(offset + ' ' + color[0]);
		};

		// Enumerate stops, assumes offsets are enumerated in order
		if ('length' in stops) for (var i = 0, l = stops.length - 1; i <= l; i++) addColor(i / l, stops[i]);
		else for (var offset in stops) addColor(offset, stops[offset]);
		
		fill.color = color1[0];
		fill.color2 = color2[0];
		
		//if (fill.colors) fill.colors.value = colors; else
		fill.colors = colors;

		// Opacity order gets flipped when color stops are specified
		fill.opacity = color2[1];
		fill['ao:opacity2'] = color1[1];

		fill.on = true;
		this.element.appendChild(fill);
		return fill;
	},
	
	_setColor: function(type, color){
		var element = this[type + 'Element'];
		if (color == null){
			element.on = false;
		} else {
			color = Color.detach(color);
			element.color = color[0];
			element.opacity = color[1];
			element.on = true;
		}
	},
	
	fill: function(color){
		if (arguments.length > 1){
			this.fillLinear(arguments);
		} else {
			var fill = this.fillElement;
			fill.type = 'solid';
			fill.color2 = '';
			fill['ao:opacity2'] = '';
			if (fill.colors) fill.colors.value = '';
			this._setColor('fill', color);
		}
		return this;
	},

	fillRadial: function(stops, focusX, focusY, radius){
		var fill = this._createGradient('gradientradial', stops);
		fill.focus = 50;
		fill.focussize = '0 0';
		fill.focusposition = (focusX == null ? 0.5 : focusX) + ',' + (focusY == null ? 0.5 : focusY);
		fill.focus = (radius == null || radius > 0.5) ? '100%' : (Math.round(radius * 200) + '%');
		return this;
	},

	fillLinear: function(stops, angle){
		var fill = this._createGradient('gradient', stops);
		fill.focus = '100%';
		fill.angle = (angle == null) ? 0 : (90 + angle) % 360;
		return this;
	},

	/* stroke */
	
	stroke: function(color, width, cap, join){
		var stroke = this.strokeElement;
		stroke.weight = (width != null) ? (width / 2) + 'pt' : 1;
		stroke.endcap = (cap != null) ? ((cap == 'butt') ? 'flat' : cap) : 'round';
		stroke.joinstyle = (join != null) ? join : 'round';

		this._setColor('stroke', color);
		return this;
	}

});

// VML Shape Class

ART.VML.Shape = new Class({

	Extends: ART.VML.Base,
	
	initialize: function(path){
		this.parent('shape');

		var p = this.pathElement = document.createElement('av:path');
		p.gradientshapeok = true;
		this.element.appendChild(p);
		
		if (path != null) this.draw(path);
	},
	
	getPath: function(){
		return this.currentPath;
	},
	
	// SVG to VML
	
	draw: function(path){
		
		this.currentPath = (path instanceof ART.Path) ? path : new ART.Path(path);
		this.currentVML = this.currentPath.toVML(precision);
		var size = this.currentPath.measure(precision);
		
		this.right = size.right;
		this.bottom = size.bottom;
		this.top = size.top;
		this.left = size.left;
		this.height = size.height;
		this.width = size.width;
		
		this._transform();
		this._redraw(this._radial);
		
		return this;
	},
	
	measure: function(){
		return this.getPath().measure();
	},
	
	// radial gradient workaround

	_redraw: function(radial){
		var vml = this.currentVML || '';

		this._radial = radial;
		if (radial){
			var cx = Math.round((this.left + this.width * radial.x) * precision),
				cy = Math.round((this.top + this.height * radial.y) * precision),

				rx = Math.round(this.width * radial.r * precision),
				ry = Math.round(this.height * radial.r * precision),

				arc = ['wa', cx - rx, cy - ry, cx + rx, cy + ry].join(' ');

			vml = [
				// Resolve rendering bug
				'm', cx, cy - ry, 'l', cx, cy - ry,

				// Merge existing path
				vml,

				// Draw an ellipse around the path to force an elliptical gradient on any shape
				'm', cx, cy - ry,
				arc, cx, cy - ry, cx, cy + ry, arc, cx, cy + ry, cx, cy - ry,
				arc, cx, cy - ry, cx, cy + ry, arc, cx, cy + ry, cx, cy - ry,

				// Don't stroke the path with the extra ellipse, redraw the stroked path separately
				'ns e', vml, 'nf'
			
			].join(' ');
		}

		this.element.path = vml + 'e';
	},

	fill: function(){
		this._redraw();
		return this.parent.apply(this, arguments);
	},

	fillLinear: function(){
		this._redraw();
		return this.parent.apply(this, arguments);
	},

	fillRadial: function(stops, focusX, focusY, radius, centerX, centerY){
		this.parent.apply(this, arguments);

		if (focusX == null) focusX = 0.5;
		if (focusY == null) focusY = 0.5;
		if (radius == null) radius = 0.5;
		if (centerX == null) centerX = focusX;
		if (centerY == null) centerY = focusY;
		
		centerX += centerX - focusX;
		centerY += centerY - focusY;
		
		// Compensation not needed when focusposition is applied out of document
		//focusX = (focusX - centerX) / (radius * 4) + 0.5;
		//focusY = (focusY - centerY) / (radius * 4) + 0.5;

		this.fillElement.focus = '50%';
		//this.fillElement.focusposition = focusX + ',' + focusY;

		this._redraw({x: centerX, y: centerY, r: radius * 2});

		return this;
	}

});
	
})();


/*
---
name: ART.Base
description: "Implements ART, ART.Shape and ART.Group based on the current browser."
provides: [ART.Base, ART.Group, ART.Shape]
requires: [ART.VML, ART.SVG]
...
*/

(function(){
	
var SVG = function(){

	var implementation = document.implementation;
	return (implementation && implementation.hasFeature && implementation.hasFeature("http://www.w3.org/TR/SVG11/feature#BasicStructure", "1.1"));

};

var VML = function(){

	return ART.VML.init(document);

};

var MODE = SVG() ? 'SVG' : VML() ? 'VML' : null;
if (!MODE) return;

ART.Shape = new Class({Extends: ART[MODE].Shape});
ART.Group = new Class({Extends: ART[MODE].Group});
ART.implement({Extends: ART[MODE]});

})();


/*
---
name: ART.Shapes
description: "Shapes for ART"
authors: ["[Valerio Proietti](http://mad4milk.net)", "[Sebastian Markbåge](http://calyptus.eu/)"]
provides: [ART.Shapes, ART.Rectangle, ART.Pill, ART.Ellipse, ART.Wedge]
requires: [ART.Path, ART.Shape]
...
*/

ART.Rectangle = new Class({

	Extends: ART.Shape,
	
	initialize: function(width, height, radius){
		this.parent();
		if (width != null && height != null) this.draw(width, height, radius);
	},
	
	draw: function(width, height, radius){

		var path = new ART.Path;

		if (!radius){

			path.move(0, 0).line(width, 0).line(0, height).line(-width, 0).line(0, -height);

		} else {

			if (typeof radius == 'number') radius = [radius, radius, radius, radius];

			var tl = radius[0], tr = radius[1], br = radius[2], bl = radius[3];

			if (tl < 0) tl = 0;
			if (tr < 0) tr = 0;
			if (bl < 0) bl = 0;
			if (br < 0) br = 0;

			path.move(0, tl);

			if (width < 0) path.move(width, 0);
			if (height < 0) path.move(0, height);

			if (tl > 0) path.arc(tl, -tl);
			path.line(Math.abs(width) - (tr + tl), 0);

			if (tr > 0) path.arc(tr, tr);
			path.line(0, Math.abs(height) - (tr + br));

			if (br > 0) path.arc(-br, br);
			path.line(- Math.abs(width) + (br + bl), 0);

			if (bl > 0) path.arc(-bl, -bl);
			path.line(0, - Math.abs(height) + (bl + tl));
		}

		return this.parent(path);
	}

});

ART.Pill = new Class({
	
	Extends: ART.Rectangle,
	
	draw: function(width, height){
		return this.parent(width, height, ((width < height) ? width : height) / 2);
	}
	
});

ART.Ellipse = new Class({
	
	Extends: ART.Shape,
	
	initialize: function(width, height){
		this.parent();
		if (width != null && height != null) this.draw(width, height);
	},
	
	draw: function(width, height){
		var path = new ART.Path;
		var rx = width / 2, ry = height / 2;
		path.move(0, ry).arc(width, 0, rx, ry).arc(-width, 0, rx, ry);
		return this.parent(path);
	}

});

ART.Wedge = new Class({

	Extends: ART.Shape,

	initialize: function(innerRadius, outerRadius, startAngle, endAngle){
		this.parent();
		if (innerRadius != null || outerRadius != null) this.draw(innerRadius, outerRadius, startAngle, endAngle);
	},

	draw: function(innerRadius, outerRadius, startAngle, endAngle){
		var path = new ART.Path;

		var circle = Math.PI * 2,
			radiansPerDegree = Math.PI / 180,
			sa = startAngle * radiansPerDegree % circle || 0,
			ea = endAngle * radiansPerDegree % circle || 0,
			ir = Math.min(innerRadius || 0, outerRadius || 0),
			or = Math.max(innerRadius || 0, outerRadius || 0),
			a = sa > ea ? circle - sa + ea : ea - sa;

		if (a >= circle){

			path.move(0, or).arc(or * 2, 0, or).arc(-or * 2, 0, or);
			if (ir) path.move(or - ir, 0).counterArc(ir * 2, 0, ir).counterArc(-ir * 2, 0, ir);

		} else {

			var ss = Math.sin(sa), es = Math.sin(ea),
				sc = Math.cos(sa), ec = Math.cos(ea),
				ds = es - ss, dc = ec - sc, dr = ir - or,
				large = a > Math.PI;

			path.move(or + or * ss, or - or * sc).arc(or * ds, or * -dc, or, or, large).line(dr * es, dr * -ec);
			if (ir) path.counterArc(ir * -ds, ir * dc, ir, ir, large);

		}

		path.close();
		return this.parent(path);
	}

});


/*
---
name: ART.Font
description: "Fonts for ART, implements code from [Cufón](http://cufon.shoqolate.com/)"
authors: ["[Simo Kinnunen](http://twitter.com/sorccu)", "[Valerio Proietti](http://mad4milk.net/)"]
provides: ART.Font
requires: ART.Shape
...
*/

(function(){

var fonts = {};

ART.registerFont = function(font){
	var face = font.face, name = face['font-family'];
	if (!fonts[name]) fonts[name] = {};
	var currentFont = fonts[name];
	var isBold = (face['font-weight'] > 400), isItalic = (face['font-stretch'] == 'oblique');
	if (isBold && isItalic) currentFont.boldItalic = font;
	else if (isBold) currentFont.bold = font;
	else if (isItalic) currentFont.italic = font;
	else currentFont.normal = font;
	return this;
};

var VMLToSVG = function(path, s, x, y){
	var end = '';
	var regexp = /([mrvxe])([^a-z]*)/g, match;
	while ((match = regexp.exec(path))){
		var c = match[2].split(',');
		switch (match[1]){
			case 'v': end += 'c ' + (s * c[0]) + ',' + (s * c[1]) + ',' + (s * c[2]) + ',' + (s * c[3]) + ',' + (s * c[4]) + ',' + (s * c[5]); break;
			case 'r': end += 'l ' + (s * c[0]) + ',' + (s * c[1]); break;
			case 'm': end += 'M ' + (x + (s * c[0])) + ',' + (y + (s * c[1])); break;
			case 'x': end += 'z'; break;
		}
	}
	
	return end;
};

ART.Font = new Class({
	
	Extends: ART.Shape,
	
	initialize: function(font, variant, text, size){
		this.parent();
		if (font != null && text != null && size != null) this.draw(font, variant, text, size);
	},
	
	draw: function(font, variant, text, size){
		if (typeof font == 'string') font = fonts[font][(variant || 'normal').camelCase()];
		if (!font) throw new Error('The specified font has not been found.');
		size = size / font.face['units-per-em'];
		
		var width = 0, height = size * font.face.ascent, path = '';

		for (var i = 0, l = text.length; i < l; ++i){
			var glyph = font.glyphs[text.charAt(i)] || font.glyphs[' '];
			var w = size * (glyph.w || font.w);
			if (glyph.d) path += VMLToSVG('m' + glyph.d + 'x', size, width, height);
			width += w;
		}
		
		this.fontSize = {width: width, height: size * (font.face.ascent - font.face.descent)};
		
		return this.parent(path);
	},
	
	measure: function(){
		return this.fontSize || this.parent();
	}

});

})();


/*
---

name: Moderna

description: MgOpen Moderna built with [Cufón](http://wiki.github.com/sorccu/cufon/about)

provides: Moderna

requires: ART.Font

...
*/

/*!
 * The following copyright notice may not be removed under any circumstances.
 * 
 * Copyright:
 * Copyright (c) Magenta ltd, 2004.
 * 
 * Manufacturer:
 * Magenta ltd
 * 
 * Vendor URL:
 * http://www.magenta.gr
 * 
 * License information:
 * http://www.ellak.gr/fonts/MgOpen/license.html
 */

ART.registerFont({"w":205,"face":{"font-family":"Moderna","font-weight":400,"font-stretch":"normal","units-per-em":"360","panose-1":"2 11 5 3 0 2 0 6 0 4","ascent":"288","descent":"-72","x-height":"6","bbox":"-20 -279 328 86","underline-thickness":"18","underline-position":"-27","unicode-range":"U+0020-U+007E"},"glyphs":{" ":{"w":97},"!":{"d":"9,-63v0,-20,-9,-93,-9,-114r0,-85r36,0v2,71,-3,135,-9,199r-18,0xm36,0r-36,0r0,-39r36,0r0,39","w":61},"\"":{"d":"77,-157r-24,0r0,-99r24,0r0,99xm25,-157r-25,0r0,-99r25,0r0,99","w":102},"#":{"d":"250,-179r-10,26r-54,0r-16,47r57,0r-9,26r-58,0r-28,81r-30,0r29,-81r-46,0r-29,81r-29,0r28,-81r-55,0r9,-26r55,0r17,-47r-59,0r9,-26r59,0r29,-82r30,0r-29,82r46,0r29,-82r29,0r-28,82r54,0xm157,-153r-47,0r-16,47r47,0","w":275},"$":{"d":"3,-188v-1,-41,36,-67,77,-67r0,-24r19,0r0,24v41,0,75,27,73,69r-32,0v-4,-23,-17,-38,-41,-40r0,83v49,9,82,29,82,80v0,47,-32,70,-82,69r0,33r-19,0r0,-33v-50,1,-81,-32,-80,-83r32,0v0,32,17,54,48,56r0,-93v-47,-9,-75,-25,-77,-74xm80,-226v-37,-5,-56,41,-33,66v7,7,18,10,33,13r0,-79xm99,-20v46,-2,59,-45,35,-74v-8,-8,-19,-14,-35,-16r0,90"},"%":{"d":"282,-63v0,37,-27,63,-63,63v-36,0,-63,-27,-63,-63v0,-36,26,-64,63,-64v36,0,63,28,63,64xm184,-63v0,21,15,36,35,36v22,0,35,-16,35,-36v0,-21,-14,-38,-35,-37v-21,0,-35,16,-35,37xm230,-256r-156,263r-22,0r156,-263r22,0xm125,-185v0,36,-26,64,-62,64v-37,0,-65,-28,-64,-64v0,-36,26,-63,63,-63v37,0,63,26,63,63xm99,-185v0,-21,-15,-37,-36,-37v-23,-1,-37,17,-37,38v0,21,14,36,36,36v23,0,37,-16,37,-37","w":307},"&":{"d":"161,-85v6,-14,8,-20,11,-43r31,0v0,24,-7,48,-20,69r49,59r-44,0r-26,-32v-20,23,-46,43,-82,43v-49,0,-76,-38,-81,-83v5,-40,30,-58,65,-79v-15,-18,-28,-34,-28,-61v-1,-34,29,-59,64,-58v35,0,62,23,61,57v-2,34,-23,53,-49,69xm94,-163v21,-15,35,-23,37,-49v1,-19,-14,-31,-31,-31v-31,-2,-45,34,-25,58v4,6,10,13,19,22xm35,-73v0,59,76,70,109,19r-62,-76v-25,15,-47,27,-47,57","w":257},"'":{"d":"36,-262v2,43,0,79,-36,88r0,-16v14,-2,18,-19,19,-36r-19,0r0,-36r36,0","w":61},"(":{"d":"82,-265v-65,92,-63,248,0,340r-22,0v-33,-44,-60,-101,-60,-170v0,-70,26,-124,60,-170r22,0","w":107},")":{"d":"23,-265v33,46,59,99,59,170v0,70,-25,126,-59,170r-23,0v63,-91,65,-248,0,-340r23,0","w":107},"*":{"d":"175,-199r-52,14r35,44r-23,17r-32,-45r-32,44r-25,-16r36,-44r-52,-14r10,-28r49,16r0,-51r27,0r0,51r49,-16"},"+":{"d":"199,-96r-84,0r0,96r-25,0r0,-96r-84,0r0,-25r84,0r0,-97r25,0r0,97r84,0r0,25"},",":{"d":"37,-39v2,45,0,84,-37,93r0,-18v13,-4,18,-17,18,-36r-18,0r0,-39r37,0","w":61},"-":{"d":"91,-82r-88,0r0,-29r88,0r0,29","w":123},".":{"d":"37,0r-37,0r0,-39r37,0r0,39","w":61},"\/":{"d":"158,-270r-89,303r-21,0r87,-303r23,0"},"0":{"d":"102,-256v112,1,112,263,0,264v-111,-3,-112,-261,0,-264xm102,-226v-72,9,-72,196,0,203v72,-7,72,-196,0,-203"},"1":{"d":"56,-206v44,-1,57,-14,67,-50r26,0r0,256r-34,0r0,-181r-59,0r0,-25"},"2":{"d":"106,-256v44,0,84,32,84,76v0,84,-120,83,-138,148r138,0r0,32r-175,0v0,-33,11,-60,36,-81v32,-27,102,-51,104,-98v1,-27,-22,-45,-48,-46v-32,0,-54,27,-53,62r-33,0v-1,-53,32,-93,85,-93"},"3":{"d":"150,-136v77,29,37,142,-46,142v-55,0,-91,-31,-90,-86r34,0v-1,34,21,55,55,55v31,0,54,-18,53,-48v-2,-37,-28,-45,-72,-45r0,-29v41,0,60,-5,63,-39v1,-24,-20,-43,-44,-42v-33,1,-51,21,-50,57r-33,0v0,-52,29,-86,80,-86v43,0,82,27,82,69v0,26,-15,41,-32,52"},"4":{"d":"193,-63r-37,0r0,63r-33,0r0,-63r-110,0r0,-32r110,-153r33,0r0,156r37,0r0,29xm123,-92r0,-114r-81,114r81,0"},"5":{"d":"55,-150v52,-43,136,2,136,69v0,76,-94,111,-151,69v-18,-13,-26,-31,-26,-54r34,0v6,25,23,43,51,43v31,0,57,-26,57,-58v0,-58,-77,-72,-105,-33r-30,0r20,-134r136,0r0,30r-111,0"},"6":{"d":"50,-134v40,-54,142,-22,142,53v0,48,-40,90,-88,89v-60,-2,-90,-55,-90,-122v0,-99,76,-181,153,-123v16,12,23,31,23,52r-34,0v-2,-42,-62,-57,-87,-21v-12,18,-19,41,-19,72xm105,-22v29,-1,51,-23,51,-55v0,-32,-21,-57,-51,-57v-30,0,-51,23,-51,56v0,32,21,56,51,56"},"7":{"d":"190,-218v-56,66,-86,121,-103,218r-37,0v17,-97,48,-149,104,-215r-139,0r0,-33r175,0r0,30"},"8":{"d":"182,-189v0,25,-15,42,-34,52v25,10,43,34,43,64v0,46,-40,80,-88,80v-48,0,-89,-33,-89,-79v0,-32,18,-54,43,-65v-20,-10,-34,-27,-34,-51v-1,-86,160,-90,159,-1xm58,-187v0,25,19,40,45,40v25,0,43,-16,43,-39v0,-24,-19,-40,-44,-40v-25,0,-44,15,-44,39xm50,-73v0,29,23,50,53,50v29,0,52,-22,52,-50v0,-28,-22,-48,-52,-48v-30,0,-53,19,-53,48"},"9":{"d":"99,-256v62,0,93,53,93,120v0,103,-74,183,-153,124v-17,-13,-24,-31,-24,-51r34,0v0,41,64,58,87,21v11,-17,19,-40,19,-72v-45,56,-141,22,-141,-54v0,-50,36,-88,85,-88xm49,-171v0,32,19,56,51,55v30,0,52,-24,52,-55v0,-30,-22,-55,-52,-55v-31,0,-52,25,-51,55"},":":{"d":"37,-152r-37,0r0,-39r37,0r0,39xm37,0r-37,0r0,-39r37,0r0,39","w":61},";":{"d":"37,-152r-37,0r0,-39r37,0r0,39xm37,-39v2,45,0,84,-37,93r0,-18v13,-6,18,-15,18,-36r-18,0r0,-39r37,0","w":61},"<":{"d":"201,-11r-196,-87r0,-23r196,-86r0,28r-160,70r160,70r0,28"},"=":{"d":"203,-132r-200,0r0,-25r200,0r0,25xm203,-61r-200,0r0,-25r200,0r0,25"},">":{"d":"201,-98r-196,87r0,-28r158,-70r-158,-70r0,-28r196,86r0,23"},"?":{"d":"78,-271v61,-2,105,68,63,115v-20,23,-50,42,-48,85r-32,0v-5,-60,58,-77,62,-127v2,-25,-21,-44,-45,-44v-30,0,-47,26,-46,59r-32,0v-1,-51,29,-86,78,-88xm96,0r-36,0r0,-39r36,0r0,39","w":182},"@":{"d":"163,32v41,0,87,-15,112,-34r10,14v-30,22,-73,41,-121,41v-95,3,-164,-57,-164,-147v0,-94,81,-167,178,-167v85,0,148,49,149,127v1,60,-43,115,-102,113v-25,0,-33,-8,-34,-31v-11,18,-27,30,-54,31v-36,1,-54,-25,-56,-60v-4,-67,86,-138,129,-72r10,-20r23,0r-27,115v0,10,8,15,18,15v40,0,66,-45,66,-89v0,-68,-50,-108,-122,-108v-85,0,-154,63,-153,146v0,76,59,128,138,126xm143,-40v43,-8,47,-31,61,-87v-2,-19,-17,-32,-37,-32v-32,-1,-58,40,-57,75v0,23,11,44,33,44","w":351},"A":{"d":"235,0r-39,0r-28,-78r-103,0r-28,78r-37,0r98,-262r39,0xm158,-109r-41,-116r-41,116r82,0","w":261,"k":{"y":54,"w":44,"v":55,"t":29,"s":12,"q":17,"o":17,"j":8,"i":8,"g":17,"f":27,"e":17,"d":26,"c":18,"a":8,"Z":8,"Y":79,"W":57,"V":78,"U":19,"T":77,"S":22,"Q":28,"O":28,"J":27,"G":29,"C":27}},"B":{"d":"190,-199v0,29,-18,49,-40,58v31,3,52,27,52,60v1,48,-41,81,-90,81r-112,0r0,-262v81,1,189,-16,190,63xm153,-193v0,-49,-66,-40,-117,-40r0,81v51,1,117,7,117,-41xm165,-78v0,-53,-72,-46,-129,-45r0,91v57,1,129,7,129,-46","w":227,"k":{"z":10,"x":8,"Z":13,"Y":30,"X":25,"W":16,"V":21,"T":21,"A":17}},"C":{"d":"122,-26v41,0,74,-32,73,-72r35,0v2,60,-53,105,-112,105v-75,0,-118,-61,-118,-139v0,-78,46,-138,122,-138v55,0,107,36,106,86r-35,0v-6,-31,-34,-55,-70,-55v-54,0,-88,50,-88,107v0,56,32,106,87,106","w":244,"k":{"Z":18,"Y":28,"X":30,"W":11,"V":18,"T":19,"A":20}},"D":{"d":"215,-135v0,71,-47,135,-116,135r-99,0r0,-262r101,0v69,-2,114,57,114,127xm176,-132v0,-51,-30,-100,-78,-100r-62,0r0,200r62,0v49,1,78,-48,78,-100","w":242,"k":{"Z":32,"Y":41,"X":44,"W":20,"V":28,"T":35,"J":13,"A":31}},"E":{"d":"191,0r-191,0r0,-262r188,0r0,32r-152,0r0,78r140,0r0,31r-140,0r0,89r155,0r0,32","w":204,"k":{"y":24,"w":22,"v":25,"t":22,"q":12,"o":12,"g":12,"f":24,"e":12,"d":16,"c":12,"Y":7,"T":10,"S":12,"Q":9,"O":9,"J":15,"G":9,"C":9}},"F":{"d":"177,-230r-141,0r0,79r123,0r0,32r-123,0r0,119r-36,0r0,-262r177,0r0,32","w":185,"k":{"z":75,"y":25,"x":37,"w":22,"v":26,"u":8,"t":18,"s":17,"r":8,"q":13,"p":8,"o":12,"n":8,"m":8,"g":12,"f":21,"e":12,"d":15,"c":12,"a":19,"Z":10,"S":9,"Q":8,"O":8,"J":98,"G":8,"C":8,"A":49}},"G":{"d":"37,-132v0,57,34,108,88,108v47,0,82,-40,81,-89r-82,0r0,-29r115,0r0,142r-23,0r-8,-35v-18,24,-48,41,-86,42v-70,2,-122,-65,-122,-137v0,-109,109,-178,198,-118v22,16,34,37,37,64r-35,0v-7,-32,-37,-56,-74,-56v-55,0,-89,51,-89,108","w":262,"k":{"Y":32,"W":14,"V":21,"T":23}},"H":{"d":"208,0r-35,0r0,-123r-137,0r0,123r-36,0r0,-262r36,0r0,107r137,0r0,-107r35,0r0,262","w":237},"I":{"d":"47,0r-36,0r0,-262r36,0r0,262","w":75},"J":{"d":"75,-25v35,0,41,-20,41,-62r0,-175r36,0r0,190v0,53,-25,79,-77,79v-56,-1,-75,-33,-75,-92r35,0v0,34,9,60,40,60","w":181,"k":{"A":13}},"K":{"d":"212,0r-44,0r-92,-132r-40,39r0,93r-36,0r0,-262r36,0r0,126r126,-126r48,0r-107,106","w":234,"k":{"y":55,"w":45,"v":56,"u":13,"t":30,"s":19,"q":27,"o":27,"g":27,"f":24,"e":27,"d":32,"c":27,"a":13,"Y":7,"T":10,"S":28,"Q":39,"O":39,"J":29,"G":39,"C":37}},"L":{"d":"167,0r-167,0r0,-262r36,0r0,229r131,0r0,33","w":180,"k":{"y":47,"w":39,"v":48,"t":24,"q":12,"o":12,"j":8,"i":8,"g":12,"f":27,"e":12,"d":20,"c":12,"Z":8,"Y":79,"W":51,"V":71,"U":13,"T":77,"S":15,"Q":27,"O":27,"J":18,"G":27,"C":24}},"M":{"d":"247,0r-35,0r0,-225r-73,225r-34,0r-72,-224r0,224r-33,0r0,-262r52,0r70,221r75,-221r50,0r0,262","w":276},"N":{"d":"208,0r-39,0r-135,-213r0,213r-34,0r0,-262r39,0r135,212r0,-212r34,0r0,262","w":236},"O":{"d":"0,-131v0,-77,52,-139,126,-139v73,0,122,62,122,137v0,77,-49,140,-123,140v-75,0,-125,-62,-125,-138xm37,-131v0,57,33,106,88,106v53,0,86,-50,86,-106v0,-57,-32,-106,-86,-106v-54,0,-88,49,-88,106","w":273,"k":{"Z":28,"Y":39,"X":40,"W":18,"V":26,"T":31,"J":10,"A":28}},"P":{"d":"188,-186v0,45,-35,75,-81,75r-71,0r0,111r-36,0r0,-262r110,0v44,-1,78,31,78,76xm154,-187v0,-50,-65,-44,-118,-43r0,88v54,2,118,4,118,-45","w":208,"k":{"z":11,"x":8,"s":13,"q":17,"o":17,"g":17,"e":17,"d":20,"c":17,"a":17,"Z":26,"Y":30,"X":32,"W":14,"V":20,"T":20,"S":9,"J":104,"A":54}},"Q":{"d":"126,-270v121,0,159,165,90,243r31,26r-17,22r-37,-30v-91,48,-193,-18,-193,-122v0,-77,51,-139,126,-139xm37,-131v0,72,58,132,127,99r-29,-24r17,-22r36,28v52,-56,27,-189,-63,-189v-55,0,-88,51,-88,108","w":277,"k":{"Y":39,"W":18,"V":26,"T":32,"J":10}},"R":{"d":"194,-190v0,27,-16,50,-36,60v28,15,31,25,31,76v0,31,11,37,14,54r-41,0v-6,-9,-10,-30,-10,-63v0,-62,-61,-48,-116,-49r0,112r-36,0r0,-262v87,-1,194,-12,194,72xm159,-190v0,-48,-70,-43,-123,-42r0,89v56,2,123,3,123,-47","w":235,"k":{"y":9,"w":8,"v":9,"t":8,"s":12,"q":11,"o":11,"j":8,"i":8,"g":10,"f":9,"e":11,"d":18,"c":11,"a":10,"Z":8,"Y":38,"W":22,"V":28,"T":28,"S":14,"Q":12,"O":12,"J":23,"G":12,"C":11}},"S":{"d":"103,-242v-29,-1,-64,19,-61,45v9,67,162,33,162,121v0,78,-120,108,-174,58v-19,-18,-30,-40,-30,-69r34,0v0,39,27,65,66,65v32,0,71,-19,68,-48v-7,-69,-159,-35,-159,-122v0,-74,107,-101,158,-56v19,16,29,36,29,62r-34,0v0,-34,-24,-56,-59,-56","w":230,"k":{"z":13,"y":8,"x":10,"v":8,"Z":15,"Y":35,"X":27,"W":19,"V":25,"T":25,"J":9,"A":20}},"T":{"d":"202,-231r-83,0r0,231r-36,0r0,-231r-83,0r0,-31r202,0r0,31","w":216,"k":{"z":65,"y":64,"x":64,"w":63,"v":65,"u":58,"t":18,"s":63,"r":58,"q":61,"p":58,"o":61,"n":58,"m":58,"g":61,"f":22,"e":61,"d":65,"c":62,"a":62,"Z":10,"S":12,"Q":22,"O":22,"J":72,"G":21,"C":19,"A":66}},"U":{"d":"102,-28v39,0,68,-33,68,-74r0,-160r36,0r0,160v2,61,-44,108,-106,108v-61,0,-100,-46,-100,-108r0,-160r36,0r0,160v-1,41,26,74,66,74","w":234,"k":{"Z":10,"A":21}},"V":{"d":"224,-262r-93,262r-37,0r-94,-262r37,0r76,219r73,-219r38,0","w":243,"k":{"z":24,"y":19,"x":21,"w":18,"v":19,"u":13,"t":15,"s":31,"r":12,"q":35,"p":11,"o":35,"n":11,"m":11,"g":35,"f":15,"e":35,"d":39,"c":35,"a":35,"Z":10,"S":18,"Q":21,"O":21,"J":54,"G":21,"C":20,"A":72}},"W":{"d":"328,-262r-70,262r-35,0r-60,-217r-58,217r-36,0r-69,-262r35,0r54,209r55,-209r38,0r57,208r55,-208r34,0","w":350,"k":{"z":18,"y":13,"x":15,"w":12,"v":13,"u":7,"t":10,"s":24,"q":26,"o":25,"g":25,"f":10,"e":26,"d":30,"c":25,"a":27,"Z":10,"S":15,"Q":15,"O":15,"J":42,"G":15,"C":15,"A":54}},"X":{"d":"221,0r-44,0r-67,-106r-67,106r-43,0r90,-135r-85,-127r43,0r63,97r64,-97r42,0r-86,127","w":243,"k":{"y":42,"w":41,"v":42,"u":11,"t":27,"s":17,"q":25,"o":25,"g":25,"f":22,"e":24,"d":30,"c":24,"a":11,"Y":7,"T":10,"S":26,"Q":36,"O":36,"J":27,"G":36,"C":34}},"Y":{"d":"212,-262r-87,154r0,108r-36,0r0,-108r-89,-154r36,0r71,124r69,-124r36,0","w":229,"k":{"z":37,"y":32,"x":34,"w":31,"v":32,"u":26,"t":24,"s":48,"r":25,"q":55,"p":23,"o":54,"n":22,"m":22,"g":55,"f":28,"e":55,"d":58,"c":54,"a":53,"Z":10,"S":23,"Q":31,"O":31,"J":75,"G":31,"C":29,"A":69}},"Z":{"d":"202,0r-202,0r0,-30r161,-202r-151,0r0,-30r192,0r0,32r-158,198r158,0r0,32","w":219,"k":{"y":25,"w":24,"v":25,"t":18,"q":10,"o":10,"g":10,"f":20,"e":9,"d":13,"c":9,"S":9,"Q":18,"O":18,"J":12,"G":17,"C":15}},"[":{"d":"71,73r-71,0r0,-335r71,0r0,24r-39,0r0,286r39,0r0,25","w":96},"\\":{"d":"110,33r-23,0r-87,-303r22,0","w":135},"]":{"d":"71,73r-71,0r0,-25r39,0r0,-286r-39,0r0,-24r71,0r0,335","w":96},"^":{"d":"155,-200r-21,0r-31,-41r-31,41r-22,0r35,-62r35,0"},"_":{"d":"183,86r-183,0r0,-26r183,0r0,26","w":208},"`":{"d":"36,-181r-36,0v-2,-43,-2,-81,36,-89r0,17v-16,1,-18,19,-18,36r18,0r0,36","w":61},"a":{"d":"86,-200v40,0,74,24,74,62v0,36,-3,78,2,110v1,6,12,5,19,4r0,24v-22,10,-52,2,-51,-25v-27,43,-135,45,-130,-25v3,-46,32,-57,85,-63v34,-4,43,-2,43,-23v0,-47,-88,-44,-88,3r-31,0v-1,-41,34,-67,77,-67xm68,-22v38,0,68,-26,59,-74v-43,8,-91,10,-94,44v-2,20,16,30,35,30","k":{"y":21,"w":17,"v":20,"t":12,"f":13}},"b":{"d":"89,-195v51,-1,88,47,88,99v0,53,-35,102,-87,102v-28,0,-47,-12,-59,-30r0,24r-31,0r0,-262r31,0r0,97v11,-18,31,-30,58,-30xm86,-22v35,0,58,-34,58,-71v0,-39,-22,-75,-59,-74v-35,0,-58,33,-57,71v0,38,22,74,58,74","w":203,"k":{"z":8,"y":13,"x":21,"w":8,"v":12,"t":14,"f":16}},"c":{"d":"93,-23v28,0,50,-19,54,-45r35,0v-5,43,-44,75,-90,75v-56,0,-92,-46,-92,-103v0,-81,86,-131,148,-82v16,13,27,29,31,50r-35,0v-3,-22,-26,-40,-51,-40v-38,0,-58,33,-58,72v0,39,21,74,58,73","w":200,"k":{"y":11,"x":18,"w":7,"v":11}},"d":{"d":"0,-95v0,-82,98,-142,146,-73r0,-94r31,0r0,262r-31,0r0,-24v-15,15,-33,30,-61,30v-49,0,-85,-51,-85,-101xm93,-22v37,0,58,-36,58,-75v0,-38,-22,-72,-57,-72v-36,0,-59,35,-59,74v0,38,22,73,58,73","w":206},"e":{"d":"0,-94v0,-91,112,-143,162,-69v15,21,23,47,23,78r-149,0v0,33,23,63,57,63v25,0,48,-19,52,-42r36,0v-8,39,-42,71,-88,71v-56,1,-93,-44,-93,-101xm149,-111v0,-47,-62,-78,-95,-40v-9,11,-16,24,-18,40r113,0","w":210,"k":{"z":9,"y":16,"x":22,"w":12,"v":16,"t":11,"f":12}},"f":{"d":"98,-235v-28,-12,-37,12,-34,44r34,0r0,26r-34,0r0,165r-33,0r0,-165r-31,0r0,-26r31,0v-3,-62,12,-79,67,-74r0,30","w":122,"k":{"q":8,"o":8,"g":8,"e":8,"d":13,"c":7}},"g":{"d":"86,-197v27,1,45,13,60,30r0,-24r32,0r0,182v4,73,-83,110,-144,75v-18,-10,-26,-26,-26,-44r32,0v9,35,76,45,94,6v7,-15,13,-31,13,-51v-13,16,-33,28,-60,29v-50,1,-87,-50,-87,-102v0,-52,37,-102,86,-101xm94,-22v35,0,58,-36,57,-73v0,-39,-22,-74,-58,-74v-37,0,-58,34,-58,73v-1,39,22,74,59,74","w":217,"k":{"i":7}},"h":{"d":"94,-197v41,0,66,31,65,75r0,122r-32,0v-6,-63,22,-166,-39,-170v-29,-2,-56,27,-56,57r0,113r-32,0r0,-262r32,0r0,98v8,-20,36,-33,62,-33","w":187},"i":{"d":"35,-226r-31,0r0,-36r31,0r0,36xm35,0r-31,0r0,-191r31,0r0,191","w":71},"j":{"d":"43,-226r-32,0r0,-36r32,0r0,36xm-20,42v22,1,31,-2,31,-25r0,-208r32,0r0,212v2,42,-20,53,-63,51r0,-30","w":90,"k":{"z":11,"y":11,"x":10,"w":9,"v":11,"t":11,"s":10,"q":14,"o":8,"l":16,"i":17,"g":8,"f":11,"e":8,"d":15,"c":9,"a":9}},"k":{"d":"159,-192r-71,71r70,121r-36,0r-57,-99r-34,34r0,65r-31,0r0,-262r31,0r0,158r87,-88r41,0","w":180,"k":{"s":9,"q":17,"o":17,"g":16,"e":16,"d":27,"c":16}},"l":{"d":"43,0r-32,0r0,-262r32,0r0,262","w":72},"m":{"d":"136,-166v27,-52,128,-37,128,33r0,133r-31,0v-5,-64,21,-171,-41,-171v-63,0,-38,106,-43,171r-33,0v-6,-63,23,-170,-40,-170v-64,0,-40,105,-44,170r-32,0r0,-192r30,0r0,26v16,-40,90,-40,106,0","w":292},"n":{"d":"93,-198v86,-1,64,114,66,198r-32,0v-6,-63,23,-166,-41,-170v-31,-2,-56,30,-56,61r0,109r-30,0r0,-192r30,0r0,29v10,-20,35,-35,63,-35","w":187},"o":{"d":"185,-95v0,57,-37,101,-92,101v-55,0,-93,-44,-93,-101v0,-58,37,-102,93,-102v55,0,92,46,92,102xm35,-96v0,38,23,74,58,74v37,0,58,-35,58,-74v0,-40,-21,-73,-58,-73v-35,-1,-58,35,-58,73","w":212,"k":{"z":10,"y":15,"x":23,"w":10,"v":14,"t":10,"f":10}},"p":{"d":"91,-197v51,-2,86,50,86,103v0,53,-34,102,-86,100v-25,0,-45,-10,-60,-29r0,95r-31,0r0,-264r31,0r0,28v13,-18,32,-33,60,-33xm86,-22v36,0,58,-36,58,-74v0,-38,-23,-73,-58,-73v-35,0,-58,36,-58,74v0,38,22,73,58,73","w":203,"k":{"z":7,"y":19,"x":20,"w":9,"v":13,"t":8,"f":8}},"q":{"d":"87,-197v28,0,47,14,59,32r0,-26r31,0r0,263r-31,0r0,-97v-9,19,-30,30,-56,31v-53,1,-90,-45,-90,-100v0,-53,36,-103,87,-103xm94,-22v37,0,57,-36,57,-75v0,-38,-22,-72,-58,-72v-35,0,-58,33,-58,71v0,40,21,76,59,76","w":227,"k":{"z":14,"y":14,"x":13,"w":12,"v":13,"u":8,"t":12,"s":13,"r":7,"q":17,"p":8,"o":11,"n":8,"m":8,"l":18,"k":7,"i":18,"h":7,"g":11,"f":13,"e":11,"d":11,"c":12,"b":8,"a":12}},"r":{"d":"92,-161v-38,-1,-60,16,-60,54r0,107r-32,0r0,-191r30,0r0,34v10,-26,28,-37,62,-38r0,34","w":111},"s":{"d":"37,-145v-10,26,94,42,89,43v68,32,14,108,-50,108v-44,0,-77,-26,-76,-68r31,0v0,26,21,40,47,40v22,1,50,-13,46,-32v-8,-46,-119,-23,-119,-87v0,-53,82,-72,123,-42v16,11,23,26,23,45r-31,0v3,-40,-80,-43,-83,-7","w":180,"k":{"y":8,"x":10,"v":8}},"t":{"d":"60,-48v-3,23,17,24,35,20r0,28v-36,5,-67,2,-67,-37r0,-128r-28,0r0,-26r28,0r0,-54r32,0r0,54r35,0r0,26r-35,0r0,117","w":118,"k":{"d":11}},"u":{"d":"71,-21v32,2,57,-34,57,-67r0,-103r32,0r0,191r-31,0r0,-28v-12,20,-36,34,-65,34v-40,1,-64,-31,-64,-73r0,-124r32,0v6,63,-22,165,39,170","w":189},"v":{"d":"175,-191r-70,191r-34,0r-71,-191r35,0r53,153r52,-153r35,0","w":197,"k":{"q":9,"o":9,"g":9,"e":9,"d":9,"c":8,"a":9}},"w":{"d":"262,-191r-59,191r-32,0r-41,-147r-40,147r-32,0r-58,-191r34,0r40,146r38,-146r37,0r38,146r41,-146r34,0","w":285,"k":{"a":8}},"x":{"d":"175,0r-39,0r-50,-74r-49,74r-37,0r67,-98r-64,-93r38,0r46,68r47,-68r37,0r-64,92","w":198,"k":{"s":10,"q":18,"o":18,"g":19,"e":18,"d":18,"c":18}},"y":{"d":"25,39v30,6,38,-3,46,-32r-71,-198r36,0r53,155r53,-155r35,0r-76,213v-11,33,-35,59,-76,47r0,-30","w":199,"k":{"q":18,"o":9,"g":13,"e":10,"d":10,"c":9,"a":10}},"z":{"d":"159,0r-159,0r0,-26r112,-137r-107,0r0,-28r149,0r0,26r-112,137r117,0r0,28","w":180},"{":{"d":"82,-37v-2,51,-7,85,43,84r0,27v-50,-2,-76,-13,-76,-62v0,-49,8,-99,-49,-93r0,-27v106,12,-12,-167,125,-155r0,26v-87,-11,-7,131,-79,142v25,9,37,24,36,58","w":149},"|":{"d":"116,-120r-27,0r0,-136r27,0r0,136xm116,63r-27,0r0,-136r27,0r0,136"},"}":{"d":"76,-201v0,48,-9,100,49,93r0,27v-108,-14,17,168,-125,155r0,-27v49,6,43,-40,43,-84v0,-35,10,-48,36,-58v-38,-9,-38,-52,-35,-98v2,-34,-12,-43,-44,-44r0,-26v50,2,76,13,76,62","w":149},"~":{"d":"154,-198v25,-2,36,-21,35,-50r27,0v0,44,-21,76,-62,76v-29,6,-68,-50,-92,-50v-25,0,-36,23,-35,50r-27,0v0,-44,21,-77,62,-76v29,-6,67,52,92,50","w":241},"\u00a0":{"w":97}}});


/*
---

name: Moderna.Bold

description: MgOpen Moderna Bold built with [Cufón](http://wiki.github.com/sorccu/cufon/about)

provides: Moderna.Bold

requires: ART.Font

...
*/

/*!
 * The following copyright notice may not be removed under any circumstances.
 * 
 * Copyright:
 * Copyright (c) Magenta ltd, 2004.
 * 
 * Manufacturer:
 * Magenta ltd
 * 
 * Vendor URL:
 * http://www.magenta.gr
 * 
 * License information:
 * http://www.ellak.gr/fonts/MgOpen/license.html
 */

ART.registerFont({"w":214,"face":{"font-family":"Moderna","font-weight":700,"font-stretch":"normal","units-per-em":"360","panose-1":"2 11 8 3 0 2 0 2 0 4","ascent":"288","descent":"-72","x-height":"6","bbox":"-12 -283 335 86","underline-thickness":"18","underline-position":"-27","unicode-range":"U+0020-U+007E"},"glyphs":{" ":{"w":97},"!":{"d":"14,-73v-7,-61,-17,-118,-14,-189r54,0v2,66,-3,125,-13,189r-27,0xm54,0r-53,0r0,-53r53,0r0,53","w":79},"\"":{"d":"93,-157r-35,0r0,-99r35,0r0,99xm34,-157r-34,0r0,-99r34,0r0,99","w":117},"#":{"d":"264,-187r-13,36r-50,0r-15,43r52,0r-13,36r-52,0r-26,73r-40,0r27,-73r-44,0r-26,73r-40,0r27,-73r-51,0r13,-36r51,0r15,-43r-53,0r13,-36r53,0r26,-74r39,0r-26,74r44,0r26,-74r39,0r-26,74r50,0xm163,-151r-45,0r-16,43r45,0","w":289},"$":{"d":"3,-181v0,-43,30,-75,77,-73r0,-23r22,0r0,23v47,0,72,30,75,75r-48,0v-2,-20,-10,-33,-27,-36r0,65v52,15,81,27,81,83v0,42,-36,74,-81,73r0,34r-22,0r0,-34v-52,-6,-79,-29,-80,-84r48,0v2,25,11,39,32,45r0,-74v-46,-5,-76,-27,-77,-74xm80,-215v-25,-3,-36,32,-22,49v4,4,12,8,22,11r0,-60xm102,-33v18,-3,29,-14,29,-34v0,-17,-10,-27,-29,-33r0,67","w":207},"%":{"d":"158,-59v-1,-38,27,-67,65,-67v37,0,65,29,65,67v0,38,-27,67,-65,67v-39,0,-65,-29,-65,-67xm196,-59v0,16,10,28,27,28v17,0,27,-14,28,-28v0,-16,-11,-27,-28,-27v-16,0,-27,13,-27,27xm234,-255r-152,262r-29,0r153,-262r28,0xm0,-189v0,-38,28,-66,65,-66v38,0,64,28,64,66v0,38,-27,67,-65,67v-37,0,-64,-29,-64,-67xm93,-189v0,-15,-11,-28,-28,-28v-17,0,-29,14,-29,29v1,15,12,27,28,27v17,0,29,-14,29,-28","w":314},"&":{"d":"165,-204v-1,33,-19,46,-44,64r38,47v7,-14,11,-29,13,-44r47,0v-4,29,-14,55,-31,79r48,58r-61,0r-18,-22v-29,23,-56,34,-81,34v-41,0,-76,-42,-76,-83v0,-39,25,-59,57,-76v-14,-18,-25,-29,-26,-52v-1,-37,30,-63,68,-63v37,0,67,24,66,58xm96,-171v21,-7,37,-52,3,-54v-32,2,-21,41,-3,54xm51,-74v0,40,57,52,79,19r-48,-59v-21,13,-31,27,-31,40","w":261},"'":{"d":"50,-262v4,51,-4,96,-50,100r0,-19v18,-5,26,-16,26,-34r-26,0r0,-47r50,0","w":74},"(":{"d":"101,-262v-63,85,-64,253,0,338r-38,0v-34,-47,-63,-100,-63,-169v0,-68,28,-122,63,-169r38,0","w":126},")":{"d":"38,-262v33,47,63,100,63,169v0,68,-28,122,-63,169r-38,0v27,-46,49,-102,49,-169v0,-68,-22,-120,-49,-169r38,0","w":126},"*":{"d":"185,-192r-51,13r35,43r-31,22r-31,-45r-31,45r-31,-22r35,-43r-50,-13r11,-36r48,16r0,-50r36,0r0,50r47,-16"},"+":{"d":"203,-91r-78,0r0,91r-36,0r0,-91r-78,0r0,-36r78,0r0,-91r36,0r0,91r78,0r0,36"},",":{"d":"53,-53v-1,36,6,73,-14,92v-10,11,-23,19,-39,23r0,-21v18,-8,29,-16,29,-41r-29,0r0,-53r53,0","w":78},"-":{"d":"107,-72r-100,0r0,-49r100,0r0,49","w":136},".":{"d":"53,0r-53,0r0,-54r53,0r0,54","w":78},"\/":{"d":"164,-270r-89,303r-24,0r88,-303r25,0"},"0":{"d":"202,-123v0,66,-34,129,-94,129v-61,0,-95,-63,-95,-129v0,-68,34,-132,95,-132v60,0,94,66,94,132xm108,-213v-54,11,-57,167,0,177v54,-10,54,-167,0,-177"},"1":{"d":"51,-210v45,0,63,-9,71,-45r41,0r0,255r-50,0r0,-175r-62,0r0,-35"},"2":{"d":"108,-256v47,0,90,34,90,79v0,77,-88,84,-118,133r115,0r0,44r-178,0v12,-73,24,-72,95,-125v23,-17,34,-33,34,-50v0,-22,-19,-38,-38,-38v-26,1,-39,22,-40,49r-47,0v-1,-54,35,-92,87,-92"},"3":{"d":"159,-135v22,11,38,29,40,60v4,72,-102,110,-155,62v-18,-16,-28,-37,-28,-65r49,0v-1,26,16,44,40,44v23,0,41,-16,41,-39v0,-29,-23,-40,-57,-39r0,-35v56,9,65,-68,14,-68v-21,0,-35,17,-34,41r-48,0v-1,-50,37,-82,86,-82v43,-1,85,28,83,70v-1,26,-14,38,-31,51"},"4":{"d":"201,-55r-30,0r0,55r-49,0r0,-55r-108,0r0,-45r94,-148r63,0r0,152r30,0r0,41xm123,-94r0,-110r-70,110r70,0"},"5":{"d":"71,-154v50,-40,128,13,128,71v0,75,-100,118,-158,72v-17,-14,-26,-33,-26,-57r51,0v-1,22,18,35,38,35v25,0,43,-20,43,-47v0,-41,-55,-62,-76,-29r-47,-2r17,-137r147,0r0,43r-111,0"},"6":{"d":"15,-118v0,-97,78,-175,155,-119v16,11,23,28,23,47r-52,0v-5,-27,-43,-34,-59,-9v-8,13,-14,28,-14,51v51,-42,135,-2,132,66v-2,51,-39,91,-91,90v-65,-1,-94,-55,-94,-126xm69,-81v0,25,17,48,42,48v24,0,39,-21,39,-45v0,-25,-18,-46,-42,-46v-24,0,-39,19,-39,43"},"7":{"d":"53,0v11,-94,38,-137,89,-204r-125,0r0,-44r181,0r0,38v-51,59,-80,117,-92,210r-53,0"},"8":{"d":"107,-256v70,0,119,88,55,122v21,11,37,31,37,60v1,46,-43,82,-91,82v-52,0,-93,-31,-93,-79v0,-31,15,-51,38,-63v-18,-12,-30,-27,-31,-52v0,-39,41,-70,85,-70xm71,-183v0,18,15,30,36,30v20,0,36,-12,36,-30v0,-19,-15,-32,-36,-32v-20,0,-36,13,-36,32xm68,-73v0,21,15,38,39,38v24,0,39,-14,39,-37v0,-22,-16,-42,-39,-41v-22,0,-40,16,-39,40"},"9":{"d":"106,-255v66,0,93,55,93,127v0,96,-73,173,-150,119v-16,-11,-24,-29,-27,-49r51,0v3,25,45,35,60,11v8,-13,13,-29,13,-53v-49,42,-131,3,-131,-67v0,-52,38,-88,91,-88xm67,-168v0,25,16,45,41,45v24,0,37,-17,37,-41v0,-26,-17,-50,-41,-50v-23,0,-38,23,-37,46"},":":{"d":"54,-137r-54,0r0,-53r54,0r0,53xm54,0r-54,0r0,-54r54,0r0,54","w":78},";":{"d":"54,-137r-54,0r0,-53r54,0r0,53xm54,-54v4,59,-2,107,-54,116r0,-21v21,-8,29,-16,30,-41r-30,0r0,-54r54,0","w":79},"<":{"d":"205,-5r-196,-86r0,-35r196,-87r0,39r-145,65r145,65r0,39"},"=":{"d":"207,-130r-199,0r0,-35r199,0r0,35xm207,-53r-199,0r0,-35r199,0r0,35"},">":{"d":"205,-91r-196,86r0,-39r145,-65r-145,-65r0,-39r196,87r0,35"},"?":{"d":"91,-270v67,0,115,74,70,124v-17,19,-49,36,-48,70r-48,0r0,-26v-5,-22,63,-64,58,-86v0,-21,-16,-38,-37,-37v-24,0,-35,20,-35,44r-51,0v-1,-52,40,-89,91,-89xm116,0r-53,0r0,-53r53,0r0,53","w":203},"@":{"d":"168,27v40,0,81,-15,107,-33r14,20v-30,23,-72,40,-121,41v-97,2,-167,-57,-168,-149v-1,-97,82,-170,181,-170v86,0,149,50,150,131v1,61,-42,114,-102,114v-29,0,-33,-5,-38,-29v-25,46,-118,35,-111,-32v-7,-67,83,-137,129,-77r9,-18r32,0r-28,113v0,9,9,14,17,14v37,0,60,-45,59,-84v0,-65,-46,-103,-113,-103v-85,0,-154,61,-154,141v0,73,59,123,137,121xm118,-84v0,37,34,50,56,27v17,-18,19,-46,26,-73v0,-13,-16,-24,-31,-24v-30,0,-51,39,-51,70","w":356},"A":{"d":"251,0r-57,0r-18,-54r-102,0r-17,54r-57,0r94,-262r62,0xm162,-98r-37,-112r-37,112r74,0","w":277,"k":{"y":54,"w":41,"v":54,"t":28,"s":12,"q":16,"o":17,"j":8,"g":17,"f":23,"e":17,"d":24,"c":17,"a":8,"Y":74,"W":62,"V":76,"U":17,"T":71,"S":22,"Q":28,"O":28,"J":26,"G":28,"C":28}},"B":{"d":"207,-195v0,26,-13,45,-32,53v22,8,41,34,41,63v0,49,-44,79,-96,79r-120,0r0,-262v88,2,207,-21,207,67xm153,-189v0,-38,-62,-27,-102,-28r0,56v41,-2,102,11,102,-28xm160,-81v0,-44,-63,-34,-109,-35r0,69v45,-1,109,10,109,-34","w":243,"k":{"Y":27,"X":23,"W":17,"V":20,"T":12,"A":16}},"C":{"d":"128,-40v35,-1,57,-22,67,-50r57,0v-10,57,-60,98,-125,98v-74,1,-127,-63,-127,-139v0,-111,120,-179,208,-113v23,17,37,40,42,68r-57,0v-8,-26,-32,-46,-64,-46v-46,-1,-74,42,-74,90v0,48,27,93,73,92","w":270,"k":{"z":10,"y":8,"x":10,"v":8,"Z":15,"Y":36,"X":36,"W":21,"V":26,"T":20,"J":9,"A":27}},"D":{"d":"224,-136v0,79,-57,136,-136,136r-88,0r0,-262r104,0v71,-2,120,56,120,126xm170,-131v0,-44,-26,-87,-67,-86r-50,0r0,170v72,8,117,-18,117,-84","w":250,"k":{"x":8,"Z":22,"Y":38,"X":40,"W":22,"V":27,"T":23,"J":10,"A":31}},"E":{"d":"197,0r-197,0r0,-262r190,0r0,45r-137,0r0,56r126,0r0,45r-126,0r0,68r144,0r0,48","w":213,"k":{"y":22,"w":18,"v":22,"t":10,"f":9,"T":8,"Q":9,"O":9,"J":8,"G":8,"C":9}},"F":{"d":"183,-217r-130,0r0,60r114,0r0,45r-114,0r0,112r-53,0r0,-262r183,0r0,45","w":201,"k":{"z":22,"y":28,"x":39,"w":25,"v":28,"u":12,"t":21,"s":21,"r":12,"q":14,"p":12,"o":15,"n":12,"m":12,"g":15,"f":23,"e":15,"d":17,"c":15,"a":21,"T":8,"S":9,"Q":12,"O":12,"J":101,"G":12,"C":12,"A":48}},"G":{"d":"55,-131v0,67,65,119,118,75v12,-11,20,-25,24,-41r-55,0r0,-45r110,0r0,142r-37,0r-6,-31v-23,25,-52,39,-85,39v-74,1,-124,-64,-124,-140v0,-109,116,-177,205,-114v23,16,37,37,44,65r-58,0v-8,-22,-32,-41,-61,-41v-47,0,-75,44,-75,91","w":273,"k":{"Y":31,"W":16,"V":21,"T":15}},"H":{"d":"217,0r-54,0r0,-117r-109,0r0,117r-54,0r0,-262r54,0r0,97r109,0r0,-97r54,0r0,262","w":246},"I":{"d":"65,0r-54,0r0,-262r54,0r0,262","w":94},"J":{"d":"83,-37v24,-1,31,-17,31,-43r0,-182r54,0r0,186v-1,59,-24,84,-83,84v-66,0,-85,-34,-85,-105r52,0v-3,31,1,62,31,60","w":196,"k":{"A":12}},"K":{"d":"233,0r-65,0r-86,-120r-28,28r0,92r-54,0r0,-262r54,0r0,108r103,-108r67,0r-104,106","w":258,"k":{"y":58,"w":45,"v":58,"u":15,"t":32,"s":24,"q":28,"o":32,"j":8,"g":31,"f":23,"e":31,"d":32,"c":32,"a":17,"Y":15,"W":11,"V":13,"T":16,"S":33,"Q":43,"O":44,"J":33,"G":43,"C":44}},"L":{"d":"183,0r-183,0r0,-262r54,0r0,214r129,0r0,48","w":197,"k":{"y":42,"w":32,"v":42,"t":21,"j":8,"f":23,"d":11,"Y":74,"W":52,"V":63,"T":71,"S":9,"Q":17,"O":18,"J":14,"G":17,"C":18}},"M":{"d":"264,0r-51,0r0,-212r-53,212r-56,0r-54,-212r0,212r-50,0r0,-262r80,0r52,199r52,-199r80,0r0,262","w":294},"N":{"d":"216,0r-56,0r-107,-180r0,180r-53,0r0,-262r56,0r107,180r0,-180r53,0r0,262","w":245},"O":{"d":"258,-131v0,78,-53,139,-129,139v-76,0,-129,-62,-129,-139v0,-78,53,-139,129,-139v76,0,129,62,129,139xm55,-131v0,48,28,91,74,91v45,0,73,-43,73,-91v0,-48,-28,-91,-73,-91v-48,0,-74,43,-74,91","w":283,"k":{"Z":17,"Y":37,"X":37,"W":21,"V":26,"T":21,"J":8,"A":27}},"P":{"d":"200,-177v0,46,-31,83,-79,83r-67,0r0,94r-54,0r0,-262r117,0v49,-1,83,36,83,85xm148,-177v0,-43,-48,-42,-94,-40r0,76v43,0,94,7,94,-36","w":217,"k":{"d":8,"Y":24,"X":24,"W":12,"V":16,"T":8,"J":39,"A":43}},"Q":{"d":"129,-270v113,0,166,148,100,230r29,28r-29,30r-32,-29v-87,54,-197,-15,-197,-120v0,-78,53,-139,129,-139xm54,-130v0,59,45,107,105,83r-27,-28r29,-30r28,28v30,-52,7,-145,-58,-145v-47,0,-77,44,-77,92","w":284,"k":{"Y":36,"W":20,"V":25,"T":21}},"R":{"d":"206,-60v1,21,-2,44,11,52r0,8r-59,0v-20,-30,13,-111,-47,-102r-57,0r0,102r-54,0r0,-262r127,0v48,-2,85,26,85,72v0,30,-16,56,-40,64v27,10,34,27,34,66xm159,-183v0,-46,-61,-32,-105,-34r0,69v45,-1,104,9,105,-35","w":245,"k":{"d":10,"Y":26,"W":15,"V":19,"T":10,"J":16}},"S":{"d":"153,-185v0,-46,-92,-55,-96,-8v-11,35,119,47,116,52v27,12,41,32,41,61v0,83,-117,112,-178,65v-21,-17,-34,-39,-36,-67r54,0v0,49,103,60,108,9v11,-35,-118,-49,-116,-53v-27,-12,-41,-32,-41,-60v0,-79,113,-107,169,-62v21,16,31,37,31,63r-52,0","w":237,"k":{"Y":30,"X":22,"W":18,"V":22,"T":14,"A":15}},"T":{"d":"210,-215r-78,0r0,215r-55,0r0,-215r-77,0r0,-47r210,0r0,47","w":231,"k":{"z":36,"y":32,"x":33,"w":30,"v":31,"u":25,"t":18,"s":45,"r":25,"q":44,"p":25,"o":45,"n":25,"m":25,"g":45,"f":20,"e":46,"d":48,"c":46,"a":45,"T":8,"S":9,"Q":18,"O":18,"J":72,"G":17,"C":18,"A":66}},"U":{"d":"106,-40v32,0,51,-26,50,-60r0,-162r55,0r0,168v1,59,-47,102,-106,102v-59,0,-105,-43,-105,-102r0,-168r54,0r0,162v-1,34,21,60,52,60","w":240,"k":{"A":20}},"V":{"d":"232,-262r-89,262r-53,0r-90,-262r60,0r56,196r57,-196r59,0","w":258,"k":{"z":27,"y":22,"x":24,"w":21,"v":22,"u":16,"t":18,"s":37,"r":16,"q":37,"p":16,"o":39,"n":16,"m":15,"g":38,"f":18,"e":40,"d":41,"c":39,"a":37,"T":8,"S":21,"Q":26,"O":26,"J":54,"G":26,"C":26,"A":75}},"W":{"d":"335,-262r-75,262r-52,0r-41,-201r-40,201r-52,0r-75,-262r56,0r45,185r38,-185r57,0r38,185r45,-185r56,0","w":362,"k":{"z":23,"y":19,"x":21,"w":17,"v":19,"u":13,"t":15,"s":32,"r":12,"q":32,"p":12,"o":33,"n":12,"m":12,"g":32,"f":15,"e":34,"d":36,"c":33,"a":32,"T":8,"S":19,"Q":23,"O":23,"J":47,"G":22,"C":23,"A":63}},"X":{"d":"232,0r-62,0r-54,-91r-54,91r-62,0r82,-134r-82,-128r62,0r54,91r53,-91r63,0r-82,127","w":258,"k":{"y":37,"w":36,"v":37,"u":13,"t":28,"s":21,"q":25,"o":28,"g":28,"f":22,"e":28,"d":29,"c":28,"a":14,"T":8,"S":29,"Q":39,"O":39,"J":30,"G":38,"C":39}},"Y":{"d":"217,-262r-79,164r0,98r-54,0r0,-98r-84,-164r54,0r57,113r51,-113r55,0","w":241,"k":{"z":34,"y":30,"x":31,"w":28,"v":30,"u":23,"t":25,"s":48,"r":23,"q":49,"p":23,"o":52,"n":23,"m":23,"g":50,"f":25,"e":53,"d":53,"c":52,"a":48,"T":8,"S":25,"Q":33,"O":33,"J":72,"G":33,"C":33,"A":67}},"Z":{"d":"208,-217r-143,170r141,0r0,47r-206,0r0,-46r143,-169r-140,0r0,-47r205,0r0,45","w":234,"k":{"y":18,"w":17,"v":18,"t":14,"q":9,"o":11,"g":10,"f":14,"e":10,"d":12,"c":11,"T":8,"S":9,"Q":17,"O":17,"J":15,"G":16,"C":17}},"[":{"d":"90,78r-90,0r0,-340r90,0r0,38r-41,0r0,264r41,0r0,38","w":115},"\\":{"d":"113,33r-24,0r-89,-303r24,0","w":137},"]":{"d":"90,78r-90,0r0,-38r42,0r0,-264r-42,0r0,-38r90,0r0,340","w":115},"^":{"d":"163,-217r-24,0r-32,-39r-31,39r-25,0r35,-66r42,0"},"_":{"d":"199,86r-183,0r0,-37r183,0r0,37"},"`":{"d":"49,-170r-49,0v-4,-50,3,-96,49,-100r0,19v-18,6,-25,12,-26,34r26,0r0,47","w":74},"a":{"d":"170,-36v-2,17,11,21,11,36r-54,0v-3,-8,-5,-16,-6,-24v-24,47,-127,39,-121,-29v4,-51,28,-58,89,-64v21,-3,31,-9,31,-18v-3,-32,-67,-31,-64,5r-49,0v0,-44,32,-71,78,-71v40,0,85,22,85,57r0,108xm77,-33v31,0,49,-25,44,-62v-32,9,-71,12,-71,39v0,14,12,23,27,23","w":203,"k":{"y":9,"v":8}},"b":{"d":"187,-97v0,53,-29,104,-79,103v-28,0,-46,-14,-58,-32r0,26r-50,0r0,-262r50,0r0,92v11,-18,31,-30,58,-31v50,-1,79,51,79,104xm136,-97v0,-30,-15,-59,-42,-59v-28,-1,-44,30,-44,60v0,29,16,58,43,58v27,0,43,-29,43,-59","w":213,"k":{"y":12,"x":18,"v":10}},"c":{"d":"98,-36v22,-1,38,-18,43,-37r54,0v-8,44,-44,79,-95,79v-57,0,-100,-43,-100,-101v0,-85,94,-137,160,-86v17,13,29,31,35,55r-56,0v-20,-60,-86,-25,-86,28v0,32,16,62,45,62","k":{"y":11,"x":20,"v":10}},"d":{"d":"79,-201v27,1,47,13,58,31r0,-92r50,0r0,262r-49,0r0,-26v-14,22,-34,32,-59,32v-51,1,-79,-50,-79,-103v0,-53,28,-105,79,-104xm137,-96v0,-30,-16,-60,-43,-60v-27,0,-43,29,-43,59v0,30,15,60,42,60v27,0,44,-29,44,-59","w":216},"e":{"d":"0,-96v0,-65,32,-110,93,-110v68,0,91,48,91,123r-130,0v-5,46,58,65,75,24r52,0v-10,37,-41,66,-87,66v-56,0,-94,-45,-94,-103xm130,-117v5,-39,-42,-54,-66,-31v-7,7,-10,18,-10,31r76,0","w":209,"k":{"y":14,"x":17,"w":9,"v":13}},"f":{"d":"111,-222v-23,-1,-38,0,-33,28r33,0r0,36r-33,0r0,158r-50,0r0,-158r-28,0r0,-36r28,0v-4,-62,25,-74,83,-69r0,41","w":136},"g":{"d":"0,-98v0,-54,31,-104,82,-103v24,0,43,11,56,31r0,-24r50,0r0,181v-1,67,-27,91,-95,92v-47,1,-83,-20,-87,-60r57,0v4,13,16,18,32,18v39,1,43,-23,42,-64v-11,17,-28,26,-54,27v-51,1,-83,-45,-83,-98xm52,-100v0,29,16,57,43,57v26,0,42,-27,42,-56v1,-29,-16,-57,-43,-57v-27,0,-42,27,-42,56","w":217},"h":{"d":"111,-200v83,1,61,117,63,200r-52,0r0,-101v0,-28,-7,-56,-30,-56v-59,0,-36,96,-40,157r-52,0r0,-262r52,0r0,90v14,-16,33,-28,59,-28","w":202},"i":{"d":"55,-215r-51,0r0,-47r51,0r0,47xm55,0r-51,0r0,-194r51,0r0,194","w":84},"j":{"d":"63,-215r-52,0r0,-47r52,0r0,47xm-12,36v14,0,23,-1,23,-14r0,-216r52,0r0,225v1,42,-33,52,-75,48r0,-43","w":93},"k":{"d":"179,0r-62,0r-47,-82r-19,20r0,62r-51,0r0,-262r51,0r0,137r61,-69r63,0r-68,72","w":199,"k":{"s":9,"q":13,"o":16,"g":15,"e":15,"d":23,"c":16}},"l":{"d":"62,0r-51,0r0,-262r51,0r0,262","w":93},"m":{"d":"157,-171v26,-51,119,-30,119,35r0,136r-51,0r0,-125v0,-15,-12,-30,-28,-29v-58,3,-26,99,-34,154r-51,0r0,-125v0,-17,-10,-30,-28,-29v-56,5,-26,99,-33,154r-51,0r0,-194r49,0r0,22v20,-37,88,-36,108,1","w":304},"n":{"d":"108,-201v40,-1,67,30,66,69r0,132r-52,0r0,-117v1,-22,-11,-40,-31,-39v-59,4,-34,97,-39,156r-52,0r0,-194r50,0r0,23v11,-17,32,-30,58,-30","w":202},"o":{"d":"198,-96v0,59,-40,102,-99,102v-58,0,-99,-43,-99,-102v0,-59,40,-105,99,-105v59,0,99,46,99,105xm53,-97v0,31,16,60,45,60v30,1,46,-28,46,-60v0,-32,-16,-60,-46,-60v-30,0,-45,29,-45,60","w":225,"k":{"y":15,"x":23,"w":10,"v":14}},"p":{"d":"187,-98v0,53,-29,105,-79,104v-27,0,-47,-12,-58,-30r0,101r-50,0r0,-271r50,0r0,26v12,-18,31,-33,58,-33v50,0,79,50,79,103xm136,-96v0,-30,-14,-60,-42,-60v-29,0,-44,28,-44,58v0,30,16,61,44,60v26,0,42,-29,42,-58","w":213,"k":{"y":18,"x":18,"v":11}},"q":{"d":"79,-201v26,1,46,15,59,31r0,-24r49,0r0,271r-50,0r0,-102v-14,21,-34,31,-58,31v-49,1,-79,-49,-79,-102v0,-53,29,-107,79,-105xm138,-96v0,-29,-17,-60,-44,-60v-28,0,-43,29,-43,58v-1,30,16,61,42,61v27,0,45,-29,45,-59","w":216},"r":{"d":"112,-146v-39,0,-59,7,-60,43r0,103r-52,0r0,-194r48,0r0,34v13,-24,29,-39,64,-39r0,53","w":132},"s":{"d":"85,-161v-39,3,-35,28,3,38v60,15,84,17,88,64v6,63,-101,86,-148,49v-18,-13,-28,-30,-28,-53r52,0v-3,33,68,42,73,10v2,-21,-85,-32,-88,-37v-20,-10,-31,-26,-31,-48v-2,-60,94,-84,138,-46v15,12,24,28,27,49r-51,0v-1,-17,-17,-28,-35,-26","w":200,"k":{"y":11,"x":11,"v":10}},"t":{"d":"77,-50v0,14,15,13,30,13r0,38v-48,6,-82,0,-82,-54r0,-105r-25,0r0,-36r25,0r0,-53r52,0r0,53r30,0r0,36r-30,0r0,108","w":132},"u":{"d":"82,-38v61,0,35,-96,40,-156r51,0r0,194r-50,0r0,-23v-14,16,-36,28,-63,28v-84,0,-55,-118,-60,-199r51,0v7,55,-23,156,31,156","w":202},"v":{"d":"192,-194r-69,194r-54,0r-69,-194r58,0r39,139r40,-139r55,0","k":{"q":8,"o":9,"g":8,"e":10,"d":8,"c":9,"a":8}},"w":{"d":"274,-194r-54,194r-52,0r-31,-139r-30,139r-52,0r-55,-194r53,0r31,135r27,-135r53,0r28,135r32,-135r50,0","w":298},"x":{"d":"192,0r-61,0r-36,-62r-35,62r-60,0r66,-99r-63,-95r59,0r34,59r34,-59r58,0r-61,94","k":{"s":10,"q":14,"o":18,"g":17,"e":17,"d":14,"c":18}},"y":{"d":"31,28v21,5,41,0,39,-24v-15,-51,-50,-143,-70,-198r57,0r41,140r39,-140r55,0r-77,222v-8,37,-40,49,-84,42r0,-42","k":{"s":7,"q":16,"o":10,"g":12,"e":10,"d":8,"c":10,"a":8}},"z":{"d":"172,0r-172,0r0,-40r107,-113r-101,0r0,-41r162,0r0,42r-104,109r108,0r0,43","w":195},"{":{"d":"54,-95v73,4,-4,140,76,131r0,38v-75,5,-88,-37,-82,-107v3,-33,-15,-41,-48,-43r0,-37v80,10,25,-96,60,-132v12,-13,36,-18,70,-18r0,37v-48,-9,-41,44,-39,78v2,32,-12,44,-37,53","w":155},"|":{"d":"37,-120r-37,0r0,-135r37,0r0,135xm37,63r-37,0r0,-136r37,0r0,136","w":62},"}":{"d":"85,-202v0,49,-12,93,45,89r0,37v-80,-10,-23,95,-59,131v-12,13,-37,19,-71,19r0,-38v46,4,38,-38,38,-77v0,-32,12,-45,38,-54v-40,-7,-39,-50,-36,-95v2,-27,-12,-36,-40,-36r0,-37v56,2,85,10,85,61","w":154},"~":{"d":"155,-205v23,0,30,-20,30,-46r36,0v0,47,-21,80,-65,81v-27,6,-69,-45,-89,-45v-24,0,-31,20,-31,45r-36,0v-1,-46,22,-81,65,-81v26,-5,70,47,90,46","w":245},"\u00a0":{"w":97}}});


