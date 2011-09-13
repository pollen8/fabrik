var FbSlideshow = FbElement.extend({
	
	initialize : function (element, options) {
		this.plugin = 'slideshow';
		this.parent(element, options);
		var opts = {
			controller: true,
			delay: parseInt(this.options.slideshow_delay, 10),
			duration: parseInt(this.options.slideshow_duration, 10),
			height: parseInt(this.options.slideshow_height, 10),
			width: parseInt(this.options.slideshow_width, 10),
			hu: Fabrik.liveSite,
			thumbnails: this.options.slideshow_thumbnails,
			captions: this.options.slideshow_captions
		};
		switch (this.options.slideshow_type) {
		case 1:
			opts = Object.append(opts, {fast: true});
			this.slideshow = new Slideshow(this.options.html_id, this.options.slideshow_data, opts);
			break;
		case 2:
			opts = Object.append(opts, {
				zoom : parseInt(this.options.slideshow_zoom, 10),
				pan : parseInt(this.options.slideshow_pan, 10)
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