var PluginManager = new Class({
	
	initialize: function (plugins) {
		this.plugins = plugins;
		this.counter = 0;
		this.opts = this.opts || {};
		this.deletePluginClick = this.deletePlugin.bindWithEvent(this);
		this.watchAdd();
	},

	_makeSel: function (c, name, pairs, sel) {
		var v, l;
		var opts = [];
		this.sel = sel;
		opts.push(new Element('option', {'value': ''}).appendText(Joomla.JText._('COM_FABRIK_PLEASE_SELECT')));
		if (typeOf(pairs) === 'object') {
			$H(pairs).each(function (group, key) {
				opts.push(new Element('optgroup', {'label': key}));
				group.each(function (pair) {
					opts = this._addSelOpt(opts, pair);
				}.bind(this));
			}.bind(this));
		} else {
			pairs.each(function (pair) {
				opts = this._addSelOpt(opts, pair);
			}.bind(this));
		}
		return new Element('select', {'class': c, 'name': name}).adopt(opts);
	},
	
	_addSelOpt: function (opts, pair) {
		if (typeOf(pair) === 'object') {
			v = pair.value ? pair.value : pair.name; //plugin list should be keyed on plugin name
			l = pair.label ? pair.label : v;
		} else {
			v = l = pair;
		}
		if (v === this.sel) {
			opts.push(new Element('option', {'value': v, 'selected': 'selected'}).set('text', l));
		} else {
			opts.push(new Element('option', {'value': v}).set('text', l));
		}
		return opts;
	},
	
	addPlugin: function (o) {
		this.plugins.push(o);
	},
	
	deletePlugin: function (e) {
		// decrease the element name counter. 
		// Otherwise you can loose data on saving (2 validations, delete first - 2nd lost values)
		$('plugins').getElements('input, select, textarea').each(function (i) {
			var s = i.name.match(/\[[0-9]\]/);
			if (s) {
				var c = s[0].replace('[', '').replace(']', '').toInt();
				if (c > 0) {
					c = c - 1;
				}
				i.name = i.name.replace(/\[[0-9]\]/, '[' + c + ']');
			}
		});
		e.stop();
		$(e.target).up(3).dispose();
		this.counter --;
	},
	
	watchAdd: function () {
		$('addPlugin').addEvent('click', function (e) {
			e.stop();
			this.addAction('', '', {});
		}.bind(this));
	},
	
	watchDelete: function () {
		$('plugins').getElements('.delete').each(function (c) {
			c.removeEvents('click');
			c.addEvent('click', this.deletePluginClick);
		}.bind(this));
	},
	
	getPluginTop: function () {
		return '';
	},
	
	addAction: function (pluginHTML, plugin, opts, cloneJs) {
		cloneJs = cloneJs === false ? false : true;
		var td = new Element('td');
		var str  = '';
		this.plugins.each(function (aPlugin) {
			if (aPlugin.name === plugin) {
				str += pluginHTML;
			} else {
				str += aPlugin.options.html;
			}
			
		}.bind(this));
		//test for settting radio buttons ids - seems to work
		str = str.replace(/\[0\]/gi, '[' + this.counter + ']');
		//end test
		td.innerHTML = str;
		var display = 'block';
		opts.counter = this.counter;
		var c = new Element('div', {'class': 'actionContainer'}).adopt(
		new Element('table', {'class': 'adminform', 'id': 'formAction_' + this.counter, 'styles': {'display': display}}).adopt(
			new Element('tbody', {'styles': {'width': '100%'}}).adopt([
				this.getPluginTop(plugin, opts),
				new Element('tr').adopt(td),
				new Element('tr').adopt(
					new Element('td', {}).adopt(
						new Element('a', {'href': '#', 'class': 'delete removeButton'}).appendText(Joomla.JText._('COM_FABRIK_DELETE'))
					)
				)
			])
		)
	);
		
		c.inject($('plugins'));
		//update params ids
		if (this.counter !== 0) {
			c.getElements('input[name^=params]', 'select[name^=params]').each(function (i) {
				if (i.id !== '') {
					var a = i.id.split('-');
					a.pop();
					i.id = a.join('-') + '-' + this.counter;
				}
			}.bind(this));
			
			c.getElements('img[src=components/com_fabrik/images/ajax-loader.gif]').each(function (i) {
				i.id = i.id.replace('-0_loader', '-' + this.counter + '_loader');
			}.bind(this));
			if (cloneJs === true) {
				this.plugins.each(function (plugin) {
					// clone js controller
					var newPlugin = new CloneObject(plugin, true, []);
					newPlugin.cloned(this.counter);
				}.bind(this));
			}
		}

		// show the active plugin 
		var formaction = $('formAction_' + this.counter);
		formaction.getElements('.' + this.opts.type + 'Settings').hide();
		var activePlugin = formaction.getElement(' .page-' + plugin);
		if (activePlugin) {
			activePlugin.show();
		}
		
		//watch the drop down
		formaction.getElement('.elementtype').addEvent('change', function (e) {
			e.stop();
			var id = e.target.getParent('.adminform').id.replace('formAction_', '');
			$('formAction_' + id).getElements('.' + this.opts.type + 'Settings').hide();
			var s = e.target.get('value');
			if (s !== Joomla.JText._('COM_FABRIK_PLEASE_SELECT') && s !== '') {
				$('formAction_' + id).getElement('.page-' + s).show();
			}
		}.bind(this));
		this.watchDelete();
		
		//show any tips (only running code over newly added html)
		var myTips = new Tips($$('#formAction_' + this.counter + ' .hasTip'), {});
		this.counter ++;
	},
	
	getPublishedYesNo: function (opts) {
		var yesno = '<label>' + Joomla.JText._('COM_FABRIK_PUBLISHED') + '</label>';
		var yeschecked = opts.state !== false ? 'checked="checked"' : '';
		var nochecked = opts.state === false ? 'checked="checked"' : '';
		yesno += '<fieldset class="radio"><label>' + Joomla.JText._('JYES') + '<input type="radio" name="jform[params][plugin_state][' + opts.counter + ']" ' + yeschecked + ' value="1"></label>';
		yesno += '<label>' + Joomla.JText._('JNO') + '<input type="radio" name="jform[params][plugin_state][' + opts.counter + ']"' + nochecked + ' value="0"></label></fieldset>';
		return yesno;
	}
	
});

fabrikAdminPlugin = new Class({
	
	Implements: [Options],
	options: {},
	initialize: function (name, label, options)
	{
		this.name = name;
		this.label = label;
		this.setOptions(options);
	},
	
	cloned: function () {
		
	}
		
});