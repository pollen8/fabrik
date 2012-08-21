var FbSlider = new Class({
	Extends: FbElement,
	initialize: function (element, options) {
		this.parent(element, options);
		this.plugin = 'slider';
		if (typeOf(this.options.value) === 'null') {
			this.options.value = 0;
		}
		this.options.value = this.options.value.toInt();
		var v = this.options.value;
		if (this.options.editable === true) {
			if (typeOf(this.element) === 'null') {
				fconsole('no element found for slider');
				return;
			}
			var output = this.element.getElement('.fabrikinput');
			var output2 = this.element.getElement('.slider_output');
			this.mySlide = new Slider(
				this.element.getElement('.fabrikslider-line'),
				this.element.getElement('.knob'), 
				{
					onChange : function (pos) {
						output.value = pos;
						this.options.value = pos;
						output2.set('text', pos);
						output.fireEvent('blur', new Event.Mock(output, 'blur'));
						this.callChange();
					}.bind(this),
					onComplete: function (pos) {
						//fire for validations
						output.fireEvent('blur', new Event.Mock(output, 'change'));
						this.element.fireEvent('change', new Event.Mock(this.element, 'change'));
					}.bind(this),
					steps : this.options.steps
				}
			).set(v);
			this.mySlide.set(this.options.value);
			output.value = this.options.value;
			output2.set('text', this.options.value);
			var clear = this.element.getElement('.clearslider');
			if (typeOf(clear) !== 'null') {
				clear.addEvent('click', function (e) {
					this.mySlide.set(0);
					output.value = '';
					output.fireEvent('blur', new Event.Mock(output, 'change'));
					output2.set('text', '');
					e.stop();
				}.bind(this));
			}
		}
	},
	
	getValue: function () {
		return this.options.value;
	},
	
	callChange: function () {
		typeOf(this.changejs) === 'function' ? this.changejs.delay(0) :	eval(this.changejs);
	},
	
	addNewEvent: function (action, js) {
		if (action === 'load') {
			this.loadEvents.push(js);
			this.runLoadEvent(js);
			return;
		}
		if (action === 'change') {
			this.changejs = js;
		}
	}
});