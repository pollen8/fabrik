var fbVisCoverflow = new Class({
	Implements:[Options],
	options:{},
	initialize: function(json, options) {
		json = eval(json);
		this.setOptions(options);
	head.ready(function() {

			widget = Runway.createOrShowInstaller(
			    document.getElementById("coverflow"),
			    {
			        // examples of initial settings
			        // slideSize: 200,
			        // backgroundColorTop: "#fff",
			        
			        // event handlers
			        onReady: function() {
			            widget.setRecords(json);
			        }
			    }
			);
		}.bind(this))
	}
});