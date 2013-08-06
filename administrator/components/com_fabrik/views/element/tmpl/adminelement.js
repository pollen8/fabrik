/**
 * Admin Element Editor
 *
 * @copyright: Copyright (C) 2005-2013, fabrikar.com - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// Contains all the javascript element plugin objects
var pluginControllers = [];

var fabrikAdminElement = new Class({

	Extends: PluginManager,

	Implements: [Options, Events],

	options: {
		id: 0,
		parentid: 0,
		jsevents: [],
		deleteButton : 'removeButton'
	},

	initialize: function (plugins, options, id) {
		this.parent(plugins, id, 'validationrule');
		this.setOptions(options);
		this.setParentViz();

		this.jsCounter = -1;
		if (typeOf(document.id('addJavascript')) === false) {
			fconsole('add js button not found');
		} else {
			document.id('addJavascript').addEvent('click', function (e) {
				e.stop();
				this.addJavascript();
			}.bind(this));
		}
		this.options.jsevents.each(function (opt) {
			this.addJavascript(opt);
		}.bind(this));

		document.id('jform_plugin').addEvent('change', function (e) {
			this.changePlugin(e);
		}.bind(this));
		
		document.id('javascriptActions').addEvent('click:relay(a[data-button=removeButton])', function (e, target) {
			e.stop();
			this.deleteJS(target);
		}.bind(this));

	},

	changePlugin: function (e) {
		document.id('plugin-container').empty().adopt(
		new Element('span').set('text', 'Loading....')
		);
		var myAjax = new Request({
			url: 'index.php',
			'evalResponse': false,
			'evalScripts' : function (script, text) {
					this.script = script;
				}.bind(this),
			'data': {
				'option': 'com_fabrik',
				'id': this.options.id,
				'task': 'element.getPluginHTML',
				'format': 'raw',
				'plugin': e.target.get('value')
			},
			'update': document.id('plugin-container'),
			'onComplete': function (r) {
				document.id('plugin-container').set('html', r);
				Browser.exec(this.script);
				this.updateBootStrap();
				FabrikAdmin.reTip();
			}.bind(this)
		});
		Fabrik.requestQueue.add(myAjax);
	},

	deleteJS: function (target) {
		target.getParent('fieldset').dispose();
		this.jsCounter --;
	},

	addJavascript: function (opt) {
		var jsId = opt && opt.id ? opt.id : 0;
		// Ajax request to load the first part of the plugin form (do[plugin] in, on)
		var request = new Request.HTML({
			url: 'index.php',
			data: {
				'option': 'com_fabrik',
				'view': 'plugin',
				'task': 'top',
				'format': 'raw',
				'type': 'elementjavascript',
				'plugin': null,
				'plugin_published': true,
				'c': this.jsCounter,
				'id': jsId,
				'elementid': this.id
			},
			append: document.id('javascriptActions'),
			onSuccess: function (res) {
				this.updateBootStrap();
				FabrikAdmin.reTip();
			}.bind(this),
			onFailure: function (xhr) {
				console.log('fail', xhr);
			},
			onException: function (headerName, value) {
				console.log('exception', headerName, value);
			}
		});
		Fabrik.requestQueue.add(request);
		this.updateBootStrap();
		FabrikAdmin.reTip();
		this.jsCounter ++;
	},

	setParentViz: function () {
		if (this.options.parentid.toInt() !== 0) {
			myFX = new Fx.Tween('elementFormTable', {property: 'opacity', duration: 500, wait: false}).set(0);
			document.id('unlink').addEvent('click', function (e) {
				var s = (this.checked) ? "" : "readonly";
				if (this.checked) {
					myFX.start(0, 1);
				}
				else {
					myFX.start(1, 0);
				}
			});
		}
		if (document.id('swapToParent')) {
			document.id('swapToParent').addEvent('click', function (e) {
				var f = document.adminForm;
				f.task.value = 'element.parentredirect';
				var to = e.target.className.replace('element_', '');
				f.redirectto.value = to;
				f.submit();
			});
		}
	}
});
