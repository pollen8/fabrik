/**
 * List Radius Lookup
 *
 * @copyright: Copyright (C) 2005-2014, fabrikar.com - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

define(['jquery', 'fab/list-plugin'], function (jQuery, FbListPlugin) {
	var FbListRadius_lookup = new Class({
		Extends: FbListPlugin,

		options: {},

		initialize: function (options) {
			this.parent(options);

			if (typeOf(this.options.value) === 'null') {
				this.options.value = 0;
			}

			var clear = this.listform.getElement('.clearFilters');
			console.log('clear = ', clear);
			clear.addEvent('mouseup', function () {
				this.clearFilter();
			}.bind(this));

			if (typeOf(this.listform) !== 'null') {
				this.listform = this.listform.getElement('#radius_lookup' + this.options.renderOrder);
				if (typeOf(this.listform) === 'null') {
					fconsole('didnt find element #radius_lookup' + this.options.renderOrder);
					return;
				}
			}

			//this.options.value = this.options.value.toInt();
			if (typeOf(this.listform) === 'null') {
				return;
			}

			if (geo_position_js.init()) {
				geo_position_js.getCurrentPosition(function (p) {
						this.setGeoCenter(p);
					}.bind(this),
					function (e) {
						this.geoCenterErr(e);
					}.bind(this), {
						enableHighAccuracy: true
					});
			}
		},

		setGeoCenter: function (p) {
			this.geocenterpoint = p;
			this.geoCenter(p);
		},

		geoCenter: function (p) {
			if (typeOf(p) === 'null') {
				window.alert(Joomla.JText._('PLG_VIEW_RADIUS_NO_GEOLOCATION_AVAILABLE'));
			} else {
				this.listform.getElement('input[name=radius_search_lat' + this.options.renderOrder + ']').value = 
					p.coords.latitude.toFixed(2);
				this.listform.getElement('input[name=radius_search_lon' + this.options.renderOrder + ']').value = 
					p.coords.longitude.toFixed(2);
			}
		},

		clearFilter: function () {
			this.listform.getElements('select').set('value', '');
			return true;
		}
	});

	return FbListRadius_lookup;
});
