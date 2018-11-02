/**
 * Visualization Slideshow
 *
 * @copyright: Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

var FbSlideshowViz = new Class({

	Implements: [Options],
	
	options: {},
	
	initialize: function (element, options) {
		this.setOptions(options);
		var opts = {
			controller: true,
			delay: parseInt(this.options.slideshow_delay, 10),
			duration: parseInt(this.options.slideshow_duration, 10),
			height: parseInt(this.options.slideshow_height, 10),
			width: parseInt(this.options.slideshow_width, 10),
			//hu: Fabrik.liveSite,
			hu: this.options.liveSite,
			thumbnails: this.options.slideshow_thumbnails,
			captions: this.options.slideshow_captions
		};

		if (this.options.slideshow_thumbnails)
		{
            jQuery('.slider').slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                //variableWidth: true,
                arrows: false,
                dots: false,
                fade: true,
                cssEase: 'linear',
                infinite: true,
                speed: 500,
				asNavFor: '.slider-nav'
            });

            jQuery('.slider-nav').slick({
                slidesToShow: 3,
                slidesToScroll: 1,
                //variableWidth: true,
                arrows: true,
                dots: true,
                centerMode: true,
                focusOnSelect: true,
                asNavFor: '.slider'
            });

		}
		else {
            jQuery('.slider').slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                //variableWidth: true,
                arrows: true,
                dots: true,
                fade: true,
                cssEase: 'linear',
                infinite: true,
                speed: 500
            });
        }

		this.mediaScan();
	},

	mediaScan: function () {
		if (typeof(Slimbox) !== 'undefined') {
			Slimbox.scanPage();
		}
		if (typeof(Lightbox) !== 'undefined') {
			Lightbox.init();
		}
		if (typeof(Mediabox) !== 'undefined') {
			Mediabox.scanPage();
		}
	}
});