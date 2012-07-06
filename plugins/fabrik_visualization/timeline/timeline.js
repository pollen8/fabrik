var FbVisTimeline = new Class({
	
	Implements: [Options],
	
	options: {
		dateFormat: '%c',
		orientation: '0'
	},
	
	initialize : function (json, options) {
		this.json = eval(json);
		this.setOptions(options);

		this.resizeTimerID = null;
		this.tl = null;
		var dateFormat = this.options.dateFormat;

		Timeline.GregorianDateLabeller.prototype.labelPrecise = function(date)
		{
			// Crazy hackery to reset the label time to the correct one.
			// means the Z time format will not give you the correct tz
			var newdate = new Date(date.getTime() + date.getTimezoneOffset() * 60000);
			return newdate.format(dateFormat);
		}; 
		
		var eventSource = new Timeline.DefaultEventSource();

		// TODO: theme the viz in admin
		var theme = Timeline.ClassicTheme.create();
		theme.event.bubble.width = 320;
		theme.event.bubble.height = 520;
		theme.event.track.height = 11.5;
		theme.event.track.gap = 0.1;
		theme.ether.backgroundColors = [ "#000000", "red" ];
		
		theme.ether.highlightColor = 'red';

		Timeline.setDefaultTheme(theme);
		
		var bandBase = {
				trackGap : 0.2,
				width : "70%",
				intervalUnit : Timeline.DateTime.DAY,
				intervalPixels : 50
			}
		
		
		var bandTracks = [];
		
		for (var b = 0; b < json.bands.length; b ++) {
			var bandClone = Object.clone(bandBase);
			bandClone.width = json.bands[b].width;
			bandClone.intervalUnit = json.bands[b].intervalUnit;
			bandClone.overview = json.bands[b].overview;
			bandClone.eventSource = eventSource;
			bandClone.theme = theme;
			bandTracks.push(Timeline.createBandInfo(bandClone));
		}
		
		// Sync the bands to scroll together
		for (var b = 1; b < json.bands.length; b ++) {
			bandTracks[b].syncWith = 0;
			bandTracks[b].highlight = true;
		}
	
		this.tl = Timeline.create(document.id("my-timeline"), bandTracks, this.options.orientation);
		
		eventSource.loadJSON(this.json, '');

		window.addEvent('resize', function () {
			if (this.resizeTimerID === null) {
				this.resizeTimerID = window.setTimeout(function () {
					this.resizeTimerID = null;
					this.tl.layout();
				}.bind(this), 500);
			}
		}.bind(this));
		
		this.watchDatePicker();
	},
	
	watchDatePicker: function () {
		var dateEl = document.id('timelineDatePicker');
		if (typeOf(dateEl) === 'null') {
			return;
		}
		var params = {'eventName': 'click',
				'ifFormat': this.options.dateFormat,
				'daFormat': this.options.dateFormat,
				'singleClick': true,
				'align': "Br",
				'range': [1900, 2999],
				'showsTime': false,
				'timeFormat': '24',
				'electric': true,
				'step': 2,
				'cache': false,
				'showOthers': false,
				'advanced': false };
		var dateFmt = this.options.dateFormat;
		params.date = Date.parseDate(dateEl.value || dateEl.innerHTML, dateFmt);
		params.onClose = function (cal) {
			cal.hide();
		};
		params.onSelect = function () {
			if (this.cal.dateClicked) {
				this.cal.callCloseHandler();
				dateEl.value = this.cal.date.format(dateFmt);
				this.tl.getBand(0).setCenterVisibleDate(this.cal.date);
			}
		}.bind(this);
		
		params.inputField = dateEl;
		params.button = document.id('timelineDatePicker_cal_img');
		params.align = "Tl";
		params.singleClick = true;
		
		this.cal = new Calendar(0,
				params.date,
				params.onSelect,
				params.onClose);
		
		this.cal.showsOtherMonths = params.showOthers;
		this.cal.yearStep = params.step;
		this.cal.setRange(params.range[0], params.range[1]);
		this.cal.params = params;
		
		this.cal.setDateFormat(dateFmt);
		this.cal.create();
		this.cal.refresh();
		this.cal.hide();
		
		params.button.addEvent('click', function (e) {
			this.cal.showAtElement(params.button);
			this.cal.show();
		}.bind(this));
		
		dateEl.addEvent('blur', function (e) {
			this.updateFromField();
		}.bind(this));
		
		dateEl.addEvent('keyup', function (e) {
			console.log(e);
			if (e.key === 'enter') {
				this.updateFromField();
			}
		}.bind(this));
	
	},
	
	updateFromField: function () {
		var dateStr = document.id('timelineDatePicker').value;
		d = Date.parseDate(dateStr, this.options.dateFormat);
		this.cal.date = d;
		var newdate = new Date(this.cal.date.getTime() - (this.cal.date.getTimezoneOffset() * 60000));
		//this.tl.getBand(0).setCenterVisibleDate(newdate);
		this.tl.getBand(0).scrollToCenter(newdate);
		
	}
});