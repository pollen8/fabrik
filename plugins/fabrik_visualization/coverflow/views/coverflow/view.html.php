<?php
/**
* @package		Joomla.Plugin
* @subpackage	Fabrik.visualization.coverflow
* @copyright	Copyright (C) 2005 Fabrik. All rights reserved.
* @license		GNU General Public License version 2 or later; see LICENSE.txt
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
* Fabrik Coverflow HTML View
*
* @package		Joomla.Plugin
* @subpackage	Fabrik.visualization.coverflow
*/

class fabrikViewCoverflow extends JViewLegacy
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
		$app = JFactory::getApplication();
		$input = $app->input;
		$srcs = FabrikHelperHTML::framework();
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$model = $this->getModel();
		$id = $input->getInt('id', $usersConfig->get('visualizationid', $input->getInt('visualizationid', 0)));
		$model->setId($id);
		$row = $model->getVisualization();
		if ($this->get('RequiredFiltersFound'))
		{
			$model->render();
		}
		$params = $model->getParams();
		$this->params = $params;
		$this->containerId = $this->get('ContainerId');
		$this->row = $row;
		$this->showFilters = $input->getInt('showfilters', $params->get('show_filters')) === 1 ? 1 : 0;
		$this->filters = $this->get('Filters');
		$this->filterFormURL = $this->get('FilterFormURL');
		$tmplpath = JPATH_ROOT . '/plugins/fabrik_visualization/coverflow/views/coverflow/tmpl/' . $tpl;
		$this->_setPath('template', $tmplpath);
		$srcs[] = 'media/com_fabrik/js/listfilter.js';

		// Assign something to Fabrik.blocks to ensure we can clear filters
		$ref = $model->getJSRenderContext();
		$js = "$ref = {};";
		$js .= "\n" . "Fabrik.addBlock('$ref', $ref);";
		$js .= $model->getFilterJs();
		FabrikHelperHTML::addScriptDeclaration($srcs, $js);
		echo parent::display();
	}
}
