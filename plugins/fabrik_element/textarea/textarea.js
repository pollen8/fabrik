var FbTextarea = new Class({
	Extends: FbElement,
	initialize: function (element, options) {
		
		this.plugin = 'fabriktextarea';
		this.parent(element, options);
		
		// $$$ rob need to slightly delay this as if lots of js loaded (eg maps)
		// before the editor then the editor may not yet be loaded 
		(function () {
			this.getTextContainer();
			this.watchTextContainer();	
		}.bind(this)).delay(10000);
		
	},
	
	watchTextContainer: function ()
	{
		if (typeOf(this.element) === 'null') {
			this.element = document.id(this.options.element);
		}
		if (typeOf(this.element) === 'null') {
			//can occur when element is part of hidden first group
			return;
		}
		if (this.options.editable === true) {
			var c = this.element.getParent('.fabrikElementContainer');
			if (c === 'null' || typeOf(c) === 'null' || c === false) {
				fconsole('no fabrikElementContainer class found for textarea');
				return;
			}
			var element = c.getElement('.fabrik_characters_left');
			
			if (typeOf(element) !== 'null') {
				this.warningFX = new Fx.Morph(element, {duration: 1000, transition: Fx.Transitions.Quart.easeOut});
				this.origCol = element.getStyle('color');
				if (this.options.wysiwyg) {
					tinymce.dom.Event.add(this.container, 'keydown', this.informKeyPress.bindWithEvent(this));
				} else {
					this.container.addEvent('keydown', function (e) {
						this.informKeyPress();
					}.bind(this));
				}
			}
		}
	},
	
	cloned: function (c) {
		//c is the repeat group count
		this.getTextContainer();
		this.watchTextContainer();
	},
	
	getTextContainer: function ()
	{
		if (this.options.wysiwyg) {
			var instance = tinyMCE.get(this.options.element);
			if (instance) {
				this.container = instance.getDoc();
			} else {
				fconsole('didnt find wysiwyg edtor ...' + this.options.element);
			}
		} else {
			//regrab the element for inline editing (otherwise 2nd col you edit doesnt pickup the textarea.
			this.element = document.id(this.options.element);
			this.container = this.element;
		}
	},
	
	getContent: function ()
	{
		if (this.options.wysiwyg) {
			return tinyMCE.activeEditor.getContent().replace(/<\/?[^>]+(>|$)/g, "");
		} else {
			return this.container.value;
		}
	},
	
	setContent: function (c)
	{
		if (this.options.wysiwyg) {
			return tinyMCE.getInstanceById(this.element.id).setContent(c);
		} else {
			this.getTextContainer();
			if (typeOf(this.container) !== 'null') {
				this.container.value = c;
			}
		}
		return null;
	},
	
	informKeyPress: function ()
	{
		var charsleftEl = this.element.getParent('.fabrikElementContainer').getElement('.fabrik_characters_left');
		var content = this.getContent();
		var charsLeft =  this.options.max - (content.length + 1);
		if (charsLeft < 0) {
			this.setContent(content.substring(0, this.options.max));
			charsLeft = 0;
			this.warningFX.start({'opacity': 0, 'color': '#FF0000'}).chain(function () {
				this.start({'opacity': 1, 'color': '#FF0000'}).chain(function () {
					this.start({'opacity': 0, 'color': this.origCol}).chain(function () {
						this.start({'opacity': 1});
					});
				});
			});
		} else {
			charsleftEl.setStyle('color', this.origCol);
		}
		charsleftEl.getElement('span').set('html', charsLeft);
	},
	
	reset: function ()
	{
		this.update(this.options.defaultVal);
	},
	
	update: function (val) {
		this.getTextContainer();
		if (!this.options.editable) {
			this.element.set('html', val);
			return;
		}
		this.setContent(val);
	}
});