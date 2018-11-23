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

		var slickOptions = {
            slidesToShow: 1,
            slidesToScroll: 1,
            autoplay: this.options.slideshow_delay === 0 ? false : true,
            autoplaySpeed: this.options.slideshow_delay,
            //variableWidth: true,
            arrows: true,
            dots: false,
            fade: true,
            cssEase: 'linear',
            infinite: true,
            speed: this.options.slideshow_duration
		};

		var slickJSON = JSON.parse(this.options.slideshow_options);
		jQuery.extend(slickOptions, slickJSON);

        jQuery('#' + this.options.html_id).show();

		if (this.options.slideshow_thumbnails)
		{
		    var thumbOptions = {
		        asNavFor: '.slider-nav'
            };

		    jQuery.extend(slickOptions, thumbOptions);

            jQuery('.slider').slick(slickOptions);

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
            var noThumbOptions = {
            };

            jQuery.extend(slickOptions, noThumbOptions);

            jQuery('.slider').slick(slickOptions);
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