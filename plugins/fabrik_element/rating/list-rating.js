/**
 * Ratings Element - List
 *
 * @copyright: Copyright (C) 2005-2013, fabrikar.com - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

define(['jquery'], function (jQuery) {
	var FbRatingList = new Class({

		options: {
			'userid': 0,
			'mode'  : '',
			'formid': 0
		},

		Implements: [Events, Options],

		initialize: function (id, options) {
			options.element = id;
			this.setOptions(options);
			if (this.options.canRate === false) {
				return;
			}
			if (this.options.mode === 'creator-rating') {
				return;
			}
			this.col = $$('.' + id);
			this.origRating = {};
			this.col.each(function (tr) {
				var stars = tr.getElements('.starRating');

				stars.each(function (star) {
					star.addEvent('mouseover', function (e) {
						this.origRating[tr.id] = star.getParent('.fabrik_element').getElement('.ratingMessage').innerHTML.toInt();
						stars.each(function (ii) {
							if (this._getRating(star) >= this._getRating(ii)) {
								if (Fabrik.bootstrapped) {
									ii.removeClass('icon-star-empty').addClass('icon-star');
								} else {
									ii.src = this.options.insrc;
								}
							} else {
								if (Fabrik.bootstrapped) {
									ii.addClass('icon-star-empty').removeClass('icon-star');
								} else {
									ii.src = this.options.insrc;
								}
							}
						}.bind(this));
						star.getParent('.fabrik_element').getElement('.ratingMessage').innerHTML = star.get('data-fabrik-rating');
					}.bind(this));

					star.addEvent('mouseout', function (e) {
						stars.each(function (ii) {
							if (this.origRating[tr.id] >= this._getRating(ii)) {
								if (Fabrik.bootstrapped) {
									ii.removeClass('icon-star-empty').addClass('icon-star');
								} else {
									ii.src = this.options.insrc;
								}
							} else {
								if (Fabrik.bootstrapped) {
									ii.addClass('icon-star-empty').removeClass('icon-star');
								} else {
									ii.src = this.options.insrc;
								}
							}
						}.bind(this));
						star.getParent('.fabrik_element').getElement('.ratingMessage').innerHTML = this.origRating[tr.id];
					}.bind(this));
				}.bind(this));

				stars.each(function (star) {
					star.addEvent('click', function (e) {
						this.doAjax(e, star);
					}.bind(this));
				}.bind(this));

			}.bind(this));

		},

		_getRating: function (i) {
			var r = i.get('data-fabrik-rating');
			return r.toInt();
		},

		doAjax: function (e, star) {
			e.stop();
			this.rating = this._getRating(star);
			var ratingmsg = star.getParent('.fabrik_element').getElement('.ratingMessage');
			Fabrik.loader.start(ratingmsg);

			var starRatingCover = new Element('div', {id: 'starRatingCover',
				styles                                  : {
					bottom  : 0,
					top     : 0,
					right   : 0,
					left    : 0,
					position: 'absolute',
					cursor  : 'progress'
				}
			});
			var starRatingContainer = star.getParent('.fabrik_element').getElement('div');
			starRatingContainer.grab(starRatingCover, 'top');

			var row = document.id(star).getParent('.fabrik_row');
			var rowid = row.id.replace('list_' + this.options.listRef + '_row_', '');
			var data = {
				'option'     : 'com_fabrik',
				'format'     : 'raw',
				'task'       : 'plugin.pluginAjax',
				'plugin'     : 'rating',
				'g'          : 'element',
				'method'     : 'ajax_rate',
				'formid'     : this.options.formid,
				'element_id' : this.options.elid,
				'row_id'     : rowid,
				'elementname': this.options.elid,
				'userid'     : this.options.userid,
				'rating'     : this.rating,
				'mode'       : this.options.mode
			};
			new Request({
				url       : '',
				'data'    : data,
				onComplete: function (r) {
					r = r.toInt();
					this.rating = r;
					ratingmsg.set('html', this.rating);
					Fabrik.loader.stop(ratingmsg);
					var tag = Fabrik.bootstrapped ? 'i' : 'img';
					star.getParent('.fabrik_element').getElements(tag).each(function (i, x) {
						if (x < r) {
							if (Fabrik.bootstrapped) {
								i.removeClass('icon-star-empty').addClass('icon-star');
							} else {
								i.src = this.options.insrc;
							}
						} else {
							if (Fabrik.bootstrapped) {
								i.addClass('icon-star-empty').removeClass('icon-star');
							} else {
								i.src = this.options.insrc;
							}
						}
					}.bind(this));
					document.id('starRatingCover').destroy();
				}.bind(this)
			}).send();
		}
	});

	return FbRatingList;
});