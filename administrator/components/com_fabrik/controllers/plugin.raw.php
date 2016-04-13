<?php
/**
 * Raw Fabrik Plugin Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       1.6
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Worker;
use Fabrik\Helpers\StringHelper;

jimport('joomla.application.component.controller');

require_once 'fabcontrollerform.php';

/**
 * Raw Fabrik Plugin Controller
 *
 * @package  Fabrik
 * @since    3.0
 */
class FabrikAdminControllerPlugin extends FabControllerForm
{
	/**
	 * Means that any method in Fabrik 2, e.e. 'ajax_upload' should
	 * now be changed to 'onAjax_upload'
	 * ajax action called from element
	 *
	 * @return  void
	 */
	public function pluginAjax()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$plugin = $input->get('plugin', '');
		$method = $input->get('method', '');
		$group = $input->get('g', 'element');

		$dispatcher = JEventDispatcher::getInstance();

		if ($group === 'element')
		{
			// As elements are namespaced the default importPlugin wont work in registering the
			// plugin with the dispatcher
			$className = '\\Fabrik\\Plugins\\Element\\' . ucfirst($plugin);
			$plg = JPluginHelper::getPlugin($group, $plugin);
			new $className($dispatcher, (array) ($plg));
			$res = 1;
		}
		else
		{
			$res = JPluginHelper::importPlugin('fabrik_' . $group, $plugin);
		}

		if (!$res)
		{
			$o = new stdClass;
			$o->err = 'unable to import plugin fabrik_' . $group . ' ' . $plugin;
			echo json_encode($o);

			return;
		}

		if (substr($method, 0, 2) !== 'on')
		{
			$method = 'on' . StringHelper::ucfirst($method);
		}

		$dispatcher = JEventDispatcher::getInstance();
		$dispatcher->trigger($method);

		return;
	}

	/**
	 * Custom user ajax call
	 *
	 * @return  void
	 */
	public function userAjax()
	{
		$db = Worker::getDbo();
		require_once COM_FABRIK_FRONTEND . '/user_ajax.php';
		$app = JFactory::getApplication();
		$input = $app->input;
		$method = $input->get('method', '');
		$userAjax = new userAjax($db);

		if (method_exists($userAjax, $method))
		{
			$userAjax->$method();
		}
	}
}
