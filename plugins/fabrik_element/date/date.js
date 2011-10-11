var FbDateTime = new Class({
	Extends: FbElement,
	initialize: function (element, options) {
		this.parent(element, options);
		this.setOptions(element, options);
		this.hour = '0';
		this.plugin = 'fabrikdate';
		this.minute = '00';
		this.buttonBg = '#ffffff';
		this.buttonBgSelected = '#88dd33';
		this.startElement = element;
		this.setUp = false;
		this.watchButtons();
		if (this.options.typing === false) {
			this.disableTyping();
		}
	},

	disableTyping : function () {
		if (typeOf(this.element) === 'null') {
			fconsole(element + ': not date element container - is this a custom template with a missing $element->containerClass div/li surrounding the element?');
			return;
		}
		// yes we really can set the none existant 'readonly' property of the
		// subelement container
		// and get it when checking the validations - cool or what?
		this.element.setProperty('readonly', 'readonly');
		this.element.getElements('.fabrikinput').each(function (f) {
			f.addEvent('focus', function (e) {
				if (typeOf(e) === 'null') {
					return;
				}
				if (e.target.hasClass('timeField')) {
					this.element.getParent('.fabrikElementContainer').getElement('.timeButton').fireEvent('click');
				} else {
					this.options.calendarSetup.inputField = e.target.id;
					this.options.calendarSetup.button = this.element.id + "_img";
					this.addEventToCalOpts();
					Calendar.setup(this.options.calendarSetup);
				}
			}.bind(this));
		}.bind(this));
	},

	getValue: function () {
		if (!this.options.editable) {
			return this.options.value;
		}
		this.getElement();
		var v = this.element.getElement('.fabrikinput').get('value');
		// @TODO use relative class name to get time value
		if (this.options.showtime === true && this.timeElement) {
			v += ' ' + this.timeElement.get('value');
		}
		return v;
	},

	watchButtons : function () {
		var b = document.id(this.options.element + '_cal_img'); 
		if (typeOf(b) !== 'null') {
			b.addEvent('click', function (e) {
				this.showCalendar('y-mm-dd', e);
			}.bind(this));
		}
		if (this.options.showtime & this.options.editable) {
			this.timeElement = this.element.getParent('.fabrikElementContainer').getElement('.timeField');
			this.timeButton = this.element.getParent('.fabrikElementContainer').getElement('.timeButton');
			if (this.timeButton) {
				this.timeButton.removeEvents('click');
				this.timeButton.addEvent('click', function () {
					this.showTime();
				}.bind(this));
				if (!this.setUp) {
					if (this.timeElement) {
						this.dropdown = this.makeDropDown();
						this.setAbsolutePos(this.timeElement);
						this.setUp = true;
					}
				}
			}
		}
	},

	addNewEvent : function (action, js) {
		// this._getSubElements();
		if (action === 'load') {
			this.loadEvents.push(js);
			this.runLoadEvent(js);
		} else {
			if (!this.element) {
				this.element = $(this.strElement);
			}
			if (action === 'change') {
				window.addEvent('fabrik.date.select', function () {
					typeOf(js) === 'function' ? js.delay(0) : eval(js);
				});
			}
			this.element.getElements('input').each(function (i) {
				i.addEvent(action, function (e) {
					if (typeOf(e) === 'event') {
						e.stop();
					}
					typeOf(js) === 'function' ? js.delay(0) : eval(js);
				});
			}.bind(this));
		}
	},

	update: function (val) {
		var date;
		this.fireEvents([ 'change' ]);
		if (typeOf(val) === 'null' || val === false) {
			return;
		}
		if (!this.options.editable) {
			if (typeOf(this.element) !== 'null') {
				this.element.set('html', val);
			}
			return;
		}
		
		if (this.options.hidden) {
			//if hidden but form set to show time format dont split up the time as we don't 
			// have a time field to put it into
			date = val;
		} else {
			// have to reget the time element as update is called (via reset) in
			// duplicate group code
			// before cloned() method called
			this.timeElement = this.element.getParent('.fabrikElementContainer').getElement('.timeField');
			var bits = val.split(" ");
			date = bits[0];
			var time = (bits.length > 1) ? bits[1].substring(0, 5) : '00:00';
			var timeBits = time.split(":");
			this.hour = timeBits[0];
			this.minute = timeBits[1];
			this.stateTime();
		}
		this.element.getElement('.fabrikinput').value = date;
	},

	showCalendar : function (format, e) {
		if (window.ie) {
			// when scrolled down the page the offset of the calendar is wrong - this
			// fixes it
			var calHeight = $(window.calendar.element).getStyle('height').toInt();
			e = new Event(e);
			var u = ie ? event.clientY + document.documentElement.scrollTop : e.pageY;
			u = u.toInt();
			$(window.calendar.element).setStyles({
				'top' : u - calHeight + 'px'
			});
		}
	},

	getAbsolutePos : function (el) {
		var r = {
			x : el.offsetLeft,
			y : el.offsetTop
		};
		if (el.offsetParent) {
			var tmp = this.getAbsolutePos(el.offsetParent);
			r.x += tmp.x;
			r.y += tmp.y;
		}
		return r;
	},

	setAbsolutePos : function (el) {
		var r = this.getAbsolutePos(el);
		this.dropdown.setStyles({
			position : 'absolute',
			left : r.x,
			top : r.y + 30
		});
	},

	makeDropDown : function () {
		var h = null;
		var handle = new Element('div', {
			styles : {
				'height' : '20px',
				'curor' : 'move',
				'color' : '#dddddd',
				'padding' : '2px;',
				'background-color' : '#333333'
			},
			'id' : this.startElement + '_handle'
		}).appendText(this.options.timelabel);
		var d = new Element('div', {
			'className' : 'fbDateTime',
			'styles' : {
				'z-index' : 999999,
				display : 'none',
				cursor : 'move',
				width : '264px',
				height : '125px',
				border : '1px solid #999999',
				backgroundColor : '#EEEEEE'
			}
		});

		d.appendChild(handle);
		for (var i = 0; i < 24; i++) {
			h = new Element('div', {
				styles: {
					width: '20px',
					'float': 'left',
					'cursor': 'pointer',
					'background-color': '#ffffff',
					'margin': '1px',
					'text-align': 'center'
				}
			});
			h.innerHTML = i;
			h.className = 'fbdateTime-hour';
			d.appendChild(h);
			$(h).addEvent('click', function (event) {
				var e = new Event(event);
				this.hour = $(e.target).innerHTML;
				this.stateTime();
				this.setActive();
			}.bind(this));
			$(h).addEvent('mouseover', function (event) {
				var e = new Event(event);
				var h = $(e.target);
				if (this.hour !== h.innerHTML) {
					e.target.setStyles({
						background : '#cbeefb'
					});
				}
			}.bind(this));
			$(h).addEvent('mouseout', function (event) {
				var e = new Event(event);
				var h = $(e.target);
				if (this.hour !== h.innerHTML) {
					h.setStyles({
						background : this.buttonBg
					});
				}
			}.bind(this));
		}
		var d2 = new Element('div', {
			styles : {
				clear : 'both',
				paddingTop : '5px'
			}
		});
		for (i = 0; i < 12; i++) {
			h = new Element('div', {
				styles : {
					width : '41px',
					'float' : 'left',
					'cursor' : 'pointer',
					'background' : '#ffffff',
					'margin' : '1px',
					'text-align' : 'center'
				}
			});
			h.setStyles();
			h.innerHTML = ':' + (i * 5);
			h.className = 'fbdateTime-minute';
			d2.appendChild(h);
			$(h).addEvent('click', function (e) {
				this.minute = this.formatMinute(e.target.innerHTML);
				this.stateTime();
				this.setActive();
			}.bind(this));
			h.addEvent('mouseover', function (e) {
				var h = e.target;
				if (this.minute !== this.formatMinute(h.innerHTML)) {
					e.target.setStyles({
						background : '#cbeefb'
					});
				}
			}.bind(this));
			h.addEvent('mouseout', function (e) {
				var h = e.target;
				if (this.minute !== this.formatMinute(h.innerHTML)) {
					e.target.setStyles({
						background : this.buttonBg
					});
				}
			}.bind(this));
		}
		d.appendChild(d2);

		document.addEvent('click', function (e) {
			if (this.timeActive) {
				var t = e.target;
				if (t !== this.timeButton && t !== this.timeElement) {
					if (!t.within(this.dropdown)) {
						this.hideTime();
					}
				}
			}
		}.bind(this));
		d.injectInside(document.body);
		var mydrag = new Drag.Move(d);
		return d;
	},

	toggleTime : function () {
		if (this.dropdown.style.display === 'none') {
			this.doShowTime();
		} else {
			this.hideTime();
		}
	},

	doShowTime : function () {
		this.dropdown.setStyles({
			'display' : 'block'
		});
		this.timeActive = true;
		window.fireEvent('fabrik.date.showtime', this);
	},

	hideTime: function () {
		this.timeActive = false;
		this.dropdown.setStyles({
			'display': 'none'
		});
		this.form.doElementValidation(this.element.id);
		window.fireEvent('fabrik.date.hidetime', this);
		window.fireEvent('fabrik.date.select', this);
	},

	formatMinute: function (m) {
		m = m.replace(':', '');
		if (m.length === 1) {
			m = '0' + m;
		}
		return m;
	},

	stateTime: function () {
		if (this.timeElement) {
			var newv = this.hour + ':' + this.minute;
			var changed = this.timeElement.value !== newv;
			this.timeElement.value = newv;
			if (changed) {
				this.fireEvents([ 'change' ]);
			}
		}
	},

	showTime: function () {
		this.setAbsolutePos(this.timeElement); // need to recall if using tabbed form
		this.toggleTime();
		this.setActive();
	},

	setActive: function () {
		var hours = this.dropdown.getElements('.fbdateTime-hour');
		hours.each(function (e) {
			e.setStyles({
				backgroundColor: this.buttonBg
			});
		}, this);
		var mins = this.dropdown.getElements('.fbdateTime-minute');
		mins.each(function (e) {
			e.setStyles({
				backgroundColor: this.buttonBg
			});
		}, this);
		hours[this.hour.toInt()].setStyles({
			backgroundColor: this.buttonBgSelected
		});
		mins[this.minute / 5].setStyles({
			backgroundColor: this.buttonBgSelected
		});
	},
	
	addEventToCalOpts: function () {
		var form = this.form;
		var elid = this.element.id;
		var el = this;
		var onclose = function (e) {
			window.fireEvent('fabrik.date.close', this);
			this.hide();
			try {
				form.triggerEvents(elid, ["blur", "click", "change"], el);
			} catch (err) {
				fconsole(err);
			}
		};
		var onselect = function (calendar, date) {
			elementid = calendar.params.inputField.id.replace('_cal', '');
			calendar.params.inputField.value = date;
			window.fireEvent('fabrik.date.select', this);
			if (calendar.dateClicked) {
				calendar.callCloseHandler();
			}
		};
		
		var datechange = function (date) {
			try {
				return disallowDate(this, date);
			} catch (err) {
				//fconsole(err);
			}
		};
		this.options.calendarSetup.onClose = onclose;
		this.options.calendarSetup.onSelect = onselect;
		this.options.calendarSetup.dateStatusFunc = datechange;
	},

	cloned : function (c) {
		this.setUp = false;
		this.hour = 0;
		this.watchButtons();
		var button = this.element.getElement('img');
		if (button) {
			button.id = this.element.id + "_img";
		}
		var datefield = this.element.getElement('input');
		datefield.id = this.element.id + "_cal";
		this.options.calendarSetup.inputField = datefield.id;
		this.options.calendarSetup.button = this.element.id + "_img";
		
		if (this.options.typing === false) {
			this.disableTyping();
		}
		this.addEventToCalOpts();
		if (this.options.hidden !== true) {
			Calendar.setup(this.options.calendarSetup);
		}
	}
});

/// you can add custom events with:
	/*
	 * window.addEvent('fabrik.date.select', function () {
		console.log('trigger custom date event');
	})
 */