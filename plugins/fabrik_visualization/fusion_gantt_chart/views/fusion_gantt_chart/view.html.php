<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class fabrikViewFusion_gantt_chart extends JView
{

	function display( $tmpl = 'default')
	{
		FabrikHelperHTML::framework();
		FabrikHelperHTML::script('media/com_fabrik/js/list.js');
		FabrikHelperHTML::script('media/com_fabrik/js/advanced-search.js');
		$model = $this->getModel();
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$model->setId(JRequest::getVar('id', $usersConfig->get('visualizationid', JRequest::getInt('visualizationid', 0) )));
		$this->row = $model->getVisualization();
		$model->setListIds();

		if ($this->row->published == 0) {  
			JError::raiseWarning(500, JText::_('JERROR_ALERTNOAUTHOR'));
			return '';
		}
		$this->assign('requiredFiltersFound', $this->get('RequiredFiltersFound'));
		if ($this->requiredFiltersFound) {
			$this->assign('chart', $this->get('Chart'));
		}
		$viewName = $this->getName();
		$pluginManager = FabrikWorker::getPluginManager();
		$plugin = $pluginManager->getPlugIn('fusion_gantt_chart', 'visualization');
		$this->assign('containerId', $this->get('ContainerId'));
    $this->assignRef('filters', $this->get('Filters'));
    $this->assign('showFilters', JRequest::getInt('showfilters', 1));
		$pluginParams = $model->getPluginParams();
		$tmpl = $pluginParams->get('fusion_gantt_chart_layout', $tmpl);
		$tmplpath = JPATH_ROOT.DS.'plugins'.DS.'fabrik_visualization'.DS.'fusion_gantt_chart'.DS.'views'.DS.'fusion_gantt_chart'.DS.'tmpl'.DS.$tmpl;
		$this->_setPath('template', $tmplpath);

		$ab_css_file = $tmplpath.DS."template.css";

		if (JFile::exists($ab_css_file))
		{
			JHTML::stylesheet('template.css', 'plugins/fabrik_visualization/fusion_gantt_chart/views/fusion_gantt_chart/tmpl/'.$tmpl.'/', true);
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