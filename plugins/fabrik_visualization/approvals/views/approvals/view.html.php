<?php
/**
 * Approval HTML View
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.approvals
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Html;

jimport('joomla.application.component.view');

/**
 * Approval HTML View
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.slideshow
 * @since       3.0.6
 */

class FabrikViewApprovals extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */

	public function display($tpl = 'default')
	{
		$model = $this->getModel();
		$j3 = FabrikWorker::j3();
		$app = JFactory::getApplication();
		$input = $app->input;
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$id = $input->get('id', $usersConfig->get('visualizationid', $input->getInt('visualizationid', 0)));
		$model->setId($id);

		if (!$model->canView())
		{
			echo FText::_('JERROR_ALERTNOAUTHOR');

			return false;
		}

		$this->id = $id;
		$this->row = $this->get('Visualization');
		$this->rows = $this->get('Rows');
		$this->containerId = $this->get('ContainerId');
		$this->calName = $this->get('VizName');
		$this->params = $model->getParams();
		$tpl = $j3 ? 'bootstrap' : $tpl;
		$this->_setPath('template', JPATH_SITE . '/plugins/fabrik_visualization/approvals/views/approvals/tmpl/' . $tpl);

		Html::stylesheetFromPath('plugins/fabrik_visualization/approvals/views/approvals/tmpl/' . $tpl . '/template.css');

		$ref = $model->getJSRenderContext();
		$js = "var $ref = new fbVisApprovals('approvals_" . $id . "');\n";
		$js .= "Fabrik.addBlock('" . $ref . "', $ref);\n";
		$js .= $model->getFilterJs();

		$srcs = Html::framework();
		$srcs['FbListFilter'] = 'media/com_fabrik/js/listfilter.js';
		$srcs['Approvals'] = 'plugins/fabrik_visualization/approvals/approvals.js';

		Html::iniRequireJs($model->getShim());
		Html::script($srcs, $js);

		$text = $this->loadTemplate();
		Html::runContentPlugins($text);
		echo $text;
	}
}
