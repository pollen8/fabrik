<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class fabrikViewChart extends JView
{

	function display($tmpl = 'default')
	{
		FabrikHelperHTML::framework();
		FabrikHelperHTML::script('media/com_fabrik/js/list.js');
		FabrikHelperHTML::script('media/com_fabrik/js/advanced-search.js');
		require_once(COM_FABRIK_FRONTEND.DS.'helpers'.DS.'html.php');
		$model = $this->getModel();
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$model->setId(JRequest::getVar('id', $usersConfig->get('visualizationid', JRequest::getInt('visualizationid', 0))));
		$this->row = $model->getVisualization();
		$model->setListIds();

		if ($this->row->published == 0) {
			JError::raiseWarning(500, JText::_('JERROR_ALERTNOAUTHOR'));
			return '';
		}
		$calendar = $model->_row;
		$this->assign('requiredFiltersFound', $this->get('RequiredFiltersFound'));
		if ($this->requiredFiltersFound) {
			$this->assign('chart', $this->get('Chart'));
		} else {
			$this->assign('chart', '');
		}
		$viewName = $this->getName();
		$pluginManager = FabrikWorker::getPluginManager();
		$plugin = $pluginManager->getPlugIn('chart', 'visualization');
		$this->assign('containerId', $this->get('ContainerId'));
    $this->assignRef('filters', $this->get('Filters'));
    $this->assign('showFilters', JRequest::getInt('showfilters', 1) === 1 ?  1 : 0);
    $this->assign('filterFormURL', $this->get('FilterFormURL'));

		$pluginParams = $model->getPluginParams();
		$this->assignRef('params', $pluginParams);
		$tmpl = $pluginParams->get('chart_layout', $tmpl);
		$tmplpath = JPATH_ROOT.DS.'plugins'.DS.'fabrik_visualization'.DS.'chart'.DS.'views'.DS.'chart'.DS.'tmpl'.DS.$tmpl;
		$this->_setPath('template', $tmplpath);

		$ab_css_file = $tmplpath.DS."template.css";

		if (JFile::exists($ab_css_file))
		{
			JHTML::stylesheet('plugins/fabrik_visualization/chart/views/chart/tmpl/'.$tmpl.'/template.css', true);
		}

		//assign something to Fabrik.blocks to ensure we can clear filters
		$str = "head.ready(function() {
			fabrikChart{$this->row->id} = {};";
		$str .= "\n" . "Fabrik.addBlock('vizualization_{$this->row->id}', fabrikChart{$this->row->id});
		});";
		FabrikHelperHTML::addScriptDeclaration($str);
		echo parent::display();
	}

}
?>