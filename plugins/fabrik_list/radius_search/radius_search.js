function geoCode() {
	window.addEvent('domready', function () {
		var latlng = new google.maps.LatLng(Fabrik.radiusSearch.geocode_default_lat, Fabrik.radiusSearch.geocode_default_long);
		var mapOptions = {
			zoom: 4,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		Fabrik.radiusSearch = typeOf(Fabrik.radiusSearch) === 'null' ? {} : Fabrik.radiusSearch;
		var radiusSearchMaps = document.getElements('.radius_search_geocode_map');
		radiusSearchMaps.each(function (map) {
			Fabrik.radiusSearch[map.id] = typeOf(Fabrik.radiusSearch[map.id]) === 'null' ? {} : Fabrik.radiusSearch[map.id];
			Fabrik.radiusSearch[map.id].map = new google.maps.Map(map, mapOptions);
			var c = map.getParent('.radius_search_geocode');
			var uberC = c.getParent('.radius_search_options');
			var btn = c.getElement('button');
			var fld = c.getElement('.radius_search_geocode_field');
			
			var doGeoCode = function (e) {
				var address = fld.value;
				var geocoder = new google.maps.Geocoder();
				geocoder.geocode({'address': address}, function (results, status) {
					if (status === google.maps.GeocoderStatus.OK) {
						var loc = results[0].geometry.location;
						uberC.getElement('input[name^=radius_search_geocode_lat]').value = loc.lat();
						uberC.getElement('input[name^=radius_search_geocode_lon]').value = loc.lng();
						Fabrik.radiusSearch[map.id].map.setCenter(results[0].geometry.location);
						Fabrik.radiusSearch[map.id].marker.setPosition(results[0].geometry.location);
						document.id('radius_search_lat').value = '';
					} else {
						alert("Geocode was not successful for the following reason: " + status);
					}
				});
			} 
			if (typeOf(btn) !== 'null') {
				btn.addEvent('click', function (e) {
					e.stop();
					doGeoCode();
				});
			} else {
				fld.addEvent('keyup', function (e) {
					doGeoCode();
				});
			}
			
			var zoom = uberC.getElement('input[name=geo_code_def_zoom]').get('value').toInt();
			var lat = uberC.getElement('input[name=geo_code_def_lat]').get('value').toFloat();
			var lon = uberC.getElement('input[name=geo_code_def_lon]').get('value').toFloat();
			Fabrik.fireEvent('google.radiusmap.loaded', [map.id, zoom, lat, lon]);
		});
	});
}

	
var FbListRadiusSearch = new Class({
	Extends : FbListPlugin,
	
	options: {
		geocode_default_lat: '0',
		geocode_default_long: '0',
		geocode_default_zoom: 4,
		prefilter: true,
		prefilterDistance: 1000,
		prefilterDone: false
	},
	
	geocoder: null,
	map: null,
	

	initialize : function (options) {
		this.parent(options);
		Fabrik.radiusSearch = {};
		
		var mapid = 'radius_search_geocode_map' + this.options.renderOrder;
		if (typeOf(Fabrik.radiusSearch[mapid]) === 'null') {
			Fabrik.radiusSearch[mapid] = {};
		}
		Fabrik.radiusSearch[mapid].geocode_default_lat = this.options.geocode_default_lat;
		Fabrik.radiusSearch[mapid].geocode_default_long = this.options.geocode_default_long;
		Fabrik.radiusSearch[mapid].geocode_default_zoom = this.options.geocode_default_zoom;
		head.ready(function () {
			
			Fabrik.addEvent('google.radiusmap.loaded', function (mapid, zoom, lat, lon) {
				var latlng = new google.maps.LatLng(lat, lon);
				if (Fabrik.radiusSearch[mapid].loaded) {
					return;
				}
				Fabrik.radiusSearch[mapid].loaded = true;
				Fabrik.radiusSearch[mapid].map.setCenter(latlng);
				Fabrik.radiusSearch[mapid].map.setZoom(zoom);
				Fabrik.radiusSearch[mapid].marker = new google.maps.Marker({
					map: Fabrik.radiusSearch[mapid].map,
					draggable: true,
					position: latlng
				});
				
				google.maps.event.addListener(Fabrik.radiusSearch[mapid].marker, "dragend", function () {
					var loc = Fabrik.radiusSearch[mapid].marker.getPosition();
					var uberC = document.id(mapid).getParent('.radius_search_options');
					uberC.getElement('input[name=radius_search_geocode_lat]').value = loc.lat();
					uberC.getElement('input[name=radius_search_geocode_lon]').value = loc.lng();
				});
			}.bind(this));
			
			Fabrik.loadGoogleMap(true, 'geoCode');
			
			this.listform = this.listform.getElement('#radius_search' + this.options.renderOrder);
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
		if (a.length > 0 && a[0].get('value') === '0') {
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