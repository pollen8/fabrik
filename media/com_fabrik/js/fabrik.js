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
			this.spinners = {};			
		}.bind(this));
	},
	
	getSpinner: function(inline, msg){
		msg = msg ? msg : 'loading';
		if (typeOf(document.id(inline)) == 'null') {
			inline = false;
		}
		var inline = inline ? inline : false;
		var target = inline ? inline : document.body;
		if (!this.spinners[inline]) {
			this.spinners[inline] = new Spinner(target, {'message':msg});
		}
		return this.spinners[inline];
	},
	
	start: function(inline, msg){
		this.getSpinner(inline, msg).position().show();
	},
	
	stop: function(inline, msg, keepOverlay){
		this.getSpinner(inline, msg).hide();
	}
});

/**
 * create the Fabrik name space
 */
if(typeof(Fabrik)==="undefined"){
	var Fabrik={};
	Fabrik.Windows = {};
	Fabrik.loader = new Loader();
	Fabrik.blocks = {};
	Fabrik.addBlock = function(blockid, block){
		Fabrik.blocks[blockid] = block;
	}
	//was in head.ready but that cause js error for fileupload in admin when it wanted to 
	//build its window.
	Fabrik.iconGen = new IconGenerator({scale:0.5});
}
	
head.ready(function() {
	Fabrik.tips = new FloatingTips('.fabrikTip', {html:true});
	//Fabrik.overlay = new Overlay();
});
