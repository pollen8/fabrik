//move time line to given date:
//timeline.tl.getBand(1).setCenterVisibleDate(new Date('2009', '9', '12'))
//timeline.tl.getBand(1).scrollToCenter(new Date('2009', '9', '12'))

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
			return date.format(dateFormat);
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
		//var bandInfos = bandTracks;
		
		for (var b = 1; b < json.bands.length; b ++) {
			bandTracks[b].syncWith = 0;//json.bands[b].syncWith;
			bandTracks[b].highlight = true;//json.bands[b].highlight;
		}
		console.log(bandTracks);
		this.tl = Timeline.create(document.id("my-timeline"), bandTracks, this.options.orientation);
//*****************************///		
		// create the timeline
		var bandInfos = [ Timeline.createBandInfo({
			trackGap : 0.2,
			width : "30%",
			intervalUnit : Timeline.DateTime.DAY,
			intervalPixels : 50,
			eventSource : eventSource,
			overview : true,
			theme : theme

		}), Timeline.createBandInfo({
			showEventText : false,
			overview : false,
			trackHeight : 0.5,
			trackGap : 0.2,
			width : "40%",
			intervalUnit : Timeline.DateTime.MONTH,
			intervalPixels : 150,
			eventSource : eventSource,
			theme : theme

		}),
		
		 Timeline.createBandInfo({
				trackGap : 0.2,
				width : "30%",
				intervalUnit : Timeline.DateTime.YEAR,
				intervalPixels : 50,
				eventSource : eventSource,
				theme : theme

			})
			];
		bandInfos[1].syncWith = 0;
		bandInfos[1].highlight = true;
		
		bandInfos[2].syncWith = 0;
		bandInfos[2].highlight = true;
		
		console.log(bandInfos);
		//this.tl = Timeline.create(document.id("my-timeline"), bandInfos, this.options.orientation);
		//*****************************///
		
		eventSource.loadJSON(this.json, '');

		window.addEvent('resize', function () {
			if (this.resizeTimerID === null) {
				this.resizeTimerID = window.setTimeout(function () {
					this.resizeTimerID = null;
					this.tl.layout();
				}.bind(this), 500);
			}
		}.bind(this));
	}
});