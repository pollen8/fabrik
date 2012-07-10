<?php
/**
 * @package		Joomla.Plugin
 * @subpackage	Fabrik.visualization.calendar
 * @copyright	Copyright (C) 2005 Fabrik. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
* Fabrik Calendar Raw View
*
* @package		Joomla.Plugin
* @subpackage	Fabrik.visualization.calendar
*/

class fabrikViewCalendar extends JView
{

	function display($tmpl = 'default')
	{
		$model = $this->getModel();
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$model->setId(JRequest::getVar('id', $usersConfig->get('visualizationid', JRequest::getInt('visualizationid', 0))));
		echo $model->getEvents();
	}

}
