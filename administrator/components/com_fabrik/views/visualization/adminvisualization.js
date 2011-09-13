var AdminVisualization = new Class({
	
	Implements: [Options, Events],
	
	options: {},
	
	initialize: function (options, lang) {
		this.setOptions(options);
		this.watchSelector();
	},
	
	watchSelector: function () {
		$('jform_plugin').addEvent('change', function (e) {
			e.stop();
			var myAjax = new Request.HTML({
				url: 'index.php',
				'data': {
					'option': 'com_fabrik',
					'task': 'visualization.getPluginHTML',
					'format': 'raw',
					'plugin': e.target.get('value')
				},
				'update': document.id('plugin-container')
			}).send();
		});
	}
});