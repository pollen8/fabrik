/**
 * implemented by lists and forms 
 */
var Plugins = new Class({
	
	runPlugins : function(func, event) {
		var args = $A(arguments).filter(function(a, k) {
			return k > 1;
		});
		var ret = true;
		// ie wierdness with multple table plugins in content article?
		if (typeOf(this.options) == 'null'){
			return true;
		}
		this.plugins.each( function(plugin) {
			if (typeOf(plugin) !== 'null' && typeOf(plugin[func]) != 'null') {
				if (plugin[func](event, args) == false) {
					ret = false;
				}
			}
		});
		return ret;
	},
	
	addPlugin : function(plugin) {
			this.plugins.push(plugin);
	}
});

/**
 * keeps the element posisiton in the center even when scroll/resizing
 */

Element.implement({
	keepCenter:function(){
		this.makeCenter();
		window.addEvent('scroll', function(){
			this.makeCenter();
		}.bind(this));
		window.addEvent('resize', function(){
			this.makeCenter();
		}.bind(this));
	},
	makeCenter:function(){
		var l = window.getWidth()/2 - this.getWidth()/2;
		var t = window.getScrollTop() + (window.getHeight()/2 - this.getHeight()/2);
		this.setStyles({left:l, top:t});
	}
})
/**
 * loading aninimation class, either inline next to an element or 
 * full screen
 */

Loader = new Class({
	
	Implements:[Options],
	options:{
		liveSite 	:'',
		'tmpl':'components/com_fabrik/views/package/tmpl/default/images/'
	},
	
	initialize:function(options){
		this.setOptions(options);
		head.ready(function() {
			var i = this.getSpinner();
			var s = new Element('span');
			this.modal = new Element('div', {'class':'fbPackageStatus'}).adopt(i).adopt(s);
			this.modal.inject(document.body);	
			
			this.fx = new Fx.Tween(this.modal, {
			'duration': 350,
			property:'opacity',
			'link':'cancel',
			transition: Fx.Transitions.Sine.easeInOut
			});
			this.fx.set(0);
		}.bind(this));
	},
	
	getSpinner: function(){
		return new Element('img', {'src':Fabrik.liveSite + this.options.tmpl + 'ajax-loader.gif'});
	},
	
	start: function(msg, inline){
		msg = [msg, 'loading'].pick();
		if (typeOf(document.id(inline)) == 'null') {
			inline = false;
		}
		var inline = inline ? inline : false;
		this.modal.getElement('span').set('text', msg);
		if (!inline){
			this.modal.id = 'fbPackageLoader';
			this.modal.keepCenter();
			Fabrik.overlay.show();
			this.fx.start(1);
		}else {
			this.getSpinner().inject(document.id(inline), 'after');
			this.modal.id = '';
		}
	},
	
	stop: function(msg, inline, keepOverlay){
		keepOverlay = keepOverlay ? keepOverlay : false;
		if (typeOf(document.id(inline)) == 'null') {
			//this.modal.hide();
			this.fx.set(0);
			if (!keepOverlay) {
				Fabrik.overlay.hide();
			}
		}else{
			document.id(inline).getNext().destroy();
		}
		
	}
});

/**
 * semi transparent bg to use as overlay when displaying windows.
 */

Overlay = new Class({
	
	initialize:function(){
		this.overlay = new Element('div', {'id':'fabrikOverlay', 'styles':{'background-color':'#000'}});
		this.overlay.addEvent('click', this.hide.bindWithEvent(this));
		head.ready(function() {
			this.overlay.inject(document.body);	
			this.fx = new Fx.Tween(this.overlay, {
			'duration': 350,
			'property':'opacity',
			'link':'cancel',
			transition: Fx.Transitions.Sine.easeInOut
			});
			this.overlay.keepCenter();
			this.fx.set(0);
		}.bind(this));
	},
	
	fade:function(dir){
		dir = dir ? dir : 'out';
		if (dir == 'out') {
			this.fx.start(0).chain(function(){
				this.hide();
			}.bind(this));
		} else {
			this.show();
		}
	},
	
	hide:function(){
		this.fx.set(0);
		window.fireEvent('fabrik.overlay.hide');
	},
	
	show:function(){
		this.overlay.show();
		this.fx.start(0.6);
	}
});

/**
 * create the Fabrik name space
 */
if(typeof(Fabrik)==="undefined"){
	var Fabrik={};
	Fabrik.Windows = {};
	head.ready(function() {
		Fabrik.tips = new FloatingTips('.fabrikTip', {html:true});
	});
	Fabrik.iconGen = new IconGenerator({scale:0.5});
	Fabrik.overlay = new Overlay();
	Fabrik.loader = new Loader();
	Fabrik.blocks = {};
	
	Fabrik.addBlock = function(blockid, block){
		Fabrik.blocks[blockid] = block;
	}
	
}
