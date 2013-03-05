/**File:	inlineEdit.v3.js
Title:Mootools inlineEdit Plugin
Author:Justin Maier
Url:http://justinmaier.com
Date:2008-06-06
Ver:1*/

var InlineEdit = new Class({
	Implements: [Options, Events],
	options: {
		onComplete: function () {}, 
		onLoad: function () {}, 
		onKeyup: function () {}, 
		inputClass: 'input',
		stripHtml: true
	},
	
	initialize: function (element, options) {
		this.setOptions(options);
		this.element = element;
		this.originalText = element.get('html').replace(/<br>/gi, "\n");
		this.input = new Element('textarea', {
			'class': this.options.inputClass,
			'styles': this.element.getStyles('width', 'height', 'padding-top', 'padding-right', 'padding-bottom', 'padding-left', 'margin-top', 'margin-right', 'margin-bottom', 'margin-left', 'font-family', 'font-size', 'font-weight', 'line-height', 'border-top', 'border-right', 'border-bottom', 'border-left', 'background-color', 'color'),
			'events': {
				'keyup': this.keyup.bind(this),
				'blur': this.complete.bind(this)
			},
			'value': this.originalText
		});
		this.input.setStyle('margin-left', this.input.getStyle('margin-left').toInt() - 1);
		this.originalWidth = this.element.getStyle('width');
		this.element.setStyles({'visibility': 'hidden', 'position': 'absolute', 'width': this.element.offsetWidth});
		this.input.inject(this.element, 'after');
		this.input.focus();
		this.fireEvent('onLoad', [this.element, this.input]);
	},
	
	keyup: function (e) {
		if (!e) {
			return;
		}
		this.fireEvent('onKeyup', [this.element, this.input, e]);
		this.element.set('html', (e.key === 'enter') ? this.getContent() + "&nbsp;" : this.getContent());
		if (e.key === 'enter') {
			this.input.addEvent('keydown', this.newLine.bind(this));
		}
		this.input.setStyle('height', this.element.offsetHeight);
		if (e.key === 'esc') {
			this.element.set('text', this.originalText);
			this.end();
		}
	},
	
	getContent: function () {
		var content = this.input.value;
		if (this.options.stripHtml) {
			content = content.replace(/(<([^>]+)>)/ig, "");
		}
		return (content.replace(/\n/gi, "<br>"));
	},
	
	newLine: function () {
		this.element.innerHTML = this.element.innerHTML.replace("&nbsp;", "");
		this.input.removeEvents('keydown');
	},
	
	complete: function () {
		this.element.set('html', this.getContent());
		this.fireEvent('onComplete', this.element);
		this.end();
	},
	
	end: function () {
		this.input.destroy();
		this.element.setStyles({'visibility': 'visible', 'position': 'relative', 'width': this.originalWidth});
	}
});

Element.implement({
	inlineEdit: function (options) {
		return new InlineEdit(this, options);
	}
});