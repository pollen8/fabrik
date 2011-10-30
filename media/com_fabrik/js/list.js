/**
 * @author Robert
 */

/* jshint mootools: true */
/*
 * global Fabrik:true, fconsole:true, Joomla:true, CloneObject:true, $A:true,
 * $H:true,unescape:true,head:true,FbListActions:true,FbGroupedToggler:true,FbListKeys:true
 */

var FbListPlugin = new Class({
	Implements: [Events, Options],
	options: {
		requireChecked: true
	},
	initialize: function (options) {
		this.setOptions(options);
		this.result = true; // set this to false in window.fireEvents to stop
												// current action (eg stop ordering when
												// fabrik.list.order run)
		head.ready(function () {
			this.listform = this.getList().getForm();
			var l = this.listform.getElement('input[name=listid]');
			// in case its in a viz
			if (typeOf(l) === 'null') {
				return;
			}
			this.listid = l.value;
			this.watchButton();
		}.bind(this));
	},

	getList: function () {
		return Fabrik.blocks['list_' + this.options.ref];
	},

	clearFilter: Function.from(),

	watchButton: function () {
		//do relay for floating menus
		if (typeOf(this.options.name) === 'null') {
			return;
		}
		document.addEvent('click:relay(.' + this.options.name + ')', function (e) {
			e.stop();
			var row, chx;
			// if the row button is clicked check its associated checkbox
			if (e.target.getParent('.fabrik_row')) {
				row = e.target.getParent('.fabrik_row');
				if (row.getElement('input[name^=ids]')) {
					chx = row.getElement('input[name^=ids]');
					this.listform.getElements('input[name^=ids]').set('checked', false);
					chx.set('checked', true);
				}
			}

			// check that at least one checkbox is checked
			var ok = false;
			this.listform.getElements('input[name^=ids]').each(function (c) {
				if (c.checked) {
					ok = true;
				}
			});
			// heading button pressed so check all checkboxes
			row = e.target.getParent('.fabrik___heading');
			if (row && ok === false) {
				this.listform.getElements('input[name^=ids]').set('checked', true);
				this.listform.getElement('input[name=checkAll]').set('checked', true);
				ok = true;
			}
			if (!ok && this.options.requireChecked) {
				alert(Joomla.JText._('COM_FABRIK_PLEASE_SELECT_A_ROW'));
				return;
			}
			var n = this.options.name.split('-');
			this.listform.getElement('input[name=fabrik_listplugin_name]').value = n[0];
			this.listform.getElement('input[name=fabrik_listplugin_renderOrder]').value = n.getLast();
			this.buttonAction();
		}.bind(this));
	},

	buttonAction: function () {
		this.list.submit('doPlugin');
	}
});

var FbListFilter = new Class({

	Implements: [Options, Events],

	options: {
		'container': '',
		'type': 'list',
		'id': '',
		'advancedSearch': {}
	},

	initialize: function (options) {
		this.filters = $H({});
		this.setOptions(options);
		this.container = document.id(this.options.container);

		this.filterContainer = this.container.getElement('.fabrikFilterContainer');
		var b = this.container.getElement('.toggleFilters');
		if (typeOf(b) !== 'null') {
			b.addEvent('click', function (e) {
				var dims = b.getPosition();
				e.stop();
				var x = dims.x - this.filterContainer.getWidth();
				var y = dims.y + b.getHeight();
				var rx = this.filterContainer.getStyle('display') === 'none' ? this.filterContainer.show() : this.filterContainer.hide();
				this.filterContainer.fade('toggle');
				this.container.getElements('.filter').toggle();
			}.bind(this));

			if (typeOf(this.filterContainer) !== 'null') {
				this.filterContainer.fade('hide').hide();
				this.container.getElements('.filter').toggle();
			}
		}

		if (typeOf(this.container) === 'null') {
			return;
		}
		this.getList();
		var c = this.container.getElement('.clearFilters');
		if (typeOf(c) !== 'null') {
			c.removeEvents();
			c.addEvent('click', function (e) {
				e.stop();
				this.container.getElements('.fabrik_filter').each(function (f) {
					if (f.get('tag') === 'select') {
						f.selectedIndex = 0;
					} else {
						f.value = '';
					}
				});
				this.getList().plugins.each(function (p) {
					p.clearFilter();
				});
				new Element('input', {
					'name': 'resetfilters',
					'value': 1,
					'type': 'hidden'
				}).inject(this.container);
				if (this.options.type === 'list') {
					this.list.submit('list.clearfilter');
				} else {
					this.container.getElement('form[name=filter]').submit();
				}
			}.bind(this));
		}
		if (advancedSearch = this.container.getElement('.advanced-search-link')) {
			advancedSearch.addEvent('click', function (e) {
				e.stop();
				var url = Fabrik.liveSite + "index.php?option=com_fabrik&view=list&tmpl=component&layout=_advancedsearch&listid=" + this.options.id;
				this.windowopts = {
					'id': 'advanced-search-win',
					title: Joomla.JText._('COM_FABRIK_ADVANCED_SEARCH'),
					loadMethod: 'xhr',
					evalScripts: true,
					contentURL: url,
					width: 690,
					height: 300,
					y: this.options.popwiny,
					onContentLoaded: function (win) {
						new AdvancedSearch(this.options.advancedSearch);
					}.bind(this)
				};
				var mywin = Fabrik.getWindow(this.windowopts);
			}.bind(this));
		}
	},

	getList: function () {
		this.list = Fabrik.blocks[this.options.type + '_' + this.options.ref];
		return this.list;
	},

	addFilter: function (plugin, f) {
		if (this.filters.has(plugin) === false) {
			this.filters.set(plugin, []);
		}
		this.filters.get(plugin).push(f);
	},

	// $$$ hugh - added this primarily for CDD element, so it can get an array to
	// emulate submitted form data
	// for use with placeholders in filter queries. Mostly of use if you have
	// daisy chained CDD's.
	getFilterData: function () {
		var h = {};
		this.container.getElements('.fabrik_filter').each(function (f) {
			if (f.id.test(/value$/)) {
				var key = f.id.match(/(\S+)value$/)[1];
				// $$$ rob added check that something is select - possbly causes js
				// error in ie
				if (f.get('tag') === 'select' && f.selectedIndex !== -1) {
					h[key] = document.id(f.options[f.selectedIndex]).get('text');
				} else {
					h[key] = f.get('value');
				}
				h[key + '_raw'] = f.get('value');
			}
		}.bind(this));
		return h;
	},

	update: function () {
		this.filters.each(function (fs, plugin) {
			fs.each(function (f) {
				f.update();
			}.bind(this));
		}.bind(this));
	}
});

