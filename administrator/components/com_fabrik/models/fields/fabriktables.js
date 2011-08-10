var fabriktablesElement = new Class({

	Implements : [ Options, Events ],

	options : {
		conn : null,
		connInRepeat : true,
		container : ''
	},

	initialize : function(el, options) {
		this.el = el;
		this.setOptions(options);
		this.elements = [];
		this.elementLists = $H({}); // keyed on specific element options
		this.waitingElements = $H({}); // keyed on specific element options
		// if loading in a form plugin then the connect is not yet avaiable in the
		// dom
		if (typeOf($(this.options.conn)) === false) {
			this.periodical = this.getCnn.periodical(500, this);
		} else {
			this.setUp();
		}
	},

	getCnn : function() {
		if (typeOf($(this.options.conn)) === false) {
			return;
		}
		this.setUp();
		clearInterval(this.periodical);
	},

	registerElement : function(el) {
		this.elements.push(el);
		this.updateElements();
	},

	setUp : function() {
		this.el = $(this.el);
		if (typeOf($(this.options.conn)) == 'null') {
			return;
		}
		$(this.options.conn).addEvent('change', this.updateMe.bindWithEvent(this));
		this.el.addEvent('change', this.updateElements.bindWithEvent(this));
		// see if there is a connection selected
		var v = $(this.options.conn).get('value');
		if (v != '' && v != -1) {
			this.updateMe();
		}
	},

	updateMe : function(e) {
		if (e) {
			new Event(e).stop();
		}
		var cid = $(this.options.conn).get('value');
		// keep repeating the perioical untill the cnn drop down is completed
		if (!cid) {
			return;
		}
		if ($(this.el.id + '_loader')) {
			$(this.el.id + '_loader').setStyle('display', 'inline');
		}
		var url = this.options.livesite
				+ 'index.php?option=com_fabrik&format=raw&view=plugin&&task=pluginAjax&g=element&plugin=field&method=ajax_tables&showf=1&cid=' + cid;
		var myAjax = new Request({
			url : url,
			method : 'get',
			onComplete : function(r) {
				var opts = JSON.decode(r);
				if (typeOf(opts) !== 'null') {
					if(opts.err){
						alert(opts.err);
					}else{
						this.el.empty();
						opts.each(function(opt) {
							// var o = {'value':opt.value};//wrong for calendar
							var o = {
								'value' : opt.id
							};
							if (opt.id == this.options.value) {
								o.selected = 'selected';
							}
							new Element('option', o).appendText(opt.label).inject(this.el);
						}.bind(this));
						if ($(this.el.id + '_loader')) {
							$(this.el.id + '_loader').setStyle('display', 'none');
						}
						this.updateElements();
					}
				}
			}.bind(this)
		}).send();
	},

	updateElements : function() {
		this.elements.each(function(element) {
			var opts = element.getOpts();
			var table = this.el.get('value');
			if (table === '') {
				// $$$ rob dont empty as this messes up parameter saving in paypal
				// plugin
				// element.el.empty();
				return;
			}
			if ($(element.el.id + '_loader')) {
				$(element.el.id + '_loader').setStyle('display', '');
			}
			var key = opts.getValues().toString() + ',' + table;
			if(!this.waitingElements.has(key)){
				this.waitingElements[key] = $H({});
			}
			if (this.elementLists[key] != undefined) {
				if (this.elementLists[key] == '') {
					// delay update
					this.waitingElements[key][element.el.id] = element;
				} else {
					// keyed on specific element options
					this.updateElementOptions(this.elementLists[key], element);
				}
			} else {

				var cid = $(this.options.conn).get('value');
				this.elementLists.set(key, '');
				var url = this.options.livesite
						+ 'index.php?option=com_fabrik&format=raw&view=plugin&task=pluginAjax&g=visualization&plugin=chart&method=ajax_fields&k=2&t=' + table + '&cid='
						+ cid;

				var ajaxopts = {};
				opts.each(function(v, k) {
					ajaxopts[k] = v;
				});
				var myAjax = new Request({
					url : url,
					method : 'get',
					'data' : ajaxopts,
					onComplete : function(r) {
						this.elementLists.set(key, r);
						this.updateElementOptions(r, element);
						this.waitingElements.get(key).each(function(el, i) {
							this.updateElementOptions(r, el);
							this.waitingElements[key].erase(i);
						}.bind(this));
					}.bind(this)
				}).send();
			}
		}.bind(this));
	},

	updateElementOptions : function(r, element) {
		var table = $(this.el).get('value');
		var key = element.getOpts().getValues().toString() + ',' + table;
		var opts = eval(r);
		element.el.empty();
		var o = {
			'value' : ''
		};
		if (element.options.value == '') {
			o.selected = 'selected';
		}
		new Element('option', o).appendText('-').inject(element.el);
		opts.each(function(opt) {
			opt.value = opt.value.replace('[]', '');
			var o = {
				'value' : opt.value
			};
			if (opt.value == element.options.value) {
				o.selected = 'selected';
			}
			new Element('option', o).appendText(opt.label).inject(element.el);
		}.bind(this));
		if ($(element.el.id + '_loader')) {
			$(element.el.id + '_loader').hide();
		}
	},
	// only called from repeat viz admin interface i think
	cloned : function(newid, counter) {
		if (this.options.connInRepeat == true) {
			// table needs to update watch connection id
			var cid = this.options.conn.split('-');
			cid.pop();
			this.options.conn = cid.join('-') + '-' + counter;
		}
		this.el = newid;
		this.elements = [];
		this.elementLists = $H({});
		this.waitingElements = $H({});
		this.setUp();
		Fabrik.model.fields.fabriktable[this.el.id] = this;
	}

});