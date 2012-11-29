var FbOpenStreetMap = new Class({
	Extends: FbElement,
	
	options: {
		'lat': 0,
		'lon': 0,
		'zoomlevel': '13',
		'drag': 0
	},
	
	initialize : function (element, options) {
		this.parent(element, options);
		this.element_map = element + "_map";

		// cofig stuff for openlayers
		// avoid pink tiles
		OpenLayers.IMAGE_RELOAD_ATTEMPTS = 3;
		OpenLayers.Util.onImageLoadErrorColor = "transparent";
	
	
		var opts = {};
		this.map = new OpenLayers.Map(this.element_map, opts);

		switch (this.options.defaultLayer) {
		case 'openlayers':
			this.openLayers();
			break;
		case 'yahoo':
			this.yahoo();
			break;
		case 'google':
			this.google();
			break;
		case 'virtualearth':
			this.virtualEarth();
			break;
		}
		
		if (this.options.defaultLayer !== 'openlayers') {
			this.openLayers();
		}
		
		/** create Google layers*/
		if (this.options.layers.google === 1 && this.options.defaultLayer !== 'google') {
			this.google();
		}

		/** create Virtual Earth layers */
		if (this.options.layers.virtualEarth === 1 && this.options.defaultLayer !== 'virtualearth') {
			this.virtualEarth();
		}

		/** create Yahoo layer */
		if (this.options.layers.yahoo === 1 && this.options.defaultLayer !== 'yahoo') {
			this.yahoo();
		}
		
		var lonLat = this.getLonLat(this.options.lon, this.options.lat);
		this.map.setCenter(lonLat, this.options.zoomlevel.toInt());

		this.addMarker();
		this.map.addControl(new OpenLayers.Control.LayerSwitcher());

		var controls = {
			drag: new OpenLayers.Control.DragMarker(this.markers, {'onComplete': this.dragComplete.bindWithEvent(this)})
		};

		for (var key in controls) {
			this.map.addControl(controls[key]);
		}
		if (this.options.editable === true) {
			controls.drag.activate();
			this.map.addControl(new OpenLayers.Control.MousePosition());
		}
	},
	
	dragComplete: function (marker) {
		var m = marker.lonlat;
		var str = m.toShortString() + ":" + this.map.getZoom();
		this.element.value = str;
	},

	getLonLat : function (lon, lat) {
		var lonlat = new OpenLayers.LonLat(parseFloat(lon), parseFloat(lat));
		return lonlat;
	},
	
	addMarker : function () {
		this.markers = new OpenLayers.Layer.Markers("");
		this.map.addLayer(this.markers);
		var point = this.getLonLat(this.options.lon, this.options.lat);
		var size = new OpenLayers.Size(20, 34);
		var offset = new OpenLayers.Pixel(-(size.w / 2), -size.h);
		var icon = new OpenLayers.Icon('http://boston.openguides.org/markers/AQUA.png', size, offset);
		var marker = new OpenLayers.Marker(point, icon);
		this.markers.addMarker(marker);
	},
	
	openLayers : function () {
		layerTilesAtHome = new OpenLayers.Layer.OSM.Osmarender("Open streetmap");
		this.map.addLayer(layerTilesAtHome);
	},
	
	yahoo : function () {
		var layers = [];
		layers.push(new OpenLayers.Layer.Yahoo("Yahoo", {}));
		layers.push(new OpenLayers.Layer.Yahoo("Yahoo Satellite", {
			'type' : YAHOO_MAP_SAT
		}));
		layers.push(new OpenLayers.Layer.Yahoo("Yahoo Hybrid", {
			'type' : YAHOO_MAP_HYB
		}));
		this.map.addLayers(layers);
	},
	
	google : function () {
		var layers = [];
		layers.push(new OpenLayers.Layer.Google("Google Streets", {}));
		layers.push(new OpenLayers.Layer.Google("Google Satellite", {
			type : G_SATELLITE_MAP
		}));
		layers.push(new OpenLayers.Layer.Google("Google Hybrid", {
			type : G_HYBRID_MAP
		}));
		this.map.addLayers(layers);
	},
	
	virtualEarth : function () {
		var layers = [];
		layers.push(new OpenLayers.Layer.VirtualEarth("Virtual Earth Roads", {
			'type' : VEMapStyle.Road
		}));
		layers.push(new OpenLayers.Layer.VirtualEarth("Virtual Earth Aerial", {
			'type' : VEMapStyle.Aerial
		}));
		layers.push(new OpenLayers.Layer.VirtualEarth("Virtual Earth Hybrid", {
			'type' : VEMapStyle.Hybrid
		}));
		this.map.addLayers(layers);
	}
});
