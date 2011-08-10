/**
 * @package Joomla!
 * @subpackage JavaScript
 * @since 1.5
 */
var FbRatingList = new Class({

   options: {
		'userid': 0,
		'mode' : ''
   },
   
   Implements:[Events, Options],

	initialize: function(id, options){
		options.element = id;
		this.setOptions(options);
		if (this.options.mode == 'creator-rating') {
			return;
		}
		this.spinner = Fabrik.loader.getSpinner();
		this.col = $$('.fabrik_row___' + id);
		this.origRating = {};
		this.col.each(function(tr) {
			var stars = tr.getElements('.starRating');

			stars.each(function(star) {
				star.addEvent('mouseover', function(e) {
					this.origRating[tr.id] = star.findClassUp('fabrik_element').getElement('.ratingMessage').innerHTML.toInt();;
					stars.each(function(ii) {
						if (this._getRating(star) >= this._getRating(ii)) {
							ii.src = this.options.insrc;
						} else {
							ii.src = this.options.outsrc;
						}
					}.bind(this));
					star.findClassUp('fabrik_element').getElement('.ratingMessage').innerHTML = star.alt;
				}.bind(this));

				star.addEvent('mouseout', function(e) {
					stars.each(function(ii) {
						if (this.origRating[tr.id] >= this._getRating(ii)) {
							ii.src = this.options.insrc;
						} else {
							ii.src = this.options.outsrc;
						}
					}.bind(this));
					star.findClassUp('fabrik_element').getElement('.ratingMessage').innerHTML = this.origRating[tr.id];
				}.bind(this));
			}.bind(this));

			stars.each(function(star) {
				star.addEvent('click', this.doAjax.bindWithEvent(this, [ star ]));
			}.bind(this));

		}.bind(this));

	},

	_getRating : function(i) {
		r = i.className.replace("rate_", "").replace("starRating ", "");
		return r.toInt();
	},

	doAjax : function(e, star) {
		e.stop();
		this.rating = this._getRating(star);
		var ratingmsg = star.findClassUp('fabrik_element').getElement('.ratingMessage');
		this.spinner.inject(ratingmsg);
		var row = $(star).findClassUp('fabrik_row');
		var rowid = row.id.replace('list_' + this.options.listid + '_row_', '');
		var data = {
			'row_id' : rowid,
			'elementname' : this.options.elid,
			'userid' : this.options.userid,
			'rating' : this.rating,
			'mode' : this.options.mode
		};
		var url = Fabrik.liveSite+'/index.php?option=com_fabrik&format=raw&view=plugin&task=pluginAjax&g=element&plugin=rating&method=ajax_rate&element_id='+this.options.elid;
		new Request({url:url,
			'data':data,
			onComplete:function(r){
				r = r.toInt();
				this.rating = r;
				ratingmsg.set('html', this.rating);
				this.spinner.dispose();
				star.findClassUp('fabrik_element').getElements('img').each(function(i, x) {
					i.src(x < r) ? this.options.insrc : this.options.outsrc;
				}.bind(this));
			}.bind(this)
		}).send();
	}
});