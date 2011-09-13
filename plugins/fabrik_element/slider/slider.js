var FbSlider = new Class({
	Extends : FbElement,
	initialize : function (element, options) {
		this.parent(element, options);
		this.plugin = 'fabrikslider';
		if (typeOf(this.options.value) === 'null') {
			this.options.value = 0;
		}
		this.options.value = this.options.value.toInt();
		if (this.options.editable === true) {
			head.ready(function () {
				if (typeOf(this.element) === 'null') {
					return;
				}
				var output = this.element.getElement('.fabrikinput');
				var output2 = this.element.getElement('.slider_output');
				this.mySlide = new Slider(this.element
						.getElement('.fabrikslider-line'), this.element
						.getElement('.knob'), {
					onChange : function (pos) {
						output.value = pos;
						this.options.value = pos;
						output2.set('text', pos);
						this.callChange();
					}.bind(this),
					steps : this.options.steps
				}).set(0);

				this.mySlide.set(this.options.value);
				output.value = this.options.value;
				output2.set('text', this.options.value);
				var clear = this.element.getElement('.clearslider');
				if (typeOf(clear) !== 'null') {
					clear.addEvent('click', function (e) {
						this.mySlide.set(0);
						output.value = '';
						output2.set('text', '');
						e.stop();
					});
				}
			}.bind(this));
		}
	},
	
	getValue: function () {
		return this.options.value;
	},
	
	callChange: function ()
	{
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