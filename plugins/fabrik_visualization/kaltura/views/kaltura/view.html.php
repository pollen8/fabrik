<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class fabrikViewKaltura extends JView
{

	function display($tmpl = 'default')
	{
		FabrikHelperHTML::framework();
		$app = JFactory::getApplication();
		$params = $app->getParams('com_fabrik');
		$document = JFactory::getDocument();
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$model = $this->getModel();
		$model->setId(JRequest::getVar('id', $usersConfig->get('visualizationid', JRequest::getInt('visualizationid', 0) )));
		$this->row = $model->getVisualization();
		$params = $model->getParams();
		$this->assign('params', $params);

		$pluginParams = $model->getPluginParams();
		$tmpl = $pluginParams->get('fb_gm_layout', $tmpl);
		$tmplpath = JPATH_ROOT.DS.'plugins/fabrik_visualization/kaltura/views/kaltura/tmpl'.DS.$tmpl;

		$js = <<<EOT
		<script type="text/javascript" >
function entryClicked ( entry_id )
{
	window.location = "./player.php?entry_id=" + entry_id;
}
</script>
EOT;
		$this->assignRef('data', $this->get('Data'));
		FabrikHelperHTML::addScriptDeclaration($js);
		FabrikHelperHTML::stylesheetFromPath('plugins/fabrik_visualization/kaltura/views/kaltura/tmpl/' . $tmpl . '/template.css');
		$template = null;
		$this->assign('containerId', $this->get('ContainerId'));
		$this->assign('showFilters', JRequest::getInt('showfilters', $params->get('show_filters')) === 1 ?  1 : 0);
		$this->assignRef('filters', $this->get('Filters'));
		$this->_setPath('template', $tmplpath);

		echo parent::display($template);
	}

}
?>