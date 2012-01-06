/*jshint mootools: true */
/*global Fabrik:true, fconsole:true, Joomla:true, CloneObject:true, $A:true, $H:true,unescape:true,Asset:true,FloatingTips:true,head:true,IconGenerator:true */

Request.HTML = new Class({

	Extends: Request,
	
	options: {
		update: false,
		append: false,
		evalScripts: true,
		filter: false,
		headers: {
			Accept: 'text/html, application/xml, text/xml, */*'
		}
	},
	success: function (text) {
		
		var options = this.options, response = this.response;
		var srcs = text.match(/<script[^>]*>([\s\S]*?)<\/script>/gi);
		var urls = [];
		if (typeOf(srcs) !== 'null') {
			for (var x = 0; x < srcs.length; x++) {
				if (srcs[x].contains('src="')) {
					var m = srcs[x].match(/src=\"([\s\S]*?)\"/);
					if (m[1]) {
						urls.push(m[1]);
					}
				}
			}
			var scriptadd = "head.js('" + urls.join("','") + "');\n";
			Browser.exec(scriptadd);
		}
		response.html = text.stripScripts(function (script) {
			response.javascript = script;
		});
		
		var match = response.html.match(/<body[^>]*>([\s\S]*?)<\/body>/i);
		if (match) {
			response.html = match[1];
		}
		var temp = new Element('div').set('html', response.html);

		response.tree = temp.childNodes;
		response.elements = temp.getElements('*');

		if (options.filter) {
			response.tree = response.elements.filter(options.filter);
		}
		if (options.update) {
			document.id(options.update).empty().set('html', response.html);
		}
		else if (options.append) {
			document.id(options.append).adopt(temp.getChildren());
		}
		if (options.evalScripts) {
			//response.javascript = "(function () {"+response.javascript+"}).delay(6000)";
			Browser.exec(response.javascript);
		}

		this.onSuccess(response.tree, response.elements, response.html, response.javascript);
	}
});

/**
 * keeps the element posisiton in the center even when scroll/resizing
 */

Element.implement({
	keepCenter: function () {
		this.makeCenter();
		window.addEvent('scroll', function () {
			this.makeCenter();
		}.bind(this));
		window.addEvent('resize', function () {
			this.makeCenter();
		}.bind(this));
	},
	makeCenter: function () {
		var l = window.getWidth() / 2 - this.getWidth() / 2;
		var t = window.getScrollTop() + (window.getHeight() / 2 - this.getHeight() / 2);
		this.setStyles({left: l, top: t});
	}
});

/**
 * loading aninimation class, either inline next to an element or 
 * full screen
 */

var Loader = new Class({
	
	initialize: function (options) {
		this.spinners = {};			
	},
	
	getSpinner: function (inline, msg) {
		msg = msg ? msg : 'loading';
		if (typeOf(document.id(inline)) === 'null') {
			inline = false;
		}
		inline = inline ? inline : false;
		var target = inline ? inline : document.body;
		if (!this.spinners[inline]) {
			this.spinners[inline] = new Spinner(target, {'message': msg});
		}
		return this.spinners[inline];
	},
	
	start: function (inline, msg) {
		this.getSpinner(inline, msg).position().show();
	},
	
	stop: function (inline, msg, keepOverlay) {
		var s = this.getSpinner(inline, msg);
		//dont keep the spinner once stop is called - causes issue when loading ajax form for 2nd time
		if (Browser.ie && Browser.version < 9) {
			//well ok we have to in ie8 ;( otherwise it give a js error somewhere in FX
			s.clearChain(); // tried this to remove FX but didnt seem to achieve anything
			s.hide();
		} else {
			s.destroy();
		}
		delete this.spinners[inline];
	}
});

/**
 * create the Fabrik name space
 */
(function () {
	if (typeof(Fabrik) === "undefined") {
		
		Fabrik = {};
		Fabrik.events = {};
		Fabrik.Windows = {};
		Fabrik.loader = new Loader();
		Fabrik.blocks = {};
		Fabrik.addBlock = function (blockid, block) {
			Fabrik.blocks[blockid] = block;
		};
		//was in head.ready but that cause js error for fileupload in admin when it wanted to 
		//build its window.
		Fabrik.iconGen = new IconGenerator({scale: 0.5});
		
		//events test: replacing window.addEvents as they are reset when you reload mootools in ajax window.
		// need to load mootools in ajax window otherwise Fabrik classes dont correctly load
		Fabrik.addEvent = function (type, fn) {
			if (!Fabrik.events[type]) {
				Fabrik.events[type] = [];
			}
			if (!Fabrik.events[type].contains(fn)) {
				Fabrik.events[type].push(fn);
			}
		};
		
		Fabrik.addEvents = function (events) {
			for (var event in events) {
				Fabrik.addEvent(event, events[event]);
			}
			return this;
		};
		
		Fabrik.fireEvent = function (type, args, delay) {
			var events = Fabrik.events;
			if (!events || !events[type]) {
				return this;
			}
			args = Array.from(args);
	
			events[type].each(function (fn) {
				if (delay) {
					fn.delay(delay, this, args);
				} else {
					fn.apply(this, args);
				}
			}, this);
			return this;
		};
	}
}());
	
head.ready(function () {
	Fabrik.tips = new FloatingTips('.fabrikTip', {html: true});
	Fabrik.addEvent('fabrik.list.updaterows', function () {
		//reattach new tips after list redraw,
		Fabrik.tips.attach('.fabrikTip');
	});
	Fabrik.addEvent('fabrik.plugin.inlineedit.editing', function () {
		Fabrik.tips.hideAll();
	});
});
