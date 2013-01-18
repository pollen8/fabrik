<?php
/**
 * Fabrik Kaltura Viz HTML View
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.kaltura
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * Fabrik Kaltura Viz HTML View
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.kaltura
 * @since       3.0
 */

class fabrikViewKaltura extends JViewLegacy
{

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */

	function display($tpl = 'default')
	{
		FabrikHelperHTML::framework();
		$app = JFactory::getApplication();
		$input = $app->input;
		$params = $app->getParams('com_fabrik');
		$document = JFactory::getDocument();
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$model = $this->getModel();
		$model->setId($input->getInt('id', $usersConfig->get('visualizationid', $input->getInt('visualizationid', 0))));
		$this->row = $model->getVisualization();
		$params = $model->getParams();
		$this->params = $params;

		$tpl = $params->get('fb_gm_layout', $tpl);
		$tmplpath = JPATH_ROOT . '/plugins/fabrik_visualization/kaltura/views/kaltura/tmpl/' . $tpl;

		$js = <<<EOT
		<script type="text/javascript" >
function entryClicked ( entry_id )
{
	window.location = "./player.php?entry_id=" + entry_id;
}
</script>
EOT;
		$this->data = $this->get('Data');
		FabrikHelperHTML::addScriptDeclaration($js);
		FabrikHelperHTML::stylesheetFromPath('plugins/fabrik_visualization/kaltura/views/kaltura/tmpl/' . $tpl . '/template.css');
		$template = null;
		$this->containerId = $this->get('ContainerId');
		$this->showFilters = $input->getInt('showfilters', $params->get('show_filters')) === 1 ? 1 : 0;
		$this->filters = $this->get('Filters');
		$this->_setPath('template', $tmplpath);

		echo parent::display($template);
	}

}
