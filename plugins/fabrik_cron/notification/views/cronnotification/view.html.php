<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class fabrikViewCronnotification extends JView
{

	function display( $tmpl = 'default')
	{
		$this->assignRef('rows', $this->get('UserNotifications'));

		$viewName = $this->getName();

		$tmplpath = JPATH_ROOT.DS.'fabrik_cron'.DS.'notification'.DS.'views'.DS.'cronnotification'.DS.'tmpl'.DS.$tmpl;
		$this->_setPath('template', $tmplpath);

		$ab_css_file = $tmplpath.DS."template.css";

		if (JFile::exists($ab_css_file)){
			JHTML::stylesheet('template.css', '/plugins/fabrik_cron/notification/views/cronnotification/tmpl/'.$tmpl.'/', true);
		}
		//ensure we don't have an incorrect version of mootools loaded
		FabrikHelperHTML::cleanMootools();
		echo parent::display();
	}

}
?>