var FbList = new Class({

	Implements: [Options, Events],

	options: {
		'admin': false,
		'filterMethod': 'onchange',
		'ajax': false,
		'ajax_links': false,
		'form': 'listform_' + this.id,
		'hightLight': '#ccffff',
		'primaryKey': '',
		'headings': [],
		'labels': {},
		'Itemid': 0,
		'formid': 0,
		'canEdit': true,
		'canView': true,
		'page': 'index.php',
		'actionMethod': '',
		'formels': [], // elements that only appear in the form
		'data': [], // [{col:val, col:val},...] (depreciated)
		'rowtemplate': '',
		'floatPos': 'left',
		'csvChoose': false,
		'csvOpts': {}
	},

	initialize: function (id, options) {
		this.id = id;
		this.setOptions(options);
		this.getForm();
		this.list = document.id('list_' + this.id);
		this.actionManager = new FbListActions(this, {
			'method': this.options.actionMethod,
			'floatPos': this.options.floatPos
		});
		new FbGroupedToggler(this.form);
		new FbListKeys(this);
		if (this.list) {
			this.tbody = this.list.getElement('tbody');
			if (typeOf(this.tbody) === 'null') {
				this.tbody = this.list;
			}
			// $$$ rob mootools 1.2 has bug where we cant set('html') on table
			// means that there is an issue if table contains no data
			if (window.ie) {
				this.options.rowtemplate = this.list.getElement('.fabrik_row');
			}
		}
		this.watchAll(false);
		Fabrik.addEvent('fabrik.form.submitted', function () {
			this.updateRows();
		}.bind(this));
	},

	setRowTemplate: function () {
		// $$$ rob mootools 1.2 has bug where we cant setHTML on table
		// means that there is an issue if table contains no data
		if (typeOf(this.options.rowtemplate) === 'string') {
			var r = this.list.getElement('.fabrik_row');
			if (window.ie && typeOf(r) !== 'null') {
				this.options.rowtemplate = r;
			}
		}
	},

	watchAll: function (ajaxUpdate) {
		ajaxUpdate = ajaxUpdate ? ajaxUpdate : false;
		this.watchNav();
		if (!ajaxUpdate) {
			this.watchRows();
		}
		this.watchFilters();
		this.watchOrder();
		this.watchEmpty();
		this.watchButtons();
	},

	watchButtons: function () {
		this.exportWindowOpts = {
			id: 'exportcsv',
			title: 'Export CSV',
			loadMethod: 'html',
			minimizable: false,
			width: 360,
			height: 120,
			content: ''
		};
		if (this.options.view === 'csv') {
			//for csv links e.g. index.php?option=com_fabrik&view=csv&listid=10
			this.openCSVWindow();
		} else {
			if (this.form.getElements('.csvExportButton')) {
				this.form.getElements('.csvExportButton').each(function (b) {
					if (b.hasClass('custom') === false) {
						b.addEvent('click', function (e) {
							this.openCSVWindow();
						}.bind(this));
					}
				}.bind(this));
			}
		}
	},
	
	openCSVWindow: function () {
		var thisc = this.makeCSVExportForm();
		this.form.getElements('.fabrik_filter').each(function (f) {
			var fc = new Element('input', {
				'type': 'hidden',
				'name': f.name,
				'id': f.id,
				'value': f.get('value')
			});
			fc.inject(thisc);
		}.bind(this));
		this.exportWindowOpts.content = thisc;
		this.exportWindowOpts.onContentLoaded = function () {
			this.fitToContent();
		};
		Fabrik.getWindow(this.exportWindowOpts);
	},

	makeCSVExportForm: function () {
		if (this.options.csvChoose) {
			return this._csvExportForm();
		} else {
			return this._csvAutoStart();
		}
	},
	
	_csvAutoStart: function () {
		var c = new Element('div', {
			'id': 'csvmsg'
		}).set('html', Joomla.JText._('COM_FABRIK_LOADING') + ' <br /><span id="csvcount">0</span> / <span id="csvtotal"></span> ' + Joomla.JText._('COM_FABRIK_RECORDS') + '.<br/>' + Joomla.JText._('COM_FABRIK_SAVING_TO') + '<span id="csvfile"></span>');
		
		this.csvopts = this.options.csvOpts;
		this.csvfields = this.options.csvFields;
		
		this.triggerCSVImport(-1);
		return c;
	},
	
	_csvExportForm: function () {
		// cant build via dom as ie7 doesn't accept checked status
		var rad = "<input type='radio' value='1' name='incfilters' checked='checked' />" + Joomla.JText._('JYES');
		var rad2 = "<input type='radio' value='1' name='incraw' checked='checked' />" + Joomla.JText._('JYES');
		var rad3 = "<input type='radio' value='1' name='inccalcs' checked='checked' />" + Joomla.JText._('JYES');
		var rad4 = "<input type='radio' value='1' name='inctabledata' checked='checked' />" + Joomla.JText._('JYES');
		var rad5 = "<input type='radio' value='1' name='excel' checked='checked' />Excel CSV";
		var url = 'index.php?option=com_fabrik&view=list&listid=' + this.id + '&format=csv';

		var divopts = {
			'styles': {
				'width': '200px',
				'float': 'left'
			}
		};
		var c = new Element('form', {
			'action': url,
			'method': 'post'
		}).adopt([new Element('div', divopts).set('text', Joomla.JText._('COM_FABRIK_FILE_TYPE')), new Element('label').set('html', rad5), new Element('label').adopt([new Element('input', {
			'type': 'radio',
			'name': 'excel',
			'value': '0'
		}), new Element('span').set('text', 'CSV')]), new Element('br'), new Element('br'), new Element('div', divopts).appendText(Joomla.JText._('COM_FABRIK_INCLUDE_FILTERS')), new Element('label').set('html', rad), new Element('label').adopt([new Element('input', {
			'type': 'radio',
			'name': 'incfilters',
			'value': '0'
		}), new Element('span').set('text', Joomla.JText._('JNO'))]), new Element('br'), new Element('div', divopts).appendText(Joomla.JText._('COM_FABRIK_INCLUDE_DATA')), new Element('label').set('html', rad4), new Element('label').adopt([new Element('input', {
			'type': 'radio',
			'name': 'inctabledata',
			'value': '0'
		}), new Element('span').set('text', Joomla.JText._('JNO'))]), new Element('br'), new Element('div', divopts).appendText(Joomla.JText._('COM_FABRIK_INCLUDE_RAW_DATA')), new Element('label').set('html', rad2), new Element('label').adopt([new Element('input', {
			'type': 'radio',
			'name': 'incraw',
			'value': '0'
		}), new Element('span').set('text', Joomla.JText._('JNO'))]), new Element('br'), new Element('div', divopts).appendText(Joomla.JText._('COM_FABRIK_INLCUDE_CALCULATIONS')), new Element('label').set('html', rad3), new Element('label').adopt([new Element('input', {
			'type': 'radio',
			'name': 'inccalcs',
			'value': '0'
		}), new Element('span').set('text', Joomla.JText._('JNO'))])]);
		new Element('h4').set('text', Joomla.JText._('COM_FABRIK_SELECT_COLUMNS_TO_EXPORT')).inject(c);
		var g = '';
		var i = 0;
		$H(this.options.labels).each(function (label, k) {
			if (k.substr(0, 7) !== 'fabrik_' && k !== '____form_heading') {
				var newg = k.split('___')[0];
				if (newg !== g) {
					g = newg;
					new Element('h5').set('text', g).inject(c);
				}
				var rad = "<input type='radio' value='1' name='fields[" + k + "]' checked='checked' />" + Joomla.JText._('JYES');
				label = label.replace(/<\/?[^>]+(>|$)/g, "");
				var r = new Element('div', divopts).appendText(label);
				r.inject(c);
				new Element('label').set('html', rad).inject(c);
				new Element('label').adopt([new Element('input', {
					'type': 'radio',
					'name': 'fields[' + k + ']',
					'value': '0'
				}), new Element('span').appendText(Joomla.JText._('JNO'))]).inject(c);
				new Element('br').inject(c);
			}
			i++;
		}.bind(this));

		// elements not shown in table
		if (this.options.formels.length > 0) {
			new Element('h5').set('text', Joomla.JText._('COM_FABRIK_FORM_FIELDS')).inject(c);
			this.options.formels.each(function (el) {
				var rad = "<input type='radio' value='1' name='fields[" + el.name + "]' checked='checked' />" + Joomla.JText._('JYES');
				var r = new Element('div', divopts).appendText(el.label);
				r.inject(c);
				new Element('label').set('html', rad).inject(c);
				new Element('label').adopt([new Element('input', {
					'type': 'radio',
					'name': 'fields[' + el.name + ']',
					'value': '0'
				}), new Element('span').set('text', Joomla.JText._('JNO'))]).inject(c);
				new Element('br').inject(c);
			}.bind(this));
		}

		new Element('div', {
			'styles': {
				'text-align': 'right'
			}
		}).adopt(new Element('input', {
			'type': 'button',
			'name': 'submit',
			'value': Joomla.JText._('COM_FABRIK_EXPORT'),
			'class': 'button',
			events: {
				'click': function (e) {
					e.stop();
					e.target.disabled = true;
					new Element('div', {
						'id': 'csvmsg'
					}).set('html', Joomla.JText._('COM_FABRIK_LOADING') + ' <br /><span id="csvcount">0</span> / <span id="csvtotal"></span> ' + Joomla.JText._('COM_FABRIK_RECORDS') + '.<br/>' + Joomla.JText._('COM_FABRIK_SAVING_TO') + '<span id="csvfile"></span>').inject(e.target, 'before');
					this.triggerCSVImport(0);
				}.bind(this)

			}
		})).inject(c);
		new Element('input', {
			'type': 'hidden',
			'name': 'view',
			'value': 'table'
		}).inject(c);
		new Element('input', {
			'type': 'hidden',
			'name': 'option',
			'value': 'com_fabrik'
		}).inject(c);
		new Element('input', {
			'type': 'hidden',
			'name': 'listid',
			'value': this.id
		}).inject(c);
		new Element('input', {
			'type': 'hidden',
			'name': 'format',
			'value': 'csv'
		}).inject(c);
		new Element('input', {
			'type': 'hidden',
			'name': 'c',
			'value': 'table'
		}).inject(c);
		return c;
	},

	triggerCSVImport: function (start, opts, fields) {
		if (start !== 0) {
			if (start === -1) {
				// not triggered from front end selections
				start = 0;
				opts = this.csvopts;
				opts.fields = this.csvfields;
			} else {
				opts = this.csvopts;
				fields = this.csvfields;
			}
		} else {
			if (!opts) {
				opts = {};
				if (typeOf(document.id('exportcsv')) !== 'null') {
					$A(['incfilters', 'inctabledata', 'incraw', 'inccalcs', 'excel']).each(function (v) {
						var inputs = document.id('exportcsv').getElements('input[name=' + v + ']');
						if (inputs.length > 0) {
							opts[v] = inputs.filter(function (i) {
								return i.checked;
							})[0].value;
						}
					});
				}
			}
			// selected fields
			if (!fields) {
				fields = {};
				if (typeOf(document.id('exportcsv')) !== 'null') {
					document.id('exportcsv').getElements('input[name^=field]').each(function (i) {
						if (i.checked) {
							var k = i.name.replace('fields[', '').replace(']', '');
							fields[k] = i.get('value');
						}
					});
				}
			}
			opts.fields = fields;
			this.csvopts = opts;
			this.csvfields = fields;
		}

		opts.start = start;
		opts.option = 'com_fabrik';
		opts.view = 'list';
		opts.format = 'csv';
		opts.listid = this.id;
		var myAjax = new Request.JSON({
			url: '',
			method: 'post',
			data: opts,
			onSuccess: function (res) {
				if (res.err) {
					alert(res.err);
				} else {
					if (typeOf(document.id('csvcount')) !== 'null') {
						document.id('csvcount').set('text', res.count);
					}
					if (typeOf(document.id('csvtotal')) !== 'null') {
						document.id('csvtotal').set('text', res.total);
					}
					if (typeOf(document.id('csvfile')) !== 'null') {
						document.id('csvfile').set('text', res.file);
					}
					if (res.count < res.total) {
						this.triggerCSVImport(res.count);
					} else {
						var finalurl = Fabrik.liveSite + 'index.php?option=com_fabrik&view=list&format=csv&listid=' + this.id + '&start=' + res.count;
						var msg = Joomla.JText._('COM_FABRIK_CSV_COMPLETE');
						msg += ' <a href="' + finalurl + '">' + Joomla.JText._('COM_FABRIK_CSV_DOWNLOAD_HERE') + '</a>';
						if (typeOf(document.id('csvmsg')) !== 'null') {
							document.id('csvmsg').set('html', msg);
						}
					}
				}
			}.bind(this)
		});
		myAjax.send();
	},

	addPlugins: function (a) {
		a.each(function (p) {
			p.list = this;
		}.bind(this));
		this.plugins = a;
	},

	watchEmpty: function (e) {
		var b = document.id(this.options.form).getElement('.doempty', this.options.form);
		if (b) {
			b.addEvent('click', function (e) {
				e.stop();
				if (confirm(Joomla.JText._('COM_FABRIK_CONFIRM_DROP'))) {
					this.submit('list.doempty');
				}
			}.bind(this));
		}
	},

	watchOrder: function () {
		var hs = document.id(this.options.form).getElements('.fabrikorder, .fabrikorder-asc, .fabrikorder-desc');
		hs.removeEvents('click');
		hs.each(function (h) {
			h.addEvent('click', function (e) {
				var orderdir = '';
				var newOrderClass = '';
				// $$$ rob in pageadaycalendar.com h was null so reset to e.target
				h = document.id(e.target);
				var td = h.getParent('.fabrik_ordercell');
				if (h.tagName !== 'a') {
					h = td.getElement('a');
				}
				switch (h.className) {
				case 'fabrikorder-asc':
					newOrderClass = 'fabrikorder-desc';
					orderdir = 'desc';
					break;
				case 'fabrikorder-desc':
					newOrderClass = 'fabrikorder';
					orderdir = "-";
					break;
				case 'fabrikorder':
					newOrderClass = 'fabrikorder-asc';
					orderdir = 'asc';
					break;
				}
				td = td.className.split(' ')[2].replace('_order', '').replace(/^\s+/g, '').replace(/\s+$/g, '');// chrome
																																																				// and
																																																				// safari
																																																				// you
																																																				// need
																																																				// to
																																																				// trim
																																																				// whitespace
				h.className = newOrderClass;
				this.fabrikNavOrder(td, orderdir);
				e.stop();
			}.bind(this));
		}.bind(this));

	},

	watchFilters: function () {
		var e = '';
		var submit = document.id(this.options.form).getElement('.fabrik_filter_submit');
		document.id(this.options.form).getElements('.fabrik_filter').each(function (f) {
			e = f.get('tag') === 'select' ? 'change' : 'blur';
			if (this.options.filterMethod !== 'submitform') {
				f.removeEvent(e);
				f.store('initialvalue', f.get('value'));
				//document.getElement('.fabrik_filter_submit').highlight('#ffaa00');
				f.addEvent(e, function (e) {
					e.stop();
					if (e.target.retrieve('initialvalue') !== e.target.get('value')) {
						this.submit('list.filter');
					}
				}.bind(this));
			} else {
				f.addEvent(e, function (e) {
					submit.highlight('#ffaa00');
				}.bind(this));
			}
		}.bind(this));
			
		if (this.options.filterMethod === 'submitform') {
			if (submit) {
				submit.removeEvents();
				submit.addEvent('click', function (e) {
					this.submit('list.filter');
				}.bind(this));
			}
		}
		document.id(this.options.form).getElements('.fabrik_filter').addEvent('keydown', function (e) {
			if (e.code === 13) {
				e.stop();
				this.submit('list.filter');
			}
		}.bind(this));
	},

	// highlight active row, deselect others
	setActive: function (activeTr) {
		this.list.getElements('.fabrik_row').each(function (tr) {
			tr.removeClass('activeRow');
		});
		activeTr.addClass('activeRow');
	},

	getActiveRow: function (e) {
		var row = e.target.getParent('.fabrik_row');
		if (!row) {
			row = this.activeRow;
		}
		return row;
	},

	watchRows: function () {
		if (!this.list) {
			return;
		}
		
		if (this.options.ajax_links) {
			// if floatiing menus then edit out side of this.list (not tested with
			// mutliple lists on the same page.
			// if theres an issue with that we may need to inject the floating menu
			// into the list container - issue with this
			// may be that posistioning will be effected
			
			document.addEvent('click:relay(.fabrik___rowlink)', function (e) {
				e.stop();
				var row = e.target;
				var url = row.get('href');
				url += url.contains('?') ? '&' : '?';
				url += 'tmpl=component&ajax=1';
				Fabrik.getWindow({
					'id': 'add.' + this.options.formid,
					'title': 'Edit',
					'loadMethod': 'xhr',
					'contentURL': url
				});
			}.bind(this));
			// this.list.addEvent('click:relay(.fabrik_edit)', function (e) {
			document.addEvent('click:relay(.fabrik_edit)', function (e) {
				e.stop();
				var row = this.getActiveRow(e);
				if (!row) {
					return;
				}
				this.setActive(row);
				var rowid = row.id.replace('list_' + this.id + '_row_', '');
				var url = Fabrik.liveSite + "index.php?option=com_fabrik&view=form&formid=" + this.options.formid + '&rowid=' + rowid + '&tmpl=component&ajax=1';
				// make id the same as the add button so we reuse the same form.
				Fabrik.getWindow({
					'id': 'add.' + this.options.formid,
					'title': 'Edit',
					'loadMethod': 'xhr',
					'contentURL': url
				});
			}.bind(this));

			// this.list.addEvent('click:relay(.fabrik_view)', function (e) {
			document.addEvent('click:relay(.fabrik_view)', function (e) {
				e.stop();
				var row = this.getActiveRow(e);
				if (!row) {
					return;
				}
				this.setActive(row);
				var rowid = row.id.replace('list_' + this.id + '_row_', '');
				var url = Fabrik.liveSite + "index.php?option=com_fabrik&view=details&formid=" + this.options.formid + '&rowid=' + rowid + '&tmpl=component&ajax=1';
				Fabrik.getWindow({
					'id': 'view.' + '.' + this.options.formid + '.' + rowid,
					'title': 'Details',
					'loadMethod': 'xhr',
					'contentURL': url
				});
			}.bind(this));
		}
	},

	getForm: function () {
		if (!this.form) {
			this.form = document.id(this.options.form);
		}
		return this.form;
	},

	submit: function (task) {
		this.getForm();
		if (task === 'list.delete') {
			var ok = false;
			this.form.getElements('input[name^=ids]').each(function (c) {
				if (c.checked) {
					ok = true;
				}
			});
			if (!ok) {
				alert(Joomla.JText._('COM_FABRIK_SELECT_ROWS_FOR_DELETION'));
				Fabrik.loader.stop('listform_' + this.id);
				return false;
			}
			if (!confirm(Joomla.JText._('COM_FABRIK_CONFIRM_DELETE'))) {
				Fabrik.loader.stop('listform_' + this.id);
				return false;
			}
		}
		Fabrik.loader.start('listform_' + this.id);
		if (task === 'list.filter') {
			this.form.task.value = task;
			if (this.form['limitstart' + this.id]) {
				this.form.getElement('#limitstart' + this.id).value = 0;
			}
		} else {
			if (task !== '') {
				this.form.task.value = task;
			}
		}
		if (this.options.ajax) {
			// for module & mambot
			// $$$ rob with modules only set view/option if ajax on
			this.form.getElement('input[name=option]').value = 'com_fabrik';
			this.form.getElement('input[name=view]').value = 'list';
			this.form.getElement('input[name=format]').value = 'raw';
			if (!this.request) {
				this.request = new Request({
					'url': this.form.get('action'),
					'data': this.form,
					onComplete: function (json) {
						json = JSON.decode(json);
						this._updateRows(json);
						Fabrik.loader.stop('listform_' + this.id);
					}.bind(this)
				});
			}
			this.request.send();
			Fabrik.fireEvent('fabrik.list.submit', [task, this.form.toQueryString().toObject()]);
		} else {
			this.form.submit();
			Fabrik.loader.stop('listform_' + this.id);
		}
		return false;
	},

	fabrikNav: function (limitStart) {
		this.options.limitStart = limitStart;
		this.form.getElement('#limitstart' + this.id).value = limitStart;
		// cant do filter as that resets limitstart to 0
		Fabrik.fireEvent('fabrik.list.navigate', [this, limitStart]);
		if (!this.result) {
			this.result = true;
			return false;
		}
		this.submit('list.view');
		return false;
	},

	fabrikNavOrder: function (orderby, orderdir) {
		this.form.orderby.value = orderby;
		this.form.orderdir.value = orderdir;
		Fabrik.fireEvent('fabrik.list.order', [this, orderby, orderdir]);
		if (!this.result) {
			this.result = true;
			return false;
		}
		this.submit('list.order');
	},

	removeRows: function (rowids) {
		// @TODO: try to do this with FX.Elements
		for (i = 0; i < rowids.length; i++) {
			var row = document.id('list_' + this.id + '_row_' + rowids[i]);
			var highlight = new Fx.Morph(row, {
				duration: 1000
			});
			highlight.start({
				'backgroundColor': this.options.hightLight
			}).chain(function () {
				this.start({
					'opacity': 0
				});
			}).chain(function () {
				row.dispose();
				this.checkEmpty();
			}.bind(this));
		}
	},

	editRow: function () {
	},

	clearRows: function () {
		this.list.getElements('.fabrik_row').each(function (tr) {
			tr.dispose();
		});
	},

	updateRows: function () {
		var data = {
				'option': 'com_fabrik',
				'view': 'list',
				'task': 'list.view',
				'format': 'raw',
				'listid': this.id
			};
		//var url = Fabrik.liveSite + 'index.php?option=com_fabrik&view=list&format=raw&listid=' + this.id;
		var url = '';
		data['limit' + this.id] = this.options.limitLength;
		new Request.JSON({
			'url': url,
			'data': data,
			onSuccess: function (json) {
				this._updateRows(json);
				// Fabrik.fireEvent('fabrik.list.update', [this, json]);
			}.bind(this)
		}).send();
	},

	_updateRows: function (data) {
		if (data.id === this.id && data.model === 'list') {
			var header = document.id(this.options.form).getElements('.fabrik___heading').getLast();
			var headings = new Hash(data.headings);
			headings.each(function (data, key) {
				key = "." + key;
				try {
					if (typeOf(header.getElement(key)) !== 'null') {
						// $$$ rob 28/10/2011 just alter span to allow for maintaining filter toggle links
						header.getElement(key).getElement('span').set('html', data);
					}
				} catch (err) {
					fconsole(err);
				}
			});

			this.clearRows();
			var counter = 0;
			var rowcounter = 0;
			trs = [];
			this.options.data = data.data;
			if (data.calculations) {
				this.updateCals(data.calculations);
			}
			if (typeOf(this.form.getElement('.fabrikNav')) !== 'null') {
				this.form.getElement('.fabrikNav').set('html', data.htmlnav);
			}
			this.setRowTemplate();
			// $$$ rob was $H(data.data) but that wasnt working ????
			// testing with $H back in again for grouped by data? Yeah works for
			// grouped data!!
			var gdata = this.options.isGrouped ? $H(data.data) : data.data;
			var gcounter = 0;
			gdata.each(function (groupData, groupKey) {
				var container, thisrowtemplate;
				var tbody = this.options.isGrouped ? this.list.getElements('.fabrik_groupdata')[gcounter] : this.tbody;
				gcounter++;
				for (i = 0; i < groupData.length; i++) {

					if (typeOf(this.options.rowtemplate) === 'string') {
						container = (!this.options.rowtemplate.match(/<tr/)) ? 'div' : 'table';
						thisrowtemplate = new Element(container);
						thisrowtemplate.set('html', this.options.rowtemplate);
					} else {
						container = this.options.rowtemplate.get('tag') === 'tr' ? 'table' : 'div';
						thisrowtemplate = new Element(container);
						// ie tmp fix for mt 1.2 setHTML on table issue
						thisrowtemplate.adopt(this.options.rowtemplate.clone());
					}
					var row = $H(groupData[i]);
					$H(row.data).each(function (val, key) {
						var rowk = '.' + key;
						var cell = thisrowtemplate.getElement(rowk);
						if (typeOf(cell) !== 'null') {
							cell.set('html', val);
						}
						rowcounter++;
					}.bind(this));
					// thisrowtemplate.getElement('.fabrik_row').id = 'list_' + this.id +
					// '_row_' + row.get('__pk_val');
					thisrowtemplate.getElement('.fabrik_row').id = row.id;
					if (typeOf(this.options.rowtemplate) === 'string') {
						var c = thisrowtemplate.getElement('.fabrik_row').clone();
						c.id = row.id;
						c.inject(tbody);
					} else {
						var r = thisrowtemplate.getElement('.fabrik_row');
						r.inject(tbody);
						thisrowtemplate.empty();
					}
					counter++;
				}
			}.bind(this));

			var fabrikDataContainer = this.list.getParent('.fabrikDataContainer');
			var emptyDataMessage = this.list.getParent('.fabrikForm').getElement('.emptyDataMessage');
			if (rowcounter === 0) {
				/*
				 * if (typeOf(fabrikDataContainer) !== 'null') {
				 * fabrikDataContainer.setStyle('display', 'none'); }
				 */
				if (typeOf(emptyDataMessage) !== 'null') {
					emptyDataMessage.setStyle('display', '');
				}
			} else {
				if (typeOf(fabrikDataContainer) !== 'null') {
					fabrikDataContainer.setStyle('display', '');
				}
				if (typeOf(emptyDataMessage) !== 'null') {
					emptyDataMessage.setStyle('display', 'none');
				}
			}
			if (typeOf(this.form.getElement('.fabrikNav')) !== 'null') {
				this.form.getElement('.fabrikNav').set('html', data.htmlnav);
			}
			this.watchAll(true);
			Fabrik.fireEvent('fabrik.list.updaterows');
			try {
				Slimbox.scanPage();
			} catch (err) {
				fconsole('slimbox scan:' + err);
			}
			try {
				Mediabox.scanPage();
			} catch (err2) {
				fconsole('mediabox scan:' + err2);
			}
			Fabrik.fireEvent('fabrik.list.update', [this, data]);
		}
		this.stripe();
		Fabrik.loader.stop('listform_' + this.id);
	},

	addRow: function (obj) {
		var r = new Element('tr', {
			'class': 'oddRow1'
		});
		var x = {
			test: 'hi'
		};
		for (var i in obj) {
			if (this.options.headings.indexOf(i) !== -1) {
				var td = new Element('td', {}).appendText(obj[i]);
				r.appendChild(td);
			}
		}
		r.inject(this.tbody);
	},

	addRows: function (aData) {
		for (i = 0; i < aData.length; i++) {
			for (j = 0; j < aData[i].length; j++) {
				this.addRow(aData[i][j]);
			}
		}
		this.stripe();
	},

	stripe: function () {
		var trs = this.list.getElements('.fabrik_row');
		for (i = 0; i < trs.length; i++) {
			if (i !== 0) { // ignore heading
				var row = 'oddRow' + (i % 2);
				trs[i].addClass(row);
			}
		}
	},

	checkEmpty: function () {
		var trs = this.list.getElements('tr');
		if (trs.length === 2) {
			this.addRow({
				'label': Joomla.JText._('COM_FABRIK_NO_RECORDS')
			});
		}
	},

	watchCheckAll: function (e) {
		var checkAll = this.form.getElement('input[name=checkAll]');
		if (typeOf(checkAll) !== 'null') {
			// IE wont fire an event on change until the checkbxo is blurred!
			checkAll.addEvent('click', function (e) {
				var p = this.list.getParent('.fabrikList') ? this.list.getParent('.fabrikList') : this.list;
				var chkBoxes = p.getElements('input[name^=ids]');
				c = !e.target.checked ? '' : 'checked';
				for (var i = 0; i < chkBoxes.length; i++) {
					chkBoxes[i].checked = c;
					this.toggleJoinKeysChx(chkBoxes[i]);
				}
				// event.stop(); dont event stop as this stops the checkbox being
				// selected
			}.bind(this));
		}
		this.form.getElements('input[name^=ids]').each(function (i) {
			i.addEvent('change', function (e) {
				this.toggleJoinKeysChx(i);
			}.bind(this));
		}.bind(this));
	},

	toggleJoinKeysChx: function (i) {
		i.getParent().getElements('input[class=fabrik_joinedkey]').each(function (c) {
			c.checked = i.checked;
		});
	},

	watchNav: function (e) {
		var limitBox = this.form.getElement('#limit' + this.id);
		if (limitBox) {
			limitBox.addEvent('change', function (e) {
				var res = Fabrik.fireEvent('fabrik.list.limit', [this]);
				if (this.result === false) {
					this.result = true;
					return false;
				}
				this.submit('list.filter');
			}.bind(this));
		}
		var addRecord = this.form.getElement('.addRecord');
		if (typeOf(addRecord) !== 'null' && (this.options.ajax)) {
			addRecord.removeEvents();
			addRecord.addEvent('click', function (e) {
				e.stop();
				// top.Fabrik.fireEvent('fabrik.list.add', this);//for packages?
				Fabrik.getWindow({
					'id': 'add-' + this.id,
					'title': 'Add',
					'loadMethod': 'xhr',
					'contentURL': addRecord.href
				});
			}.bind(this));
		}
		var del = document.getElements('.fabrik_delete a');
		document.addEvent('click:relay(.fabrik_delete a)', function (e) {
			var r = e.target.getParent('.fabrik_row');
			if (!r) {
				r = this.activeRow;
			}
			if (r) {
				var chx = r.getElement('input[type=checkbox][name*=id]');
				if (typeOf(chx) !== 'null') {
					// if delete link is in hover box the we cant find the associated chx
					// box
					this.form.getElements('input[type=checkbox][name*=id], input[type=checkbox][name=checkAll]').each(function (c) {
						// $$$ rob hmm no! this meant if you selected 2 records to delete only the last selected recod would be deleted.
						//c.checked = false;
					});
				}
				if (typeOf(chx) !== 'null') {
					chx.checked = true;
				}
			} else {
				// checkAll
				this.form.getElements('input[type=checkbox][name*=id], input[type=checkbox][name=checkAll]').each(function (c) {
					c.checked = true;
				});
			}
			if (!this.submit('list.delete')) {
				e.stop();
			}
		}.bind(this));

		if (document.id('fabrik__swaptable')) {
			document.id('fabrik__swaptable').addEvent('change', function (e) {
				window.location = 'index.php?option=com_fabrik&task=list.view&cid=' + e.target.get('value');
			}.bind(this));
		}
		if (this.options.ajax) {
			if (typeOf(this.form.getElement('.pagination')) !== 'null') {
				this.form.getElement('.pagination').getElements('.pagenav').each(function (a) {
					a.addEvent('click', function (e) {
						e.stop();
						if (a.get('tag') === 'a') {
							var o = a.href.toObject();
							this.fabrikNav(o['limitstart' + this.id]);
						}
					}.bind(this));
				}.bind(this));
			}
		}
		this.watchCheckAll();
	},

	/**
	 * currently only called from element raw view when using inline edit plugin
	 * might need to use for ajax nav as well?
	 */

	updateCals: function (json) {
		var types = ['sums', 'avgs', 'count', 'medians'];
		this.form.getElements('.fabrik_calculations').each(function (c) {
			types.each(function (type) {
				$H(json[type]).each(function (val, key) {
					var target = c.getElement('.fabrik_row___' + key);
					if (typeOf(target) !== 'null') {
						target.set('html', val);
					}
				});
			});
		});
	}
});

