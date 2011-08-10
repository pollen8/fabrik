<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class fabrikViewCalendar extends JView
{

	function display( $tmpl = 'default')
	{
		JHTML::_('behavior.calendar');
		require_once(COM_FABRIK_FRONTEND.DS.'helpers'.DS.'html.php');
		FabrikHelperHTML::mocha();
		FabrikHelperHTML::loadCalendar();
		$model		= &$this->getModel();
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$model->setId(JRequest::getVar('id', $usersConfig->get('visualizationid', JRequest::getInt('visualizationid', 0) )));
		echo $model->getEvents();
	}

}
?>
