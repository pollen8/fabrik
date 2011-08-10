var FbDateTime = new Class({
	Extends: FbElement,
	initialize: function(element, options) {
		this.parent(element, options);
		this.hour = '0';
		this.plugin = 'fabrikdate';
		this.minute = '00';
		this.buttonBg = '#ffffff';
		this.buttonBgSelected = '#88dd33';
		this.startElement = element;
		this.setUp = false;
		this.watchButtons();
		if (this.options.editable !== false) {
			//debugger;
			//this.options.calendarSetup.defaultDate = this.element.getElement('.fabrikinput').get('value');
			var calendar = new CalendarEightysix(this.getElement().getElement('input').id, this.options.calendarSetup);
			if(this.options.validations){
				calendar.addEvent('change', function(e){
					this.form.doElementValidation(this.options.element);
				}.bind(this));
			}
			if(this.options.typing == false){
				//yes we really can set the none existant 'readonly' property of the subelement container
				//and get it when checking the validations - cool or what?
				this.element.setProperty('readonly', 'readonly');
				this.element.getElements('.fabrikinput').each(function(f){
					f.addEvent('focus', function(){
						if(f.hasClass('timeField')){
							this.element.findClassUp('fabrikElementContainer').getElement('.timeButton').fireEvent('click');
						}
					}.bind(this));
				}.bind(this));
			}
		}
	},
	
	getValue:function(){
		if(!this.options.editable){
			return this.options.value;
		}
		this.getElement();
		var v = this.element.getElement('.fabrikinput').get('value');
		if(this.options.showtime == true && this.timeElement){
			v += ' ' + this.timeElement.get('value');
		}
		return(v);
	},
	
	watchButtons: function(){
		if(this.options.showtime & this.options.editable){
			this.timeElement = this.element.findClassUp('fabrikElementContainer').getElement('.timeField');
			this.timeButton = this.element.findClassUp('fabrikElementContainer').getElement('.timeButton');
			if(this.timeButton){
				this.timeButton.removeEvents('click');
				this.timeButton.addEvent('click', this.showTime.bindWithEvent(this));
				if(!this.setUp){
					if(this.timeElement){
						this.dropdown = this.makeDropDown();
						this.setAbsolutePos(this.timeElement);
						this.setUp = true;
					}
				}
			}
		}	
	},
	hasSubElements: function(){
		return true;
	},

	addNewEvent: function(action, js ){
		//this._getSubElements();
		if(action == 'load'){
			eval(js);
		}else{
			if(!this.element){
				this.element = $(this.strElement);
			}
			this.element.getElements('input').each(function(i){
				i.addEvent(action, function(e){
					eval(js);
					e.stop();
				});	
			}.bind(this));
		}
	},
	
	update: function(val){
		this.fireEvents(['change']);
		if(typeOf(val) === 'null' || val === false){
			return;
		}
		if (!this.options.editable) {
			this.element.innerHTML = val;
			return;
		}
		//have to reget the time element as update is called (via reset) in duplicate group code
		//before cloned() method called
		this.timeElement = this.element.findClassUp('fabrikElementContainer').getElement('.timeField');
		var bits = val.split(" ");
		var date = bits[0];
		var time = (bits.length > 1) ? bits[1].substring(0, 5) : '00:00';
		var timeBits = time.split(":");
		this.hour = timeBits[0];
		this.minute = timeBits[1];
		this.element.getElement('.fabrikinput').value = date;
		this.stateTime();
	},
	
	/*showCalendar:function(format, e){
		if(window.ie){
			//when scrolled down the page the offset of the calendar is wrong - this fixes it
			var calHeight = $(window.calendar.element).getStyle('height').toInt();
			e = new Event(e);
			var u = ie ? event.clientY + document.documentElement.scrollTop : e.pageY;
			u = u.toInt();
			$(window.calendar.element).setStyles({'top': u - calHeight + 'px'});
		}
	},*/
	
	getAbsolutePos: function(el) {
		var r = { x: el.offsetLeft, y: el.offsetTop };
		if (el.offsetParent) {
			var tmp = this.getAbsolutePos(el.offsetParent);
			r.x += tmp.x;
			r.y += tmp.y;
		}
		return r;
	},
	
	setAbsolutePos: function(el){
		var r = this.getAbsolutePos(el);
		this.dropdown.setStyles({position:'absolute', left:r.x, top:r.y + 30});
	},

	makeDropDown:function(){
		var h = null;
		var handle = new Element('div', {
			styles:{
				'height':'20px',
				'curor':'move',
				'color':'#dddddd',
				'padding':'2px;',
				'background-color':'#333333'
			},
			'id':this.startElement + '_handle'
		}).appendText(this.options.timelabel);
		var d = new Element('div', {
			'className':'fbDateTime',
			'styles':{
				'z-index':999999,
				display:'none',
				cursor:'move',width:'264px',height:'125px',border:'1px solid #999999',backgroundColor:'#EEEEEE'
			}
		});
	
		d.appendChild(handle);
		for(var i=0;i<24;i++){
			h = new Element('div', {styles:{width:'20px','float':'left','cursor':'pointer','background-color':'#ffffff','margin':'1px','text-align':'center'}});
			h.innerHTML = i;
			h.className = 'fbdateTime-hour';
			d.appendChild(h);
			$(h).addEvent('click', function(event){
				var e = new Event(event);
				this.hour = $(e.target).innerHTML;
				this.stateTime();
				this.setActive();
			}.bind(this));
			$(h).addEvent('mouseover', function(event){
				var e = new Event(event);
				var h = $(e.target);
				if(this.hour != h.innerHTML){
					e.target.setStyles({background:'#cbeefb'});
				}
			}.bind(this));
			$(h).addEvent('mouseout', function(event){
				var e = new Event(event);
				var h = $(e.target);
				if(this.hour != h.innerHTML){
					h.setStyles({background:this.buttonBg});
				}
			}.bind(this));
		}
		var d2 = new Element('div', {styles:{clear:'both',paddingTop:'5px'}});
		for(i=0;i<12;i++){
			h = new Element('div', {styles:{width:'41px','float':'left','cursor':'pointer','background':'#ffffff','margin':'1px','text-align':'center'}});
			h.setStyles();
			h.innerHTML = ':' + (i * 5);
			h.className = 'fbdateTime-minute';
			d2.appendChild(h);
			$(h).addEvent('click', function(e){
				e = new Event(e);
				this.minute = this.formatMinute(e.target.innerHTML);
				this.stateTime();
				this.setActive();
			}.bind(this));
			h.addEvent('mouseover', function(event){
				var e = new Event(event);
				var h = $(e.target);
				if(this.minute != this.formatMinute(h.innerHTML)){
					e.target.setStyles({background:'#cbeefb'});
				}
			}.bind(this));
			h.addEvent('mouseout', function(event){
				var e = new Event(event);
				var h = $(e.target);
				if(this.minute != this.formatMinute(h.innerHTML)){
					e.target.setStyles({background:this.buttonBg});	
				}
			}.bind(this));
		}
		d.appendChild(d2);

		document.addEvent('click', function(event){
			if(this.timeActive){
				var e = new Event(event);
				var t = $(e.target);
				if(t != this.timeButton && t != this.timeElement){
					if(!t.within(this.dropdown)){
						this.hideTime();
					}
				}
			}
		}.bind(this));
		d.inject(document.body);
		var mydrag = new Drag.Move(d);
		return d;
	},
	
	toggleTime: function(){
		if(this.dropdown.style.display == 'none'){
			this.doShowTime();
		}else{
			this.hideTime();
		}
	},
	
	doShowTime:function(){
		this.dropdown.setStyles({'display':'block'});
		this.timeActive = true;
	},
	
	hideTime:function(){
		this.timeActive = false;
		this.dropdown.setStyles({'display':'none'});
		this.form.doElementValidation(this.element.id);
	},
	
	formatMinute:function(m){
		m = m.replace(':','');
		if(m.length == 1){
			m = '0' + m;
		}
		return m;
	},

	stateTime:function(){
		if(this.timeElement){
			var newv = this.hour+ ':' + this.minute;
			var changed = this.timeElement.value != newv;
			this.timeElement.value = newv;
			if(changed){
				this.fireEvents(['change']);
			}
		}
	},

	showTime:function(){
		this.setAbsolutePos(this.timeElement); //need to recall if using tabbed form
		this.toggleTime();
		this.setActive();
	},

	setActive: function(){
		var hours = this.dropdown.getElements('.fbdateTime-hour');
		hours.each(function(e){
			e.setStyles({backgroundColor:this.buttonBg});
		}, this);
		var mins = this.dropdown.getElements('.fbdateTime-minute');
		mins.each(function(e){
			e.setStyles({backgroundColor:this.buttonBg});
		}, this);
		hours[this.hour].setStyles({backgroundColor:this.buttonBgSelected});
		mins[this.minute / 5].setStyles({backgroundColor:this.buttonBgSelected});
	},
	
	cloned: function(c){
		this.setUp = false;
		this.hour = 0;
		this.watchButtons();
		//var button = this.element.getElement('img');
		var button = this.element.getElement('div.picker');
		button.id = this.element.id + "_img";
		var datefield = this.element.getElement('input');
		datefield.id = this.element.id + "_cal";
		this.options.calendarSetup.inputField = datefield.id;
		this.options.calendarSetup.button = this.element.id + "_img";
		//Calendar.setup(this.options.calendarSetup);
	}
});

