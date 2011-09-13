var Suboptions = new Class({
	
	Implements: [Options],
	
	options: {
		sub_initial_selection: []
	},
	
	initialize: function (name, options) {
		this.setOptions(options);
		this.counter = 0;
		this.name = name;
		this.clickRemoveSubElement = this.removeSubElement.bindWithEvent(this);
		$('addSuboption').addEvent('click', this.addOption.bindWithEvent(this));
		this.options.sub_values.each(function (v, x) {
			var chx = this.options.sub_initial_selection.indexOf(v) === -1 ? '' : "checked='checked'";
			this.addSubElement(v, this.options.sub_labels[x], chx);
		}.bind(this));
		$('adminForm').addEvent('submit', function (e) {
			if (!this.onSave()) {
				e.stop();
			}
		}.bind(this));
	},
	
	addOption: function (e) {
		this.addSubElement();
		var event = new Event(e);
		event.stop();
	},
	
	removeSubElement: function (e) {
		var event = new Event(e);
		var id = event.target.id.replace('sub_delete_', '');
		if ($('sub_subElementBody').getElements('li').length > 1) {
			$('sub_content_' + id).dispose();
		}
		event.stop();
	},
	
	addSubElement: function (sValue, sText, sCurChecked) {
		sValue = sValue ? sValue : '';
		sText = sText ? sText : '';
		var chx = "<input class=\"inputbox sub_initial_selection\" type=\"checkbox\" value=\"" + sValue + "\" name='" + this.name + "[sub_initial_selection][]' id=\"sub_checked_" + this.counter + "\" " + sCurChecked + " />";
		var li = new Element('li', {id: 'sub_content_' + this.counter}).adopt([
			new Element('table',  {width: '100%'}).adopt([
				new Element('tbody').adopt([
					new Element('tr').adopt([
						new Element('td', {'rowspan': 2, 'class': 'handle subhandle'}),
						new Element('td', {width: '30%'}).adopt(
							new Element('input', {
								'class': 'inputbox sub_values', 
								type: 'text',
								name: this.name + '[sub_values][]',
								id: 'sub_value_' + this.counter, 
								size: 20,
								value: sValue,
								events: {
									'change': function (e) {
											fconsole('need to set this chb boxes value to the value field if selected, or set to blank');
										}
								}
							})),

							new Element('td', {width: '30%'}).adopt(
								new Element('input', {
									'class': 'inputbox sub_labels',
									type: 'text',
									name: this.name + '[sub_labels][]',
									id: 'sub_text_' + this.counter,
									size : 20,
									value : sText
								})),
								new Element('td', {width: '10%'}).set('html',
									chx
								),
								new Element('td', {width: '20%'}).adopt(
									new Element('a', {
										'class': 'removeButton',
										href: '#',
										id: 'sub_delete_' + this.counter
									}).set('text', 'Delete')
								)
								])
							])
						])
					]);
		if ($('sub_subElementBody').getElement('li').innerHTML === '') {
			li.replaces($('sub_subElementBody').getElement('li'));
		} else {
			li.inject($('sub_subElementBody'));
		}
		$('sub_delete_' + this.counter).addEvent('click', this.clickRemoveSubElement);
		
		if (!this.sortable) {
			this.sortable = new Sortables('sub_subElementBody', {'handle': '.subhandle'});
		} else {
			this.sortable.addItems(li);
		}
		this.counter++;
	},
	
	onSave: function () {
		var values = []; 
		var ret = true;
		var intial_selection = [];
		$$('.sub_values').each(function (dd) {
			if (dd.value === '') {
				alert(Joomla.JText._('COM_FABRIK_SUBOPTS_VALUES_ERROR'));
				ret = false;
			}
			values.push(dd.value);
		});
		$$('.sub_initial_selection').each(function (dd, c) {
			dd.value = values[c];
		});
		return ret;
	}
});