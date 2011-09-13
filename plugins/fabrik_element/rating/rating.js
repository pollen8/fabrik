var FbRating = new Class({
	Extends : FbElement,
	initialize : function (element, options, rating) {
		this.field = $(element);
		this.parent(element, options);
		if (this.options.mode === 'creator-rating' && this.options.view === 'details') {
			// deactivate if in detail view and only the record creator
			// can rate
			return;
		}
		this.element = $(element + '_div');
		this.rating = rating;
		this.spinner = new Asset.image(Fabrik.liveSite + 'media/com_fabrik/images/ajax-loader.gif', {
			'alt' : 'loading',
			'class' : 'ajax-loader'
		});
		this.stars = this.element.getElements('.starRating');
		this.ratingMessage = this.element.getElement('.ratingMessage');
		this.stars.each(function (i) {
			i.addEvent('mouseover', function (e) {
				this.stars.each(function (ii) {
					if (this._getRating(i) >= this._getRating(ii)) {
						ii.src = this.options.insrc;
					}
				}.bind(this));
				this.ratingMessage.innerHTML = i.alt;
			}.bind(this));
		}.bind(this));

		this.stars.each(function (i) {
			i.addEvent('mouseout', function (e) {
				this.stars.each(function (ii) {
					ii.src = this.options.outsrc;
				}.bind(this));
			}.bind(this));
		}.bind(this));

		this.stars.each(function (i) {
			i.addEvent('click', function (e) {
				this.rating = this._getRating(i);
				this.field.value = this.rating;
				this.doAjax();
				this.setStars();
			}.bind(this));
		}.bind(this));

		this.element.addEvent('mouseout', function (e) {
			this.setStars();
		}.bind(this));

		this.element.addEvent('mouseover', function (e) {
			this.element.getElement('.rate_-1').setStyles({
				visibility : 'visible'
			});
		}.bind(this));

		this.element.getElement('.rate_-1').addEvent('mouseover', function (e) {
			e = new Event(e);
			e.target.src = this.options.clearinsrc;
			this.ratingMessage.innerHTML = Joomla.JText._('PLG_ELEMENT_RATING_NO_RATING');
		}.bind(this));

		this.element.getElement('.rate_-1').addEvent('mouseout', function (e) {
			e = new Event(e);
			if (this.rating !== -1) {
				e.target.src = this.options.clearoutsrc;
			}
		}.bind(this));

		this.element.getElement('.rate_-1').addEvent('click', function (e) {
			this.rating = -1;
			this.field.value = '';
			this.stars.each(function (ii) {
				ii.src = this.options.outsrc;
			}.bind(this));
			e = new Event(e);
			this.element.getElement('.rate_-1').src = this.options.clearinsrc;
			this.doAjax();
		}.bind(this));
		this.setStars();

	},

	doAjax : function () {
		if (this.options.editable === false) {
			this.spinner.inject(this.ratingMessage);
			var data = {
				'row_id' : this.options.row_id,
				'elementname' : this.options.elid,
				'userid' : this.options.userid,
				'rating' : this.rating
			};
			var url = Fabrik.liveSite + 'index.php?option=com_fabrik&format=raw&view=plugin&task=pluginAjax&g=element&plugin=rating&method=ajax_rate&element_id=' + this.options.elid;

			var closeFn = new Request({
				url : url,
				'data' : data,
				onComplete : function () {
					this.spinner.dispose();
				}.bind(this)
			}).send();
		}
	},

	_getRating : function (i) {
		r = i.className.replace("rate_", "").replace("starRating ", "");
		return r.toInt();
	},

	setStars : function () {
		this.stars.each(function (ii) {
			var starScore = this._getRating(ii);
			if (starScore <= this.rating) {
				ii.src = this.options.insrc;
			} else {
				ii.src = this.options.outsrc;
			}
		}.bind(this));
		if (this.rating !== -1) {
			this.element.getElement('.rate_-1').src = this.options.clearoutsrc;
		} else {
			this.element.getElement('.rate_-1').src = this.options.clearinsrc;
		}

	},

	update : function (val) {
		this.rating = val;
		this.setStars();
	}
});