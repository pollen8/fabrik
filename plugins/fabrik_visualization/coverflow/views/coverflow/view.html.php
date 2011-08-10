<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class fabrikViewCoverflow extends JView
{

	function display($tmpl = 'default')
	{
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$model = $this->getModel();
		$id = JRequest::getVar('id', $usersConfig->get('visualizationid', JRequest::getInt('visualizationid', 0)));
		$model->setId($id);
		$row =& $model->getVisualization();
		$model->setListIds();
		if ($this->get('RequiredFiltersFound')) {
			$model->render();
		}
		$this->assign('containerId', $this->get('ContainerId'));
		$this->assignRef('row', $row);
		$this->assign('showFilters', JRequest::getInt('showfilters', 1) === 1 ?  1 : 0);
		$this->assignRef('filters', $this->get('Filters'));
		$this->assign('filterFormURL', $this->get('FilterFormURL'));
		$pluginParams =& $model->getPluginParams();
		$this->assignRef('params', $pluginParams);
		$tmplpath = JPATH_ROOT.DS.'plugins'.DS.'fabrik_visualization'.DS.'coverflow'.DS.'views'.DS.'coverflow'.DS.'tmpl'.DS.$tmpl;
		$this->_setPath('template', $tmplpath);
		FabrikHelperHTML::script('table.js', 'media/com_fabrik/js/', true);
		//ensure we don't have an incorrect version of mootools loaded
		JHTML::stylesheet('media/com_fabrik/css/table.css');
		FabrikHelperHTML::script('media/com_fabrik/js/list.js', true);
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