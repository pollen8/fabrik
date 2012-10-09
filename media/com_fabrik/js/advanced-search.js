/*jshint mootools: true */
/*global Fabrik:true, fconsole:true, Joomla:true, CloneObject:true, $A:true, $H:true,unescape:true */

AdvancedSearch = new Class({
	
	Implements: [Options, Events],
	
	options: {
		'ajax': false,
		'controller': 'list',
		'parentView': ''
	},
			
	initialize: function (options) {
		this.setOptions(options);
		this.form = document.id('advanced-search-win' + this.options.listref).getElement('form');
		this.trs = $A([]);
		if (this.form.getElement('.advanced-search-add')) {
			this.form.getElement('.advanced-search-add').removeEvents('click');
			this.form.getElement('.advanced-search-add').addEvent('click', this.addRow.bindWithEvent(this));
			this.form.getElement('.advanced-search-clearall').removeEvents('click');
			this.form.getElement('.advanced-search-clearall').addEvent('click', this.resetForm.bindWithEvent(this));
			this.trs.each(function (tr) {
				tr.inject(this.form.getElement('.advanced-search-list').getElements('tr').getLast(), 'after');
			}.bind(this));
		}
		this.watchDelete();
		this.watchApply();
		this.watchElementList();
	},
	
	watchApply: function () {
		if (!this.options.ajax) {
			return;
		}
		this.form.getElement('.advanced-search-apply').addEvent('click', function (e) {
			e.stop();
			var list = Fabrik.blocks['list_' + this.options.listref];
			list.submit(this.options.controller + '.filter');
		}.bind(this));
	},
  
	watchDelete: function () {
		//should really just delegate these events from the adv search table
		this.form.getElements('.advanced-search-remove-row').removeEvents();
		this.form.getElements('.advanced-search-remove-row').addEvent('click', this.removeRow.bindWithEvent(this));
	},
	
	watchElementList: function () {
		this.form.getElements('select.key').removeEvents();
		this.form.getElements('select.key').addEvent('change', this.updateValueInput.bindWithEvent(this));
	},
	
	/**
	 * called when you choose an element from the filter dropdown list
	 * should run ajax query that updates value field to correspond with selected
	 * element
	 * @param {Object} e event
	 */
	
	updateValueInput: function (e) {
		var row = e.target.getParent('tr');
		Fabrik.loader.start(row);
		var v = e.target.get('value');
		var update = e.target.getParent().getParent().getElements('td')[3];
		if (v === '') {
			update.set('html', '');
			return;
		}
		var url = Fabrik.liveSite + "index.php?option=com_fabrik&task=list.elementFilter&format=raw";
		var eldata = this.options.elementMap[v];
		new Request.HTML({'url': url, 
			'update': update, 
			'data': {'element': v, 'id': this.options.listid, 'elid': eldata.id, 'plugin': eldata.plugin, 'counter': this.options.counter,
				'listref':  this.options.listref, 'context': this.options.controller, 
				'parentView': this.options.parentView},
			'onComplete': function () {
				Fabrik.loader.stop(row);
			}}).send();
	},
  
	addRow: function (e) {
		this.options.counter ++;
		e.stop();
		var tr = this.form.getElement('.advanced-search-list').getElement('tbody').getElements('tr').getLast();
		var clone = tr.clone();
		clone.inject(tr, 'after');
		clone.getElement('td').empty().set('html', this.options.conditionList);
		var tds = clone.getElements('td');
		tds[1].empty().set('html', this.options.elementList);
		tds[1].adopt([
			new Element('input', {'type': 'hidden', 'name': 'fabrik___filter[list_' + this.options.listref + '][search_type][]', 'value': 'advanced'}),
			new Element('input', {'type': 'hidden', 'name': 'fabrik___filter[list_' + this.options.listref + '][grouped_to_previous][]', 'value': '0'})
		]);
		tds[2].empty().set('html', this.options.statementList);
		tds[3].empty();
		this.watchDelete();
		this.watchElementList();
	},
  
	removeRow: function (e) {
		e.stop();
		if (this.form.getElements('.advanced-search-remove-row').length > 1) {
			this.options.counter --;
			var tr = e.target.findUp('tr');
			var fx = new Fx.Morph(tr, {
				duration: 800,
				transition: Fx.Transitions.Quart.easeOut,
				onComplete: function () {
					tr.dispose();
				}
			});
			fx.start({
				'height': 0,
				'opacity': 0
			});
		}
	},
  
	/**
	 * removes all rows except for the first one, whose values are reset to empty
	 */
	resetForm: function () {
		var table = this.form.getElement('.advanced-search-list');
		if (!table) {
			return;
		}
		table.getElements('tbody tr').each(function (tr, i) {
			if (i >= 1) {
				tr.dispose();
			}
			if (i === 0) {
				tr.getElements('.inputbox').each(function (dd) {
					dd.selectedIndex = 0;
				});
				tr.getElements('input').each(function (i) {
					i.value = '';
				});
			}
		});
		this.watchDelete();
		this.watchElementList();
	},

	deleteFilterOption: function (e) {
		event.target.removeEvent('click', this.deleteFilterOption.bindWithEvent(this));
		var tr = event.target.parentNode.parentNode;
		var table = tr.parentNode;
		table.removeChild(tr);
		e.stop();
	}
 
});