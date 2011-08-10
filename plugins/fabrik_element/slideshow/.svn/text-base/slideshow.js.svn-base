var FbSlideshow = FbElement.extend( {
	initialize : function(element, options) {
		var o = null;
		this.plugin = 'slideshow';
		this.parent(element, options);
		if (this.options.slideshow_type == 1) {
			// var slideshow_data = $H(this.options.slideshow_data);
		this.slideshow = new Slideshow(this.options.html_id,
				this.options.slideshow_data, {
					controller : true,
					delay : parseInt(this.options.slideshow_delay),
					duration : parseInt(this.options.slideshow_duration),
					height : parseInt(this.options.slideshow_height),
					width : parseInt(this.options.slideshow_width),
					hu : Fabrik.liveSite,
					thumbnails : this.options.slideshow_thumbnails,
					captions : this.options.slideshow_captions,
					fast : true
				});
	} else if (this.options.slideshow_type == 2) {
		// var slideshow_data = $H(this.options.slideshow_data);
		this.slideshow = new Slideshow.KenBurns(this.options.html_id,
				this.options.slideshow_data, {
					controller : true,
					delay : parseInt(this.options.slideshow_delay),
					duration : parseInt(this.options.slideshow_duration),
					height : parseInt(this.options.slideshow_height),
					width : parseInt(this.options.slideshow_width),
					hu : Fabrik.liveSite,
					thumbnails : this.options.slideshow_thumbnails,
					captions : this.options.slideshow_captions,
					zoom : parseInt(this.options.slideshow_zoom),
					pan : parseInt(this.options.slideshow_pan)
				});
	} else if (this.options.slideshow_type == 3) {
		// var slideshow_data = $H(this.options.slideshow_data);
		this.slideshow = new Slideshow.Push(this.options.html_id,
				this.options.slideshow_data, {
					controller : true,
					delay : parseInt(this.options.slideshow_delay),
					duration : parseInt(this.options.slideshow_duration),
					height : parseInt(this.options.slideshow_height),
					width : parseInt(this.options.slideshow_width),
					hu : Fabrik.liveSite,
					thumbnails : this.options.slideshow_thumbnails,
					captions : this.options.slideshow_captions
				});
	} else if (this.options.slideshow_type == 4) {
		// var slideshow_data = $H(this.options.slideshow_data);
		this.slideshow = new Slideshow.Fold(this.options.html_id,
				this.options.slideshow_data, {
					controller : true,
					delay : parseInt(this.options.slideshow_delay),
					duration : parseInt(this.options.slideshow_duration),
					height : parseInt(this.options.slideshow_height),
					width : parseInt(this.options.slideshow_width),
					hu : Fabrik.liveSite,
					thumbnails : this.options.slideshow_thumbnails,
					captions : this.options.slideshow_captions
				});
	}
}
});