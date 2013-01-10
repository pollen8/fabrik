function geoCode() {
	window.addEvent('domready', function () {
		var latlng = new google.maps.LatLng(-34.397, 150.644);
		var mapOptions = {
			zoom: 8,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		Fabrik.radiusSearch = {};
		Fabrik.radiusSearch.map = new google.maps.Map(document.id('radius_search_geocode_map'), mapOptions);
		geocoder = new google.maps.Geocoder();
		document.id('radius_search_button').addEvent('click', function (e) {
			e.stop();
			var address = document.id('radius_search_geocode_field').value;
			geocoder.geocode({'address': address}, function (results, status) {
				if (status === google.maps.GeocoderStatus.OK) {
					var loc = results[0].geometry.location;
					document.getElement('input[name=radius_search_geocode_lat]').value = loc.lat();
					document.getElement('input[name=radius_search_geocode_lon]').value = loc.lng();
					Fabrik.radiusSearch.map.setCenter(results[0].geometry.location);
					Fabrik.radiusSearch.marker.setPosition(results[0].geometry.location);
					document.id('radius_search_lat').value = '';
				} else {
					alert("Geocode was not successful for the following reason: " + status);
				}
			}); 
		});
		Fabrik.fireEvent('google.radiusmap.loaded');
	});
}

	
var FbListRadiusSearch = new Class({
	Extends : FbListPlugin,
	
	options: {
		prefilter: true,
		prefilterDistance: 1000,
		prefilterDone: false
	},
	
	geocoder: null,
	map: null,
	

	initialize : function (options) {
		this.parent(options);

		head.ready(function () {
			
			Fabrik.addEvent('google.radiusmap.loaded', function () {
				var latlng = new google.maps.LatLng(this.options.lat, this.options.lon);
				Fabrik.radiusSearch.map.setCenter(latlng);
				Fabrik.radiusSearch.marker = new google.maps.Marker({
					map: Fabrik.radiusSearch.map,
					draggable: true,
					position: latlng
				});
				
				google.maps.event.addListener(Fabrik.radiusSearch.marker, "dragend", function () {
					var loc = Fabrik.radiusSearch.marker.getPosition();
					console.log(loc, loc.lat());
					document.getElement('input[name=radius_search_geocode_lat]').value = loc.lat();
					document.getElement('input[name=radius_search_geocode_lon]').value = loc.lng();
					//Fabrik.radiusSearch.map.setCenter(loc);
				});
			}.bind(this));
			
			Fabrik.loadGoogleMap(true, 'geoCode');
			
			this.listform = this.listform.getElement('.radus_search');
			if (typeOf(this.options.value) === 'null') {
				this.options.value = 0;
			}
			this.watchActivate();
			
			this.listform.getElements('input[name^=radius_search_type]').addEvent('click', function (e) {
				this.toggleFields(e);
			}.bind(this));
			
			this.options.value = this.options.value.toInt();
			if (typeOf(this.listform) === 'null') {
				return;
			}
			var output = this.listform.getElement('.radius_search_distance');
			var output2 = this.listform.getElement('.slider_output');
			this.mySlide = new Slider(this.listform.getElement('.fabrikslider-line'), this.listform.getElement('.knob'), {
				onChange : function (pos) {
					output.value = pos;
					output2.set('text', pos + this.options.unit);
				}.bind(this),
				steps : this.options.steps
			}).set(0);

			this.mySlide.set(this.options.value);
			output.value = this.options.value;
			output2.set('text', this.options.value);

			if (this.options.myloc && !this.options.prefilterDone) {
				if (geo_position_js.init()) {
					geo_position_js.getCurrentPosition(function (p) {
						this.setGeoCenter(p);
					}.bind(this), 
					function (e) {
						this.geoCenterErr(e);
					}.bind(this), {
						enableHighAccuracy : true
					});
				}
			}
		}.bind(this));
	},
	
	watchActivate: function () {
		this.fx = new Fx.Slide(this.listform.getElement('.radius_search_options'));
		this.listform.getElements('input[name^=radius_search_active]').addEvent('click', function (e) {
			switch (e.target.get('value')) {
			case '1':
				this.fx.slideIn();
				break;
			case '0':
				this.fx.slideOut();
				break;
			}
		}.bind(this));
		var a = this.listform.getElements('input[name^=radius_search_active]').filter(function (f) {
			return f.checked === true;
		});
		if (a[0].get('value') === '0') {
			this.fx.slideOut();
		}
	},

	setGeoCenter: function (p) {
		this.geocenterpoint = p;
		this.geoCenter(p);
		this.prefilter();
	},
	
	/**
	 * The list is set to prefilter
	 */
	prefilter: function () {
		if (this.options.prefilter) {
			this.fx.slideIn();
			this.mySlide.set(this.options.prefilterDistance);
			
			this.listform.getElements('input[name^=radius_search_active]').filter(function (f) {
				return f.get('value') === '1';
			}).getLast().checked = true;
			
			this.listform.getElements('input[value=mylocation]').checked = true;
			this.list.submit('filter');
		}
	},

	geoCenter : function (p) {
		if (typeOf(p) === 'null') {
			alert(Joomla.JText._('PLG_VIEW_RADIUS_NO_GEOLOCATION_AVAILABLE'));
		} else {
			this.listform.getElement('input[name=radius_search_lat]').value = p.coords.latitude.toFixed(2);
			this.listform.getElement('input[name=radius_search_lon]').value = p.coords.longitude.toFixed(2);
		}
	},

	geoCenterErr : function (p) {
		fconsole('geo location error=' + p.message);
	},

	toggleActive : function (e) {
		
	},

	toggleFields : function (e) {
		switch (e.target.get('value')) {
		case 'latlon':
			this.listform.getElement('.radius_search_place_container').hide();
			this.listform.getElement('.radius_search_coords_container').show();
			this.listform.getElement('.radius_search_geocode').hide();
			break;
		case 'mylocation':
			this.listform.getElement('.radius_search_place_container').hide();
			this.listform.getElement('.radius_search_coords_container').hide();
			this.listform.getElement('.radius_search_geocode').hide();
			this.setGeoCenter(this.geocenterpoint);
			break;
		case 'place':
			this.listform.getElement('.radius_search_place_container').show();
			this.listform.getElement('.radius_search_coords_container').hide();
			this.listform.getElement('.radius_search_geocode').hide();
			break;
		case 'geocode':
			this.listform.getElement('.radius_search_place_container').hide();
			this.listform.getElement('.radius_search_coords_container').hide();
			this.listform.getElement('.radius_search_geocode').show();
			break;
		}
	},

	clearFilter : function () {
		this.listform.getElements('input[name^=radius_search_active]').filter(function (f) {
			return f.get('value') === '0';
		}).getLast().checked = true;
	}

});