<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class fabrikViewCronnotification extends JViewLegacy
{

	function display( $tmpl = 'default')
	{
		$this->rows = $this->get('UserNotifications');

		$viewName = $this->getName();

		$tmplpath = JPATH_ROOT . '/fabrik_cron/notification/views/cronnotification/tmpl/' . $tmpl;
		$this->_setPath('template', $tmplpath);

		$ab_css_file = $tmplpath . '/template.css';

		if (JFile::exists($ab_css_file))
		{
			JHTML::stylesheet('template.css', '/plugins/fabrik_cron/notification/views/cronnotification/tmpl/' . $tmpl . '/', true);
		}
		//ensure we don't have an incorrect version of mootools loaded
		FabrikHelperHTML::cleanMootools();
		echo parent::display();
	}

}
?>
