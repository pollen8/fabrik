/**
 * @package		Joomla!
 * @subpackage	JavaScript
 * @since		1.5
 */
var FbThumbsList = new Class({

    options: {
	    'imageover': '',
	    'imageout': '',
	    'userid': ''
    },

	Implements: [Events, Options],
	
	initialize: function (id, options) {
		this.setOptions(options);
		//this.spinner = Fabrik.loader.getSpinner();
		this.col = $$('.' + id);
		this.origThumbUp = {};
		this.origThumbDown = {};
		this.col.each(function (tr) {
			var row = tr.getParent('.fabrik_row');
			if (row) {
				var rowid = row.id.replace('list_' + this.options.listid + '_row_', '');
				var thumbup = tr.getElements('.thumbup');
				var thumbdown = tr.getElements('.thumbdown');
				thumbup.each(function (thumbup) {
					thumbup.addEvent('mouseover', function (e) {
						thumbup.setStyle('cursor', 'pointer');
						thumbup.src = this.options.imagepath + "thumb_up_in.gif";
					}.bind(this));
					thumbup.addEvent('mouseout', function (e) {
						thumbup.setStyle('cursor', '');
						if (this.options.myThumbs[rowid] === 'up') {
							thumbup.src = this.options.imagepath + "thumb_up_in.gif";
						} else {
							thumbup.src = this.options.imagepath + "thumb_up_out.gif";
						}
					}.bind(this));
					thumbup.addEvent('click', function (e) {
						this.doAjax(thumbup, 'up');
					}.bind(this));
				}.bind(this));

				thumbdown.each(function (thumbdown) {
					thumbdown.addEvent('mouseover', function (e) {
						thumbdown.setStyle('cursor', 'pointer');
						thumbdown.src = this.options.imagepath + "thumb_down_in.gif";
					}.bind(this));

					thumbdown.addEvent('mouseout', function (e) {
						thumbdown.setStyle('cursor', '');
						if (this.options.myThumbs[rowid] === 'down') {
							thumbdown.src = this.options.imagepath + "thumb_down_in.gif";
						} else {
							thumbdown.src = this.options.imagepath + "thumb_down_out.gif";
						}
					}.bind(this));
					thumbdown.addEvent('click', function (e) {
						this.doAjax(thumbdown, 'down');
					}.bind(this));
				}.bind(this));
			}
		}.bind(this));
	},

	doAjax: function (e, thumb) {
		var row = e.getParent('.fabrik_row');
		var rowid = row.id.replace('list_' + this.options.listid + '_row_', '');
		var count_thumb = $('count_thumb' + thumb + rowid);
		Fabrik.loader.start(count_thumb);
		this.thumb = thumb;

		var data = {
			'row_id': rowid,
			'elementname': this.options.elid,
			'userid': this.options.userid,
			'thumb': this.thumb,
			'listid': this.options.listid
		};
		var url = Fabrik.liveSite + '/index.php?option=com_fabrik&format=raw&view=plugin&task=pluginAjax&g=element&plugin=thumbs&method=ajax_rate&element_id=' + this.options.elid + '&thumb=' + this.thumb + '&row_id=' + rowid;
		new Request({url: url,
			'data': data,
			onComplete: function (r) {
				var count_thumbup = document.id('count_thumbup' + rowid);
				var count_thumbdown = document.id('count_thumbdown' + rowid);
				var thumbup = row.getElements('.thumbup');
				var thumbdown = row.getElements('.thumbdown');
				this.spinner.dispose();
				Fabrik.loader.stop(count_thumb);
				//r = r.split(this.options.splitter2);
				r = JSON.decode(r);
				if (r.error) {
					console.log(r.error);
				} else {
					count_thumbup.set('html', r[0]);
					count_thumbdown.set('html', r[1]);
				}
			}.bind(this)
		}).send();
	}
});