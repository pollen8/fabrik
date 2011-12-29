var FbSlideshow = new Class({
	 
	Extends: FbElement, 
	
	initialize : function (element, options) {
		this.plugin = 'slideshow';
		this.parent(element, options);
		var opts = {
			controller: true,
			delay: this.options.delay,
			duration: this.options.duration,
			height: this.options.height,
			width: this.options.width,
			loader: false,
			hu: Fabrik.liveSite,
			thumbnails: this.options.thumbnails,
			captions: this.options.captions,
		};
		switch (this.options.slideshow_type) {
		case 1:
		/* falls through */
		default:
			opts = Object.append(opts, {fast: true});
			this.slideshow = new Slideshow(this.options.html_id, this.options.slideshow_data, opts);
			break;
		case 2:
			opts = Object.append(opts, {
				zoom: this.options.zoom,
				pan: this.options.pan
			});
			this.slideshow = new Slideshow.KenBurns(this.options.html_id, this.options.slideshow_data, opts);
			break;
		case 3:
			this.slideshow = new Slideshow.Push(this.options.html_id, this.options.slideshow_data, opts);
			break;
		case 4:
			this.slideshow = new Slideshow.Fold(this.options.html_id, this.options.slideshow_data, opts);
			break;
		}
	}
});