var FbListRadiusSearch = new Class({
	Extends : FbListPlugin,
	
	options: {
		prefilter: true,
		prefilterDistance: 1000,
		prefilterDone: false
	},
	
	initialize : function (options) {
		this.parent(options);

		window.addEvent('fabrik.loaded', function () {
			this.listform = this.listform.getElement('.radus_search');
			if (typeOf(this.options.value) === 'null') {
				this.options.value = 0;
			}
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
			this.element.getElements('input[name^=radius_search_type]').addEvent('click', function (e) {
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

			if (!this.options.prefilterDone) {
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
			break;
		case 'mylocation':
			this.listform.getElement('.radius_search_place_container').hide();
			this.listform.getElement('.radius_search_coords_container').hide();
			this.setGeoCenter(this.geocenterpoint);
			break;
		case 'place':
			this.listform.getElement('.radius_search_place_container').show();
			this.listform.getElement('.radius_search_coords_container').hide();
			break;
		}
	},

	clearFilter : function () {
		this.listform.getElements('input[name^=radius_search_active]').filter(function (f) {
			return f.get('value') === '0';
		}).getLast().checked = true;
	}

});