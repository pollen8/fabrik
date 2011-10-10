<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class fabrikViewGooglemap extends JView
{

	function display($tmpl = 'default')
	{
		FabrikHelperHTML::framework();
		FabrikHelperHTML::slimbox();
		$document = JFactory::getDocument();
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$model = $this->getModel();
		$model->setId(JRequest::getVar('id', $usersConfig->get('visualizationid', JRequest::getInt('visualizationid', 0) )));
		$this->row = $model->getVisualization();
		$model->setListIds();

		$js = $model->getJs();
		$this->txt = $model->getText();
		$this->params = $model->getParams();

		$params = $model->getPluginParams();
		$this->assignRef('params', $params);
		$tmpl = $params->get('fb_gm_layout', $tmpl);
		$tmplpath = JPATH_ROOT.DS.'plugins'.DS.'fabrik_visualization'.DS.'googlemap'.DS.'views'.DS.'googlemap'.DS.'tmpl'.DS.$tmpl;
		FabrikHelperHTML::script('media/com_fabrik/js/list.js');

		if ($params->get('fb_gm_center') == 'userslocation') {
			$document->addScript('http://code.google.com/apis/gears/gears_init.js');
			FabrikHelperHTML::script('components/com_fabrik/libs/geo-location/geo.js');
		}

		$this->get('PluginJsClasses');
		$tableplugins = "head.ready(function() {\n"
		.$this->get('PluginJsObjects')
		."\n});";
		FabrikHelperHTML::addScriptDeclaration($tableplugins);
		global $ispda;
		if ($ispda == 1) { //pdabot
		  $template = 'static';
		  $this->assign('staticmap', $this->get('StaticMap'));
		} else {
			$src = "http://maps.google.com/maps/api/js?sensor=".$params->get('fb_gm_sensor', 'false');
			$document->addScript($src);

			FabrikHelperHTML::script('plugins/fabrik_visualization/googlemap/googlemap.js');

			if ((int)$this->params->get('fb_gm_clustering', '0') == 1) {
				FabrikHelperHTML::script('components/com_fabrik/libs/googlemaps/markerclusterer/src/markerclusterer.js');
				//FabrikHelperHTML::script('http://google-maps-utility-library-v3.googlecode.com/svn/trunk/markerclusterer/src/markerclusterer_compiled.js');
				//FabrikHelperHTML::script('components/com_fabrik/libs/googlemaps/markermanager.js');

			} else {
				//doesnt work in v3
				//FabrikHelperHTML::script('components/com_fabrik/libs/googlemaps/markermanager.js');
			}

			FabrikHelperHTML::addScriptDeclaration($js);
			$ab_css_file = $tmplpath.DS.'template.css';

			if (JFile::exists($ab_css_file))
			{
				JHTML::stylesheet('plugins/fabrik_visualization/googlemap/views/googlemap/tmpl/'.$tmpl.'/template.css');
			}
			$template = null;
		}
		//check and add a general fabrik custom css file overrides template css and generic table css
		FabrikHelperHTML::stylesheetFromPath("media".DS."com_fabrik".DS."css".DS."custom.css");
		//check and add a specific viz template css file overrides template css generic table css and generic custom css
		FabrikHelperHTML::stylesheetFromPath("components".DS."com_fabrik".DS."plugins".DS."visualization".DS."googlemap".DS."views".DS."googlemap".DS."tmpl".DS.$tmpl.DS."custom.css");
		$this->assignRef('filters', $this->get('Filters'));
		$this->assign('showFilters', JRequest::getInt('showfilters', 1) === 1 ?  1 : 0);
		$this->assign('filterFormURL', $this->get('FilterFormURL'));
		$this->assign('sidebarPosition', $params->get('fb_gm_use_overlays_sidebar'));
		//if ((int)$params->get('fb_gm_use_overlays', 0) === 1 &&  (int)$params->get('fb_gm_use_overlays_sidebar', 0) > 0) {
		if ($this->get('ShowSideBar')) {
			$this->assign('showSidebar', 1);
			$this->assign('overlayUrls', $params->get('fb_gm_overlay_urls', array(), '_default', 'array'));
			$this->assign('overlayLabels', $params->get('fb_gm_overlay_labels', array(), '_default', 'array'));
		} else {
			$this->assign('showSidebar', 0);
		}

		$this->_setPath('template', $tmplpath);

		$this->assign('containerId', $this->get('ContainerId'));
		$this->assignRef('grouptemplates', $this->get('GroupTemplates'));

		echo parent::display($template);
	}

}
?>