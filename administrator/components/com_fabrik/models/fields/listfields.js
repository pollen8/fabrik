/**
 * Admin Listfields Dropdown Editor
 *
 * @copyright: Copyright (C) 2005-2013, fabrikar.com - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

var ListFieldsElement = new Class({

	Implements: [Options, Events],

	options: {
		conn: null,
		highlightpk: false,
		showAll: 1,
		mode: 'dropdown'
	},

	initialize: function (el, options) {
		this.strEl = el;
		this.el = el;
		this.setOptions(options);
		if (typeOf(document.id(this.options.conn)) === 'null') {
			this.cnnperiodical = this.getCnn.periodical(500, this);
		} else {
			this.setUp();
		}
	},

	/**
	 * Triggered when a fieldset is repeated (e.g. in googlemap viz where you can
	 * select more than one data set)
	 */
	cloned: function (newid, counter)
	{
		this.strEl = newid;
		this.el = document.id(newid);
		this._cloneProp('conn', counter);
		this._cloneProp('table', counter);
		this.setUp();
	},

	/**
	 * Helper method to update option HTML id's on clone()
	 */
	_cloneProp: function (prop, counter) {
		var bits = this.options[prop].split('-');
		bits = bits.splice(0, bits.length - 1);
		bits.push(counter);
		this.options[prop] = bits.join('-');
	},

	getCnn: function () {
		if (typeOf(document.id(this.options.conn)) === 'null') {
			return;
		}
		this.setUp();
		clearInterval(this.cnnperiodical);
	},

	setUp: function () {
		this.el = document.id(this.el);
		if (this.options.mode === 'gui') {
			this.select = this.el.getParent().getElement('select.elements');
		}
		document.id(this.options.conn).addEvent('change', function () {
			this.updateMe();
		}.bind(this));
		document.id(this.options.table).addEvent('change', function () {
			this.updateMe();
		}.bind(this));

		// See if there is a connection selected
		var v = document.id(this.options.conn).get('value');
		if (v !== '' && v !== -1) {
			this.periodical = this.updateMe.periodical(500, this);
		}
		var add = this.el.getParent().getElement('button');
		if (typeOf(add) !== 'null') {
			add.addEvent('mousedown', function (e) {
				e.stop();
				this.addPlaceHolder();
			}.bind(this));
			add.addEvent('click', function (e) {
				e.stop();
			});
		}
	},

	updateMe: function (e) {
		if (typeOf(e) === 'event') {
			e.stop();
		}
		if (document.id(this.el.id + '_loader')) {
			document.id(this.el.id + '_loader').show();
		}
		var cid = document.id(this.options.conn).get('value');
		var tid = document.id(this.options.table).get('value');
		if (!tid) {
			return;
		}
		clearInterval(this.periodical);
		var url = 'index.php?option=com_fabrik&format=raw&task=plugin.pluginAjax&g=element&plugin=field&method=ajax_fields&showall=' + this.options.showAll + '&cid=' + cid + '&t=' + tid;
		var myAjax = new Request({
			url: url,
			method: 'get',
			data: {
				'highlightpk': this.options.highlightpk,
				'k': 2
			},
			onComplete: function (r) {
				var els;
				
				// Googlemap inside repeat group & modal repeat
				if (typeOf(document.id(this.strEl)) !== null) {
					this.el = document.id(this.strEl);
				}
				if (this.options.mode === 'gui') {
					els = [this.select];
				} else {
					els = document.getElementsByName(this.el.name);
					this.el.empty();
					document.id(this.strEl).empty();
				}
				var opts = eval(r);
				
				Array.each(els, function (el) {
					document.id(el).empty();
				});
				
				opts.each(function (opt) {
					var o = {'value': opt.value};
					if (opt.value === this.options.value) {
						o.selected = 'selected';
					}
					Array.each(els, function (el) {
						new Element('option', o).set('text', opt.label).inject(el);
					});
				}.bind(this));
				if (document.id(this.el.id + '_loader')) {
					document.id(this.el.id + '_loader').hide();
				}
			}.bind(this)
		});
		Fabrik.requestQueue.add(myAjax);
	},
	
	/**
	 * If rendering with mode=gui then add button should insert selected element placeholder into 
	 * text area
	 */
	addPlaceHolder: function () {
		var list = this.el.getParent().getElement('select');
		this.insertTextAtCaret(this.el, list.get('value'));
	},
	
	/**
	 * Start of text insertion code - taken from 
	 * http://stackoverflow.com/questions/3510351/how-do-i-add-text-to-a-textarea-at-the-cursor-location-using-javascript
	 */
	getSelectionBoundary: function (el, start) {
		var property = start ? "selectionStart" : "selectionEnd";
		var originalValue, textInputRange, precedingRange, pos, bookmark;

		if (typeof el[property] === "number") {
			return el[property];
		} else if (document.selection && document.selection.createRange) {
			el.focus();

			var range = document.selection.createRange();
			if (range) {
				// Collapse the selected range if the selection is not a caret
				if (document.selection.type === "Text") {
					range.collapse(!!start);
				}

				originalValue = el.value;
				textInputRange = el.createTextRange();
				precedingRange = el.createTextRange();
				pos = 0;

				bookmark = range.getBookmark();
				textInputRange.moveToBookmark(bookmark);

				if (originalValue.indexOf("\r\n") > -1) {
					// Trickier case where input value contains line breaks

					// Insert a character in the text input range and use that
					//as a marker
					textInputRange.text = " ";
					precedingRange.setEndPoint("EndToStart", textInputRange);
					pos = precedingRange.text.length - 1;

					// Executing an undo command deletes the character inserted
					// and prevents adding to the undo stack.
					document.execCommand("undo");
				} else {
					// Easier case where input value contains no line breaks
					precedingRange.setEndPoint("EndToStart", textInputRange);
					pos = precedingRange.text.length;
				}
				return pos;
			}
		}
		return 0;
	},

	offsetToRangeCharacterMove: function (el, offset) {
		return offset - (el.value.slice(0, offset).split("\r\n").length - 1);
	},

	setSelection: function (el, startOffset, endOffset) {
		var range = el.createTextRange();
		var startCharMove = this.offsetToRangeCharacterMove(el, startOffset);
		range.collapse(true);
		if (startOffset === endOffset) {
			range.move("character", startCharMove);
		} else {
			range.moveEnd("character", this.offsetToRangeCharacterMove(el, endOffset));
			range.moveStart("character", startCharMove);
		}
		range.select();
	},

	insertTextAtCaret: function (el, text) {
		var pos = this.getSelectionBoundary(el, false);
		var newPos = pos + text.length;
		var val = el.value;
		el.value = val.slice(0, pos) + text + val.slice(pos);
		this.setSelection(el, newPos, newPos);
	}
});