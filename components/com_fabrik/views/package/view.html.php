<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class fabrikViewPackage extends JView
{

	function display($tpl = null)
	{
		FabrikHelperHTML::framework();
		$state = $this->get('State');
		$item = $this->get('Item');
		$document = JFactory::getDocument();
		FabrikHelperHTML::script('media/com_fabrik/js/icons.js');
		FabrikHelperHTML::script('media/com_fabrik/js/icongen.js');
		FabrikHelperHTML::script('media/com_fabrik/js/canvas.js');
		FabrikHelperHTML::script('media/com_fabrik/js/history.js');
		FabrikHelperHTML::script('media/com_fabrik/js/keynav.js');
		FabrikHelperHTML::script('media/com_fabrik/js/tabs.js');
		FabrikHelperHTML::script('media/com_fabrik/js/pages.js');
		FabrikHelperHTML::script('media/com_fabrik/js/frontpackage.js');

		FabrikHelperHTML::stylesheet('media/com_fabrik/css/package.css');
		$canvas = $item->params->get('canvas');
		$opts = JArrayHelper::getvalue($canvas, 'options', array());
		$tabs = JArrayHelper::getValue($canvas, 'tabs', array('Page 1'));
		$tabs = json_encode($tabs);
		$d = new stdClass();
		$layout = JArrayHelper::getValue($canvas, 'layout', $d);

		$layout = json_encode(JArrayHelper::getValue($canvas, 'layout', $d));
		$id =$this->get('State')->get('package.id');
		$script = "head.ready(function() {
			new FrontPackage({
		tabs : $tabs,
		tabelement : 'packagemenu',
		pagecontainer : 'packagepages',
		layout: $layout,
		'packageid':$id,
		'package':'$item->component_name'
	});
		});";
		$document->addScriptDeclaration($script);

		//force front end templates
		$this->_basePath = COM_FABRIK_FRONTEND.DS.'views';
		$tmpl = !isset($item->template) ? 'default' : $item->template;
		$this->addTemplatePath($this->_basePath.DS.$this->_name.DS.'tmpl'.DS.$tmpl);
		$text = $this->loadTemplate();
		$opt = JRequest::getVar('option');
		JRequest::setVar('option', 'com_content');
		jimport('joomla.html.html.content');
		$text .= '{emailcloak=off}';
		$text = JHTML::_('content.prepare', $text);
		$text = preg_replace('/\{emailcloak\=off\}/', '', $text);
		JRequest::setVar('option', $opt);

		echo $text;
	}

}
?>