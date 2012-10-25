/*jshint mootools: true */
/*global Fabrik:true, fconsole:true, Joomla:true, CloneObject:true, $A:true, $H:true,unescape:true,Asset:true,FloatingTips:true,head:true,IconGenerator:true */

/**
 *  This class is temporarily requied until this patch:
 *  https://github.com/joomla/joomla-platform/pull/1209/files
 *  makes it into the CMS code. Its purpose is to queue ajax requests so they are not
 *  all fired at the same time - which result in db session errors.
 *   
 *  Currently this is called from:
 *  fabriktables.js
 *  
 */
RequestQueue = new Class({
	
	queue: {}, // object of xhr objects
	
	initialize: function () {
		this.periodical = this.processQueue.periodical(500, this);
	},
	
	add: function (xhr) {
		var k = xhr.options.url + Object.toQueryString(xhr.options.data) + Math.random();
		if (!this.queue[k]) {
			this.queue[k] = xhr;
		}
	},
	
	processQueue: function () {
		if (Object.keys(this.queue).length === 0) {
			return;
		}
		var xhr = {},
		running = false;

		// Remove successfuly completed xhr
		$H(this.queue).each(function (xhr, k) {
			if (xhr.isSuccess()) {
				delete(this.queue[k]);
				running = false;
			}
		}.bind(this));
		
		// Find first xhr not run and completed to run
		$H(this.queue).each(function (xhr, k) {
			if (!xhr.isRunning() && !xhr.isSuccess() && !running) {
				xhr.send();
				running = true;
			}
		});
	},
	
	empty: function () {
		return Object.keys(this.queue).length === 0;
	}
});

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
			debugger;
			var scriptadd = "requirejs(['" + urls.join("','") + "'], function () {})";
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
			// response.javascript = "(function () {"+response.javascript+"}).delay(6000)";
			Browser.exec(response.javascript);
		}

		this.onSuccess(response.tree, response.elements, response.html, response.javascript);
	}
});

/**
 * Keeps the element posisiton in the center even when scroll/resizing
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
 * Loading aninimation class, either inline next to an element or 
 * full screen
 */

var Loader = new Class({
	
	initialize: function (options) {
		this.spinners = {};			
	},
	
	getSpinner: function (inline, msg) {
		msg = msg ? msg : Joomla.JText._('COM_FABRIK_LOADING');
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
		
		// Dont keep the spinner once stop is called - causes issue when loading ajax form for 2nd time
		if (Browser.ie && Browser.version < 9) {
			
			// Well ok we have to in ie8 ;( otherwise it give a js error somewhere in FX
			s.clearChain(); // Tried this to remove FX but didnt seem to achieve anything
			s.hide();
		} else {
			s.destroy();
		}
		delete this.spinners[inline];
	}
});