/**
 * observe keyboard short cuts
 */

var FbListKeys = new Class({
	initialize: function (list) {
		window.addEvent('keyup', function (e) {
			if (e.alt) {
				switch (e.key) {
				case Joomla.JText._('COM_FABRIK_LIST_SHORTCUTS_ADD'):
					var a = list.form.getElement('.addRecord');
					if (list.options.ajax) {
						a.fireEvent('click');
					}
					if (a.getElement('a')) {
						list.options.ajax ? a.getElement('a').fireEvent('click') : document.location = a.getElement('a').get('href');
					} else {
						if (!list.options.ajax) {
							document.location = a.get('href');
						}
					}
					break;

				case Joomla.JText._('COM_FABRIK_LIST_SHORTCUTS_EDIT'):
					fconsole('edit');
					break;
				case Joomla.JText._('COM_FABRIK_LIST_SHORTCUTS_DELETE'):
					fconsole('delete');
					break;
				case Joomla.JText._('COM_FABRIK_LIST_SHORTCUTS_FILTER'):
					fconsole('filter');
					break;
				}
			}
		}.bind(this));
	}
});

/**
 * toggle grouped data by click on the grouped headings icon
 */

var FbGroupedToggler = new Class({
	initialize: function (container) {
		container.addEvent('click:relay(.fabrik_groupheading a.toggle)', function (e) {
			e.stop();
			var h = e.target.getParent('.fabrik_groupheading');
			var img = h.getElement('img');
			var state = img.retrieve('showgroup', true);
			var rows = h.getParent().getNext();
			state ? rows.hide() : rows.show();
			if (state) {
				img.src = img.src.replace('orderasc', 'orderneutral');
			} else {
				img.src = img.src.replace('orderneutral', 'orderasc');
			}
			state = state ? false : true;
			img.store('showgroup', state);
		});
	}
});

