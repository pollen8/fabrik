/**
 * @author Robert
 */

/*jshint mootools: true */
/*global Fabrik:true, fconsole:true, Joomla:true, CloneObject:true, $A:true, $H:true,unescape:true */

var FbAutocomplete = new Class({
	
	Implements: [Options, Events],
	
	options: {
		menuclass: 'auto-complete-container',
		url: 'index.php',
		max: 10,
		onSelection: Class.empty
	},

	initialize: function (element, options) {
		this.setOptions(options);
		this.options.labelelement = $(element + '-auto-complete');
		this.cache = {};
		this.selected = -1;
		this.mouseinsde = false;
		this.watchKeys = this.doWatchKeys.bindWithEvent(this);
		this.testMenuClose = this.doTestMenuClose.bindWithEvent(this);
		this.element = typeOf($(element)) === "null" ? document.getElement(element) : $(element);
		
		this.buildMenu();
		if (!this.getInputElement()) {
			fconsole('autocomplete didnt find input element');
			return;
		}
		this.getInputElement().setProperty('autocomplete', 'off');
		this.doSearch = this.search.bindWithEvent(this);
		this.getInputElement().addEvent('keyup', this.doSearch);
	},
	
	search: function (e) {
		if (e.key === 'tab') {
			this.closeMenu();
			return;
		}
		var v = this.getInputElement().get('value');
		if (v === '') {
			this.element.value = '';
		}
		if (v !== this.searchText && v !== '') {
			this.element.value = v;
			this.positionMenu();
			if (this.cache[v]) {
				this.populateMenu(this.cache[v]);
				this.openMenu();
			} else {
				Fabrik.loader.start(this.getInputElement());
				if (this.ajax) {
					this.closeMenu();
					this.ajax.cancel();
				}
				this.ajax = new Request({url: this.options.url,
					data: {
						value: v
					},
					onComplete: function (e) {
						Fabrik.loader.stop(this.getInputElement());
						this.completeAjax(e, v);
					}.bind(this)
				}).send();
			}
		}
		this.searchText = v;
	},
	
	completeAjax: function (r, v) {
		r = JSON.decode(r);
		this.cache[v] = r;
		Fabrik.loader.stop(this.getInputElement());
		this.populateMenu(r);
		this.openMenu();
	},
	
	buildMenu: function ()
	{
		this.menu = new Element('div', {'class': this.options.menuclass, 'styles': {'position': 'absolute'}}).adopt(new Element('ul'));
		this.menu.inject(document.body);
		this.menu.addEvent('mouseenter', function () {
			this.mouseinsde = true;
		}.bind(this));
		this.menu.addEvent('mouseleave', function () {
			this.mouseinsde = false;
		}.bind(this));
	},
	
	getInputElement: function () {
		return this.options.labelelement ? this.options.labelelement : this.element;
	},
	
	positionMenu: function () {
		var coords = this.getInputElement().getCoordinates();
		var pos = this.getInputElement().getPosition();
		this.menu.setStyles({ 'left': coords.left, 'top': (coords.top + coords.height) - 1, 'width': coords.width});
	},
	
	populateMenu: function (data) {
		this.data = data;
		var max = this.getListMax();
		var ul = this.menu.getElement('ul');
		ul.empty();
		if (data.length === 1) {
			this.element.value = data[0].value;
			this.fireEvent('selection', [this, this.element.value]);
		}
		for (var i = 0; i < max; i ++) {
			var pair = data[i];
			var li = new Element('li', {'data-value': pair.value, 'class': 'unselected'}).set('text', pair.text);
			li.inject(ul);
			li.addEvent('click', this.makeSelection.bindWithEvent(this, [li]));
		}
		if (data.length > this.options.max) {
			new Element('li').set('text', '....').inject(ul);
		}
	},
	
	makeSelection: function (e, li) {
		this.getInputElement().value = li.get('text');
		this.element.value = li.getProperty('data-value');
		this.closeMenu();
		this.fireEvent('selection', [this, this.element.value]);
	},
	
	closeMenu: function () {
		if (this.shown) {
			this.shown = false;
			this.menu.fade('out');
			this.selected = -1;
			document.removeEvent('keydown', this.watchKeys);
			document.removeEvent('click', this.testMenuClose);
		}
	},
	
	openMenu: function () {
		if (!this.shown) {
			this.shown = true;
			this.menu.setStyle('visibility', 'visible').fade('in');
			document.addEvent('keydown', this.watchKeys);
			document.addEvent('click', this.testMenuClose);
			this.selected = 0;
			this.highlight();
		}
	},
	
	doTestMenuClose: function () {
		if (!this.mouseinsde) {
			this.closeMenu();
		}
	},
	
	getListMax: function () {
		return this.data.length > this.options.max ? this.options.max : this.data.length;
	},
	
	doWatchKeys: function (e) {
		var max = this.getListMax();
		e = new Event(e);
		if (e.key === 'enter') {
			window.fireEvent('blur');
		}
		switch (e.code) {
		case 40://down
			if (this.selected + 1 < max) {
				this.selected ++;
				this.highlight();
			}
			e.stop();
			break;
		case 38: //up
			if (this.selected - 1 >= -1) {
				this.selected --;
				this.highlight();
			}
			e.stop();
			break;
		case 13://enter
		case 9://tab
			e.stop();
			this.makeSelection({}, this.getSelected());
			this.closeMenu();
			break;
		case 27://escape
			e.stop();
			this.closeMenu();
			break;
		}
	},
	
	getSelected: function () {
		var a = this.menu.getElements('li').filter(function (li, i) {
			return i === this.selected;
		}.bind(this));
		return a[0];
	},
	
	highlight: function () {
		this.menu.getElements('li').each(function (li, i) {
			if (i === this.selected) {
				li.addClass('selected');
			} else {
				li.removeClass('selected');
			}
		}.bind(this));
	}
	
});

var FbCddAutocomplete = new Class({
	
	Extends: FbAutocomplete,
	
	search: function () {
		var v = this.getInputElement().get('value');
		if (v === '') {
			this.element.value = '';
		}
		if (v !== this.searchText && v !== '') {
			var key = $(this.options.observerid).get('value') + '.' + v;
			this.positionMenu();
			if (this.cache[key]) {
				this.populateMenu(this.cache[key]);
				this.openMenu();
			} else {
				Fabrik.loader.start(this.getInputElement());
				//this.spinner.fade('in'); //f3 fx now used
				if (this.ajax) {
					this.closeMenu();
					this.ajax.cancel();
				}
				this.ajax = new Request({
					url : this.options.url,
					data: {
						value: v,
						fabrik_cascade_ajax_update: 1,
						v: $(this.options.observerid).get('value')
					},
					onComplete: this.completeAjax.bindWithEvent(this, [key])
				}).send();
			}
		}
		this.searchText = v;
	}
});