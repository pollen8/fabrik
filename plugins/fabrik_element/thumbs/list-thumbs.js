/**
 * Thumbs Element - List
 *
 * @copyright: Copyright (C) 2005-2013, fabrikar.com - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

var FbThumbsList = new Class({

	options: {
		'imageover': '',
		'imageout': '',
		'userid': '',
		'formid': 0
	},

	Implements: [Events, Options],
	
	initialize: function (id, options) {
		this.setOptions(options);
		this.col = document.getElements('.' + id);
		this.origThumbUp = {};
		this.origThumbDown = {};
		if (Fabrik.bootstrapped) {
			this.setUpBootstrapped();
		} else {
			this.col.each(function (tr) {
				var row = tr.getParent('.fabrik_row');
				if (row) {
					var rowid = row.id.replace('list_' + this.options.renderContext + '_row_', '');
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
		}
	},
	
	setUpBootstrapped: function () {
		this.col.each(function (td) {
			var row = td.getParent('.fabrik_row');
			if (row) {
				var rowid = row.id.replace('list_' + this.options.renderContext + '_row_', '');
				var up = td.getElement('button.thumb-up'),
				down = td.getElement('button.thumb-down');
				
				up.addEvent('click', function (e) {
					e.stop();
					var add = up.hasClass('btn-success') ? false : true;
					this.doAjax(td, 'up', add);
					if (!add) {
						up.removeClass('btn-success');
					} else {
						up.addClass('btn-success');
						if (typeOf(down) !== 'null') {
							down.removeClass('btn-danger');
						}
					}
					
				}.bind(this));
				
				if (typeOf(down) !== 'null') {
					
					down.addEvent('click', function (e) {
						e.stop();
						var add = down.hasClass('btn-danger') ? false : true;
						this.doAjax(td, 'down', add);
						if (!add) {
							down.removeClass('btn-danger');
						} else {
							down.addClass('btn-danger');
							up.removeClass('btn-success');
						}
						
					}.bind(this));
				}
			}
		}.bind(this));
	},

	doAjax: function (e, thumb, add) {
		add = add ? true : false;
		var row = e.getParent('.fabrik_row');
		var rowid = row.id.replace('list_' + this.options.renderContext + '_row_', '');
		var count_thumb = document.id('count_thumb' + thumb + rowid);
		Fabrik.loader.start(row);
		this.thumb = thumb;

		var data = {
			'option': 'com_fabrik',
			'format': 'raw',
			'task': 'plugin.pluginAjax',
			'plugin': 'thumbs',
			'method': 'ajax_rate',
			'g': 'element',
			'element_id': this.options.elid,
			'row_id': rowid,
			'elementname': this.options.elid,
			'userid': this.options.userid,
			'thumb': this.thumb,
			'listid': this.options.listid,
			'formid': this.options.formid,
			'add': add
		};
		new Request({url: '',
			'data': data,
			onComplete: function (r) {
				var count_thumbup = document.id('count_thumbup' + rowid);
				var count_thumbdown = document.id('count_thumbdown' + rowid);
				var thumbup = row.getElements('.thumbup');
				var thumbdown = row.getElements('.thumbdown');
				Fabrik.loader.stop(row);
				//r = r.split(this.options.splitter2);
				r = JSON.decode(r);
				if (r.error) {
					console.log(r.error);
				} else {
					if (Fabrik.bootstrapped) {
						
						row.getElement('button.thumb-up .thumb-count').set('text', r[0]);
						if (typeOf(row.getElement('button.thumb-down')) !== 'null') {
							row.getElement('button.thumb-down .thumb-count').set('text', r[1]);
						}
					} else {
						count_thumbup.set('html', r[0]);
						count_thumbdown.set('html', r[1]);
					}
				}
			}.bind(this)
		}).send();
	}
});