/**
 * set up and show/hide list actions for each row
 */
var FbListActions = new Class({

	Implements: [Options],
	options: {
		'method': '',
		'floatPos': 'bottom'
	},

	initialize: function (list, options) {
		this.setOptions(options);
		this.list = list; // main list js object
		this.actions = [];
		this.setUpSubMenus();
		Fabrik.addEvent('fabrik.list.update', function (list, json) {
			this.observe();
		}.bind(this));
		this.observe();
	},

	observe: function () {
		if (this.options.method === 'floating') {
			this.setUpFloating();
		} else {
			this.setUpDefault();
		}
	},

	setUpSubMenus: function () {
		if (!this.list.form) {
			return;
		}
		this.actions = this.list.form.getElements('ul.fabrik_action');
		this.actions.each(function (ul) {
			// sub menus ie group by options
			if (ul.getElement('ul')) {
				var el = ul.getElement('ul');
				var c = el.clone();
				var trigger = el.getParent();
				var tip = new FloatingTips(trigger, {
					showOn: 'click',
					position: 'bottom',
					html: true,
					content: c
				});
				el.dispose();
			}
		});
	},

	setUpDefault: function () {
		this.actions = this.list.form.getElements('ul.fabrik_action');
		this.actions.each(function (ul) {
			if (ul.getParent().hasClass('fabrik_buttons')) {
				return;
			}
			ul.fade(0.6);
			var r = ul.getParent('.fabrik_row') ? ul.getParent('.fabrik_row') : ul.getParent('.fabrik___heading');
			if (r) {
				// $$$ hugh - for some strange reason, if we use 1 the object disappears
				// in Chrome and Safari!
				r.addEvents({
					'mouseenter': function (e) {
						ul.fade(0.99);
					},
					'mouseleave': function (e) {
						ul.fade(0.6);
					}
				});
			}
		});
	},

	setUpFloating: function () {

		this.list.form.getElements('ul.fabrik_action').each(function (ul) {
			if (ul.getParent('.fabrik_row')) {
				if (i = ul.getParent('.fabrik_row').getElement('input[type=checkbox]')) {
					var hideFn = function (e, elem, leaving) {
						if (!e.target.checked) {
							this.hide(e, elem);
						}
						if (leaving === 'tip') {
							// elem.checked = false; (cant do this otherwise delete
							// confirmation wont delete anything
						}
					};

					var showFn = function (e) {
						if (e.target.checked) {
							this.show(e.target);
						}
					};

					var c = function (el, o) {
						this.activeRow = ul.getParent('.fabrik_row');
						return ul.getParent();
					}.bind(this.list);

					var tip = new FloatingTips(i, {
						position: this.options.floatPos,
						html: true,
						showOn: 'click',
						hideOn: 'click',
						content: c,
						hideFn: hideFn,
						showFn: showFn
					});
					// ul.dispose();

				}
			}
		}.bind(this));

		// watch the top/master chxbox
		var chxall = this.list.form.getElement('input[name=checkAll]');
		var c = function (el) {
			return el.getParent('.fabrik___heading').getElement('ul.fabrik_action');
		};

		var hideFn = function (e, elem) {
			if (!e.target.checked) {
				this.hide(e, elem);
			}
		};

		var showFn = function (e) {
			if (e.target.checked) {
				this.show(e.target);
			}
		};

		var tip = new FloatingTips(chxall, {
			position: this.options.floatPos,
			html: true,
			showOn: 'click',
			hideOn: 'click',
			content: c,
			hideFn: hideFn,
			showFn: showFn
		});

		// hide markup that contained the actions
		if (this.list.list.getElements('.fabrik_actions')) {
			this.list.list.getElements('.fabrik_actions').hide();
		}
		if (this.list.list.getElements('.fabrik_calculation')) {
			var calc = this.list.list.getElements('.fabrik_calculation').getLast();
			if (typeOf(calc) !== 'null') {
				calc.hide();
			}
		}
	}
});