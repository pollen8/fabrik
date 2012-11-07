<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.approvals
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
* Approval HTML View
*
* @package		Joomla.Plugin
* @subpackage	Fabrik.visualization.slideshow
*/

class fabrikViewApprovals extends JViewLegacy
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
		$app = JFactory::getApplication();
		$input = $app->input;
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$id = $input->get('id', $usersConfig->get('visualizationid', $input->getInt('visualizationid', 0)));
		$model->setId($id);
		$this->id = $id;
		$this->row = $this->get('Visualization');
		$this->rows = $this->get('Rows');
		$this->containerId = $this->get('ContainerId');
		$this->calName = $this->get('VizName');
		$this->params = $model->getParams();
		$tpl = $this->params->get('approvals_layout', $tpl);
		$tmplpath = JPATH_SITE . '/plugins/fabrik_visualization/approvals/views/approvals/tmpl/' . $tpl;
		$this->_setPath('template', $tmplpath);

		$ab_css_file = $tmplpath . '/template.css';

		if (file_exists($ab_css_file))
		{
			JHTML::stylesheet('/plugins/fabrik_visualization/approvals/views/approvals/tmpl/' . $tpl . '/template.css');
		}

		$ref = $model->getJSRenderContext();
		$js = "var $ref = new fbVisApprovals('approvals_" . $id . "');\n";
		$js .= "Fabrik.addBlock('" . $ref . "', $ref);\n";
		$js .= $model->getFilterJs();

		$srcs = FabrikHelperHTML::framework();
		$srcs[] = 'media/com_fabrik/js/listfilter.js';
		$srcs[] = 'plugins/fabrik_visualization/approvals/approvals.js';
		FabrikHelperHTML::script($srcs, $js);

		$text = $this->loadTemplate();
		$opt = $input->get('option');
		$view = $input->get('view');
		$input->set('view', 'article');
		$input->set('option', 'com_content');
		jimport('joomla.html.html.content');
		$text .= '{emailcloak=off}';
		$text = JHTML::_('content.prepare', $text);
		$text = preg_replace('/\{emailcloak\=off\}/', '', $text);
		$input->set('option', $opt);
		echo $text;
	}

}
