/*jshint mootools: true */
/*global Fabrik:true, fconsole:true, Joomla:true, CloneObject:true, $A:true, $H:true,unescape:true */

AdvancedSearch = new Class({
	
	Implements: [Options, Events],
	
	options: {},
			
	initialize: function (options) {
		this.setOptions(options);
		this.trs = $A([]);
		if (document.id('advanced-search-add')) {
			document.id('advanced-search-add').addEvent("click", this.addRow.bindWithEvent(this));
			document.id('advancedFilterTable-clearall').addEvent("click", this.resetForm.bindWithEvent(this));
			this.trs.each(function (tr) {
				tr.inject(document.id('advanced-search-table').getElements('tr').getLast(), 'after');
			}.bind(this));
		}
		this.watchDelete();
		this.watchElementList();
	},
  
	watchDelete: function () {
		//should really just delegate these events from the adv search table
		$$('.advanced-search-remove-row').removeEvents();
		$$('.advanced-search-remove-row').addEvent('click', this.removeRow.bindWithEvent(this));
	},
	
	watchElementList: function () {
		$$('select.key').removeEvents();
		$$('select.key').addEvent('change', this.updateValueInput.bindWithEvent(this));
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
			'data': {'element': v, 'id': this.options.listid, 'elid': eldata.id, 'plugin': eldata.plugin, 'counter': this.options.counter},
			'onComplete': function () {
				Fabrik.loader.stop(row);
			}}).send();
	},
  
	addRow: function (e) {
		this.options.counter ++;
		e.stop();
		var tr = document.id('advanced-search-table').getElement('tbody').getElements('tr').getLast();
		var clone = tr.clone();
		clone.inject(tr, 'after');
		clone.getElement('td').empty().set('html', this.options.conditionList);
		var tds = clone.getElements('td');
		tds[1].empty().set('html', this.options.elementList);
		tds[1].adopt([
			new Element('input', {'type': 'hidden', 'name': 'fabrik___filter[list_' + this.options.listid + '][search_type][]', 'value': 'advanced'}),
			new Element('input', {'type': 'hidden', 'name': 'fabrik___filter[list_' + this.options.listid + '][grouped_to_previous][]', 'value': '0'})
		]);
		tds[2].empty().set('html', this.options.statementList);
		tds[3].empty();
		this.watchDelete();
		this.watchElementList();
	},
  
	removeRow: function (e) {
		e.stop();
		if ($$('.advanced-search-remove-row').length > 1) {
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
  
	resetForm: function () {
		var table = document.id('advanced-search-table');
		if (!table) {
			return;
		}
		table.getElements('tbody tr').each(function (tr, i) {
			if (i > 1) {
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
		event.target.removeEvent("click", this.deleteFilterOption.bindWithEvent(this));
		var tr = event.target.parentNode.parentNode;
		var table = tr.parentNode;
		table.removeChild(tr);
		e.stop();
	}
 
});