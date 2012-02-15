
OpenLayers.Handler.Marker = OpenLayers.Class.create();
OpenLayers.Handler.Marker.prototype =
  OpenLayers.Class.inherit(OpenLayers.Handler.Feature, {

    handle: function(evt) {    
    		var type = evt.type;
        var node = OpenLayers.Event.element(evt);
        var feature = null;
        for (var i = 0; i < this.layer.markers.length; i++) {
            if (this.layer.markers[i].icon.imageDiv.firstChild == node) {
                feature = this.layer.markers[i];
                break;
            }
        }
        var selected = false;
        if (feature) {
            if (this.geometryTypes == null) {
                // over a new, out of the last and over a new, or still on the last
                if (!this.feature) {
                    // over a new feature
                    this.callback('over', [feature]);
                } else if (this.feature != feature) {
                    // out of the last and over a new
                    this.callback('out', [this.feature]);
                    this.callback('over', [feature]);
                }
                this.feature = feature;
                this.callback(type, [feature]);
                selected = true;
            } else {
                if (this.feature && (this.feature != feature)) {
                    // out of the last and over a new
                    this.callback('out', [this.feature]);
                    this.feature = null;
                }
                selected = false;
            }
        } else {
            if (this.feature) {
                // out of the last
                this.callback('out', [this.feature]);
                this.feature = null;
            }
            selected = false;
        }
        return selected;
    },


    CLASS_NAME: "OpenLayers.Handler.Marker"
});

OpenLayers.Control.DragMarker = OpenLayers.Class.create();
OpenLayers.Control.DragMarker.prototype = 
  OpenLayers.Class.inherit(OpenLayers.Control.DragFeature, {

    initialize: function(layer, options) {
        OpenLayers.Control.prototype.initialize.apply(this, [options]);
        this.layer = layer;
        this.handlers = {
        		drag: new OpenLayers.Handler.Drag(
        			this, OpenLayers.Util.extend({down: this.downFeature,
                                                     move: this.moveFeature,
                                                     up: this.upFeature,
                                                     out: this.cancel,
                                                     done: this.doneDragging
                                                    }, this.dragCallbacks)
						),
						feature: new OpenLayers.Handler.Marker(
							this, this.layer, OpenLayers.Util.extend({over: this.overFeature,
                                                        out: this.outFeature
                                                       }, this.featureCallbacks),
							{geometryTypes: this.geometryTypes}
						)
					};
    },
    
    moveFeature: function(pixel) {
        var px = this.feature.icon.px.add(pixel.x - this.lastPixel.x, pixel.y - this.lastPixel.y);;
        this.feature.moveTo(px);
        this.lastPixel = pixel;
        this.onDrag(this.feature, pixel);
    },

    CLASS_NAME: "OpenLayers.Control.DragMarker"
});

function osm_getTileURL(bounds) {
    var res = this.map.getResolution();
    var x = Math.round((bounds.left - this.maxExtent.left) / (res * this.tileSize.w));
    var y = Math.round((this.maxExtent.top - bounds.top) / (res * this.tileSize.h));
    var z = this.map.getZoom();
    var limit = Math.pow(2, z);

    if (y < 0 || y >= limit) {
        return OpenLayers.Util.getImagesLocation() + "404.png";
    } else {
        x = ((x % limit) + limit) % limit;
        return this.url + z + "/" + x + "/" + y + "." + this.type;
    }
}
