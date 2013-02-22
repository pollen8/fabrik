<?php
/**
 * Fabrik nvd3_chart Chart HTML View
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.nvd3_chart
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * Fabrik nvd3_chart Chart HTML View
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.nvd3_chart
 */

class fabrikViewNvd3_chart extends JViewLegacy
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


		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		$input = $app->input;

		$model = $this->getModel();
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$model->setId($input->getInt('id', $usersConfig->get('visualizationid', $input->getInt('visualizationid', 0))));


		$srcs = FabrikHelperHTML::framework();

		FabrikHelperHTML::styleSheet('plugins/fabrik_visualization/nvd3_chart/lib/novus-nvd3/src/nv.d3.css');

		$srcs[] = 'media/com_fabrik/js/listfilter.js';
		$srcs[] = 'media/com_fabrik/js/advanced-search.js';

		$lib = COM_FABRIK_LIVESITE . 'plugins/fabrik_visualization/nvd3_chart/lib/novus-nvd3/';
		$document->addScript($lib . 'lib/d3.v2.js');
		$document->addScript($lib . 'nv.d3.js');
		$document->addScript($lib . 'src/tooltip.js');
		$document->addScript($lib . 'lib/fisheye.js');
		$document->addScript($lib . 'src/utils.js');
		$document->addScript($lib . 'src/models/legend.js');
		$document->addScript($lib . 'src/models/axis.js');
		$document->addScript($lib . 'src/models/scatter.js');
		$document->addScript($lib . 'src/models/line.js');
		$document->addScript($lib . 'src/models/lineChart.js');
		$document->addScript($lib . 'src/models/multiBar.js');
		$document->addScript($lib . 'src/models/multiBarChart.js');

		require_once COM_FABRIK_FRONTEND . '/helpers/html.php';

		$this->row = $model->getVisualization();
		if ($this->row->published == 0)
		{
			JError::raiseWarning(500, JText::_('JERROR_ALERTNOAUTHOR'));
			return '';
		}
		$this->assign('requiredFiltersFound', $this->get('RequiredFiltersFound'));

		$params = $model->getParams();
		$js = $model->js();
		$document->addScriptDeclaration($js);

		//$this->js($model);
		$this->assign('params', $params);
		$viewName = $this->getName();
		$pluginManager = JModelLegacy::getInstance('Pluginmanager', 'FabrikFEModel');
		$plugin = $pluginManager->getPlugIn('calendar', 'visualization');
		$this->params = $params;

		$this->assign('containerId', $this->get('ContainerId'));
		$this->assign('filters', $this->get('Filters'));
		$this->showFilters = $model->showFilters();
		$this->assign('filterFormURL', $this->get('FilterFormURL'));
		$tpl = $params->get('nvd3_chart_layout', $tpl);
		$this->_setPath('template', JPATH_ROOT . '/plugins/fabrik_visualization/nvd3_chart/views/nvd3_chart/tmpl/' . $tpl);

		FabrikHelperHTML::stylesheetFromPath(
			'plugins/fabrik_visualization/nvd3_chart/views/nvd3_chart/tmpl/' . $tpl . '/template.css');

		// Assign something to Fabrik.blocks to ensure we can clear filters
		$ref = $model->getJSRenderContext();
		$js = "$ref = {};";
		$js .= "\n" . "Fabrik.addBlock('$ref', $ref);";
		$js .= $model->getFilterJs();
		FabrikHelperHTML::script($srcs, $js);

		echo parent::display();
	}
}
