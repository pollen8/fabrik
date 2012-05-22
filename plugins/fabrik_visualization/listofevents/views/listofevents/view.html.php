<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class fabrikViewListofevents extends JView
{

	function display($tmpl = 'default')
	{
		FabrikHelperHTML::framework();
		$pluginManager = FabrikWorker::getPluginManager();
		//needed to load the language file!
		$plugin = $pluginManager->getPlugIn('calendar', 'visualization');
		$model = $this->getModel();
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$id = JRequest::getVar('id', $usersConfig->get('visualizationid', JRequest::getInt('visualizationid', 0)));
		$model->setId($id);
		$this->assign('id', $id);
		$this->assignRef('row', $this->get('Visualization'));
		$this->assign('rows', $this->get('Rows'));

		$this->assign('containerId', $this->get('ContainerId'));
		$params = $model->getParams();
		$model->setListIds();
		$this->assign('showFilters', JRequest::getInt('showfilters', $params->get('show_filters')) === 1 ?  1 : 0);
		$this->assignRef('filters', $this->get('Filters'));
		$this->assignRef('params', $params);
		$tmpl = $this->params->get('approvals_layout', $tmpl);
		$tmplpath = JPATH_SITE.DS.'plugins'.DS.'fabrik_visualization'.DS.'listofevents'.DS.'views'.DS.'listofevents'.DS.'tmpl'.DS.$tmpl;
		$this->_setPath('template', $tmplpath);

		$ab_css_file = $tmplpath.DS."template.css";

		if (file_exists($ab_css_file)) {
			JHTML::stylesheet('/plugins/fabrik_visualization/listofevents/views/listofevents/tmpl/'.$tmpl.'/template.css');
		}

		$text = $this->loadTemplate();
		$opt = JRequest::getVar('option');
		$view = JRequest::getCmd('view');
		JRequest::setVar('view', 'article');
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