/**
 * Enable us to use the same class interface for tips.js but use Bootrap popovers (Joomla 3)
 */
var FloatingTips = new Class({
	Implements: [Options, Events],
	
	options: {
		fxProperties: {transition: Fx.Transitions.linear, duration: 500},
		position: 'top',
		'showOn': 'mouseenter',
		'hideOn': 'mouseleave',
		'content': 'title',
		'distance': 50,
		'tipfx': 'Fx.Transitions.linear',
		'heading': '',
		'duration': 500,
		'fadein': false,
		'notice': false,
		showFn: function (e) {
			e.stop();
			return true;
		},
		hideFn: function (e) {
			e.stop();
			return true;
		}
	},
	
	initialize: function (elements, options) {
		this.setOptions(options);
		this.options.fxProperties = {transition: eval(this.options.tipfx), duration: this.options.duration};
		//any tip (not necessarily in this instance has asked for all other tips to be hidden.
		window.addEvent('tips.hideall', function (e, trigger) {
			this.hideOthers(trigger);
		}.bind(this));
		if (elements) {
			this.attach(elements);
		}
	},
	
	attach: function (elements) {
		this.elements = $$(elements);
		this.elements.each(function (trigger) {
			var opts = Object.merge(Object.clone(this.options), JSON.decode(trigger.get('opts', '{}').opts));
			if (opts.content === 'title') {
				opts.content = trigger.get('title');
				trigger.erase('title');
			}
			if (typeOf(opts.content) === 'function') {
				var c = opts.content(trigger);
				opts.content = typeOf(c) === 'null' ? '' : c.innerHTML;
			}
			opts.placement = opts.position;
			opts.title = opts.heading;
			if (!opts.notice) {
				opts.title += '<button class="close" data-popover="' + trigger.id + '">&times;</button>';
			}
			jQuery(trigger).popover(opts);
		}.bind(this));
	
	},
	
	addStartEvent: function (trigger, evnt) {
		
	},
	
	addEndEvent: function (trigger, evnt) {
		
	},
	
	getTipContent: function (trigger, evnt) {
		
	},
	
	show: function (trigger, evnt) {
		
	},
	
	hide: function (trigger, evnt) {
		
	},
	
	hideOthers: function (except) {
		
	},
	
	hideAll: function () {
		
	}

});