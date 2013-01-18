<?php
/**
 * Raw Fabrik Plugin Controller
 *
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * Raw Fabrik Plugin Controller
 *
 * Used in admin
 * @package		Joomla
 * @subpackage	Fabrik
 * @since 1.5
 */

class FabrikControllerPlugin extends JController
{
	/**
	 * Means that any method in Fabrik 2, e.e. 'ajax_upload' should
	 * now be changed to 'onAjax_upload'
	 * ajax action called from element
	 *
	 * @return  void
	 */

	function pluginAjax()
	{
		$plugin = JRequest::getVar('plugin', '');
		$method = JRequest::getVar('method', '');
		$group = JRequest::getVar('g', 'element');
		if (!JPluginHelper::importPlugin('fabrik_' . $group, $plugin))
		{
			$o = new stdClass;
			$o->err = 'unable to import plugin fabrik_' . $group . ' ' . $plugin;
			echo json_encode($o);
			return;
		}
		$dispatcher = JDispatcher::getInstance();
		if (substr($method, 0, 2) !== 'on')
		{
			$method = 'on' . JString::ucfirst($method);
		}
		$dispatcher->trigger($method);
		return;
	}

	/**
	 * Custom user ajax call
	 *
	 * @return  void
	 */

	function userAjax()
	{
		$db = FabrikWorker::getDbo();
		require_once COM_FABRIK_FRONTEND . '/user_ajax.php';
		$method = JRequest::getVar('method', '');
		$userAjax = new userAjax($db);
		if (method_exists($userAjax, $method))
		{
			$userAjax->$method();
		}
	}
}
