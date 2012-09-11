var CronAdmin = new Class({
	
	Implements: [Options, Events],
	
	options: {},
	
	initialize: function (options) {
		this.setOptions(options);
		this.watchSelector();
	},
	
	watchSelector: function () {
		if (jQuery) {
			jQuery('#jform_plugin').bind('change', function (e) {
				this.changePlugin(e);
		    }.bind(this));
		}
		
		document.id('jform_plugin').addEvent('change', function (e) {
			e.stop();
			this.changePlugin(e);
		}.bind(this));
	},
	
	changePlugin: function (e) {
		var myAjax = new Request.HTML({
			url: 'index.php',
			'data': {
				'option': 'com_fabrik',
				'task': 'cron.getPluginHTML',
				'format': 'raw',
				'plugin': e.target.get('value')
			},
			'update': document.id('plugin-container')
		}).send();
	}
});