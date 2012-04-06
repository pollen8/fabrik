<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class fabrikViewSlideshow extends JView
{

	function display($tmpl = 'default')
	{
		FabrikHelperHTML::framework();
		$model= $this->getModel();
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$model->setId(JRequest::getVar('id', $usersConfig->get('visualizationid', JRequest::getInt('visualizationid', 0))));
		$this->row = $model->getVisualization();
		$model->setListIds();
		if ($this->row->published == 0)
		{
			JError::raiseWarning(500, JText::_('JERROR_ALERTNOAUTHOR'));
			return '';
		}
		$this->assign('js', $this->get('JS'));
		$viewName = $this->getName();
		$params = $model->getParams();
		$this->assign('params', $params);
		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');
		$plugin = $pluginManager->getPlugIn('slideshow', 'visualization');
		$this->assign('showFilters', JRequest::getInt('showfilters', $params->get('show_filters')) === 1 ?  1 : 0);
		$this->assignRef('filters', $this->get('Filters'));
		$this->assign('filterFormURL', $this->get('FilterFormURL'));
		$pluginParams = $model->getPluginParams();
		$this->assignRef('params', $model->getParams());
		$tmpl = $pluginParams->get('slideshow_viz_layout', $tmpl);
		$tmplpath = $model->pathBase.'slideshow/views/slideshow/tmpl'.DS.$tmpl;
		$this->_setPath('template', $tmplpath);
		FabrikHelperHTML::script('media/com_fabrik/js/list.js');
		if ($this->get('RequiredFiltersFound'))
		{
			FabrikHelperHTML::script('components/com_fabrik/libs/slideshow2/js/slideshow.js');
			$slideshow_viz_type = $pluginParams->get('slideshow_viz_type', 1);
			switch ($slideshow_viz_type)
			{
				case 1:
					break;
				case 2:
					FabrikHelperHTML::script('components/com_fabrik/libs/slideshow2/js/slideshow.kenburns.js');
					break;
				case 3:
					FabrikHelperHTML::script('components/com_fabrik/libs/slideshow2/js/slideshow.push.js');
					break;
				case 4:
					FabrikHelperHTML::script('components/com_fabrik/libs/slideshow2/js/slideshow.fold.js');
					break;
				default:
					break;
			}

			JHTML::stylesheet('components/com_fabrik/libs/slideshow2/css/slideshow.css');
			FabrikHelperHTML::script('plugins/fabrik_visualization/slideshow/slideshow.js');
		}
		FabrikHelperHTML::addScriptDeclaration($this->js);

		$tmplpath = JPATH_ROOT . '/plugins/fabrik_visualization/slideshow/views/slideshow/tmpl/' . $tmpl;
		$this->_setPath('template', $tmplpath);
		FabrikHelperHTML::stylesheetFromPath('plugins/fabrik_visualization/slideshow/views/slideshow/tmpl/' . $tmpl . '/template.css');
		echo parent::display();
	}

}
?>