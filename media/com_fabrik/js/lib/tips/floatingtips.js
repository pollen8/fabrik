/*
---
description: Class for creating floating balloon tips that nicely appears when hovering an element.

license: MIT-style

authors:
- Lorenzo Stanco

requires:
- core/1.3: '*'

provides: [FloatingTips]

...
*/

var FloatingTips = new Class({

	Implements: [Options, Events],

	options: {
		position: 'top',
		center: true,
		content: 'title',
		html: false,
		balloon: true,
		arrowSize: 32,
		arrowOffset: 6,
		distance: 3,
		motion: 6,
		motionOnShow: true,
		motionOnHide: true,
		showOn: 'mouseenter',
		hideOn: 'mouseleave',
		showDelay: 0,
		hideDelay: 0,
		className: 'floating-tip',
		offset: {x: 0, y: 0},
		fx: { 'duration': 'short' },
		hideFn: function(e, el) { this.hide(e, el);e.stop() },
		showFn: function(e, el) { this.show(el);e.stop(); }
	},

	initialize: function(elements, options) {
		this.setOptions(options);
		if (!['top', 'right', 'bottom', 'left', 'inside'].contains(this.options.position)) this.options.position = 'top';
		if (elements) this.attach(elements);
		return this;
	},

	attach: function(elements) {
		var s = this;
		this.selector = elements;
		this.elements = $$(elements);
		this.elements.each(function(e) {
			e.addEvent(this.options.showOn, this.options.showFn.bindWithEvent(this, [e]));
			e.addEvent(this.options.hideOn, this.options.hideFn.bindWithEvent(this, [e]));
			e.getParent().addEvent(this.options.hideOn, this.options.hideFn.bindWithEvent(this, [e.getParent()]));
			Fabrik.addEvent('fabrik.tip.show', function(trigger){
				var tip = e.retrieve('floatingtip');
				if (trigger !== e && tip) {
					this._animate(tip, 'out');
				}
			}.bind(this));
		}.bind(this));
		
		return this;
	},

	show: function(element) {
		var old = element.retrieve('floatingtip');
		if (old) if (old.getStyle('opacity') == 1) { clearTimeout(old.retrieve('timeout')); return this; }
		var tip = this._create(element);
		if (tip == null) return this;
		element.store('floatingtip', tip);
		this._animate(tip, 'in');
		this.fireEvent('show', [tip, element]);
		Fabrik.fireEvent('fabrik.tip.show', [element]);
		return this;
	},
	
	hide: function(e, element) {
		if (!element) {
			return;
		}
		var t = e.target.getParent('.' + this.options.className + '-wrapper');
		var tt = e.target.getParent(this.selector);
		var classTest = false;
		if (e.target.get('tag') !== 'svg') {
			if (typeOf(this.selector) === 'string') {
				classTest = e.target.hasClass(this.selector.replace('.', ''));
			} else {
				classTest = e.target.hasClass(this.selector['class']);
			}
		}
	
		if (this.elements.contains(t) || this.elements.contains(tt) || classTest) {
			//stops the element from being hidden if mouse over trigger
			return this;
		}
		//for click triggers like group by moving out of trigger element shouldnt hide list.
		if (this.options.showOn === 'click') {
			if (!e.target.getParent('.' + this.options.className)) {
				//not moving out of the main menu
				return;
			}
		}
		var tip = element.retrieve('floatingtip');
		if (!tip) return this;
		this._animate(tip, 'out');
		this.fireEvent('hide', [tip, element]);
		return this;
	},
	
	_create: function(elem) {
		var o = Object.clone(this.options);
		elOpts = elem.retrieve('options', JSON.decode(elem.get('opts')));
		o = Object.merge(o, elOpts);
		elem.removeProperty('opts');
		var oc = o.content;
		var opos = o.position;
		if (oc == 'title') {
			oc = 'floatingtitle';
			if (!elem.get('floatingtitle')) elem.setProperty('floatingtitle', elem.get('title'));
			elem.set('title', '');
		}
		var cnt;
		switch (typeOf(oc)) {
		case 'string':
			cnt = elem.get(oc)
			break;
		case 'element':
			cnt = oc;
			break;
		default:
			cnt = oc(elem);
			break;
		}
		var cwr = new Element('div').addClass(o.className).setStyles({'margin': 0, 'position': 'absolute'});
		var positioner = new Element('div').addClass(o.className + '-positioner').setStyles({'position': 'relative'}).adopt(cwr);
		var tip = new Element('div').addClass(o.className + '-wrapper').setStyles({ 'margin': 0, 'padding': 0, 'z-index': cwr.getStyle('z-index') }).adopt(positioner);
		
		if (cnt) { 
			if (o.html) cwr.set('html', typeof(cnt) == 'string' ? cnt : cnt.get('html')); 
			else cwr.set('text', cnt); 
		} else { 
			return null;
		}
		
		cwr.store('trigger', elem);
		var body = document.id(document.body);
		tip.setStyles({ 'position': 'absolute', 'opacity': 0 }).inject(body);
		
		if (o.balloon && !Browser.ie6) {
			
			var trg = new Element('div').addClass(o.className + '-triangle').setStyles({ 'margin': 0, 'padding': 0, 'position': 'absolute' });
			trg.setStyle('z-index', tip.getStyle('z-index') -1);
			var trgSt = {};
		
			var r = 0;
			// @TODO - margin offsets only ok if arrowSize  = 32.
			trgSt['height'] = '32px';
			trgSt['width'] = '32px';
			switch (opos) {
			case 'inside': 
			case 'top': 
				r = 270;
				cwr.setStyle('bottom', '0');
				trgSt['bottom'] = '-30px';
				trgSt['left'] = o.center ? cwr.getSize().x / 2 - o.arrowSize / 2 : o.arrowOffset;
				break;
				
				
			case 'right': 
				r = 0;
				trgSt['top'] = o.center ?  cwr.getSize().y / 2 - o.arrowSize / 2 : o.arrowOffset;
				cwr.setStyle('left', '10px');
				trgSt['margin-left'] = '-20px';
				break;
				
			case 'bottom':
				r = 90;
				trgSt['margin-top'] = '-15px';
				trgSt['left'] = o.center ? cwr.getSize().x / 2 - o.arrowSize / 2 : o.arrowOffset;
				cwr.setStyle('top', '5px');
				break;
			case 'left': 
				r = 180; 
				trgSt['right'] = '-25px';
				trgSt['top'] = o.center ?  cwr.getSize().y / 2 - o.arrowSize / 2 : o.arrowOffset;
				cwr.setStyle('right', '5px');
				break;
			}
			var scale = o.arrowSize/32;
			var borderSize = cwr.getStyle('border-width').split(' ')[0].toInt();
			var bg = cwr.getStyle('background-color');
			if (bg === 'transparent') {
				bg = '#fff';
			}
	
			var arrowStyle = {
					size: {width: 32, height: 32},
					scale: scale, 
					rotate: r,
					fill: {
						color: [ bg,  bg]
					}
				};
			if (borderSize !== 0) {
				arrowStyle.stroke = {
					'color': cwr.getStyle('border-color').split(' ')[0],
					'width': borderSize,
				};
			}
			arrowStyle.translate = {x: 0, y: 0};
			if (opos === 'bottom') {
				arrowStyle.translate = {x: -11, y: 0};
			}
			var shadow = cwr.getStyle('box-shadow');
			var shadowColor, shadowX, shadowY, bits;
			if (shadow !== 'none' && shadow !== '') {
				if (shadow.contains('rgb')) {
					bits = shadow.split(')');
					shadowColor = bits[0] + ')';
					bits = bits[1].trim().split(' ');
					shadowX = bits[0].toInt();
					shadowY = bits[1].toInt();
				} else {
					bits = shadow.split(' ');
					shadowColor = Browser.ie ? bits[3] : bits[0];
					shadowX = bits[1].toInt();
					shadowY = bits[2].toInt();
				}
				arrowStyle.shadow = {
					'color': shadowColor,
					'translate': {x: -1 * (shadowX + arrowStyle.translate.x), y: shadowY + arrowStyle.translate.y},
				};
			}
			var arrow = Fabrik.iconGen.create(icon.arrowleft, arrowStyle);
			arrow.inject(trg);
			trg.setStyles(trgSt).inject(tip.getElement('.' + o.className + '-positioner'), (opos == 'top' || opos == 'inside') ? 'bottom' : 'top');
			
		}
		
		//var tipSz = tip.getSize(), trgC = elem.getCoordinates(body);
		var tipSz = cwr.getSize(), trgC = elem.getCoordinates(body);
		var pos = { x: trgC.left + o.offset.x, y: trgC.top + o.offset.y };
		
		if (opos == 'inside') {
			tip.setStyles({ 'width': tip.getStyle('width'), 'height': tip.getStyle('height') });
			elem.setStyle('position', 'relative').adopt(tip);
			pos = { x: o.offset.x, y: o.offset.y };
		} else {
			switch (opos) {
				//case 'top':     pos.y -= tipSz.y + o.distance; break;
			case 'top':     pos.y -=  o.distance; break;
				case 'right': 	pos.x += trgC.width + o.distance; break;
				case 'bottom': 	pos.y += trgC.height + o.distance; break;
				//case 'left': 	pos.x -= tipSz.x + o.distance; break;
				case 'left': 	pos.x -= o.distance; break;
			}
		}
		
		if (o.center) {
			switch (opos) {
				case 'top': case 'bottom': pos.x += (trgC.width / 2 - tipSz.x / 2); break;
				case 'left': case 'right': pos.y += (trgC.height / 2 - tipSz.y / 2); break;
				case 'inside':
					pos.x += (trgC.width / 2 - tipSz.x / 2);
					pos.y += (trgC.height / 2 - tipSz.y / 2); break;
			}
		}
		
		tip.set('morph', o.fx).store('position', pos);
		tip.setStyles({ 'top': pos.y, 'left': pos.x });
		elem.store('options', o);
		tip.store('options', o);
		
		/*tip.addEvent('mouseleave', function(e){
			this.hide(e, elem, 'tip');
		}.bind(this));
		*/

		tip.addEvent('mouseleave', this.options.hideFn.bindWithEvent(this, [elem, 'tip']));
		return tip;
		
	},
	
	_animate: function(tip, d) {
		
		clearTimeout(tip.retrieve('timeout'));
		tip.store('timeout', (function(t) { 
			
			var o = tip.retrieve('options'), din = (d == 'in');
			var m = { 'opacity': din ? 1 : 0 };
			
			if ((o.motionOnShow && din) || (o.motionOnHide && !din)) {
				var pos =  t.retrieve('position');
				if (!pos) return;
				switch (o.position) {
					case 'inside': 
					case 'top':		m['top']  = din ? [pos.y - o.motion, pos.y] : pos.y - o.motion; break;
					case 'right': 	m['left'] = din ? [pos.x + o.motion, pos.x] : pos.x + o.motion; break;
					case 'bottom': 	m['top']  = din ? [pos.y + o.motion, pos.y] : pos.y + o.motion; break;
					case 'left': 	m['left'] = din ? [pos.x - o.motion, pos.x] : pos.x - o.motion; break;
				}
			}
			
			t.morph(m);
			if (!din) t.get('morph').chain(function() { this.dispose(); }.bind(t)); 
			
		}).delay((d == 'in') ? this.options.showDelay : this.options.hideDelay, this, tip));
		
		return this;
		
	}

});
