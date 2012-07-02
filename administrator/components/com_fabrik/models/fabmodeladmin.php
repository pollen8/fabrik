<?php
/*
 * Form Model
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since		1.6
* @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
* @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');


abstract class FabModelAdmin extends JModelAdmin
{

	/**
	 * get the list's active/selected plug-ins
	 * @return array
	 */

	public function getPlugins()
	{
		$item = $this->getItem();
		// load up the active plug-ins
		$dispatcher = JDispatcher::getInstance();
		$plugins = JArrayHelper::getValue($item->params, 'plugins', array());
		$return = array();
		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');
		//@todo prob wont work for any other model that extends this class except for the form/list model
		switch (get_class($this))
		{
			case 'FabrikModelList':
				$class = 'list';
				break;
			default:
				$class = 'form';
		}
		$feModel = JModel::getInstance($class, 'FabrikFEModel');
		$feModel->setId($this->getState($class.'.id'));

		$state = isset($item->params['plugin_state']) ? $item->params['plugin_state'] : array();
		foreach ($plugins as $x => $plugin)
		{
			$o = $pluginManager->getPlugIn($plugin, $this->pluginType);
			if (!is_object($o))
			{
				JError::raiseNotice(500, 'Could not load ' . $plugin);
			}
			else
			{
				$o->getJForm()->model = $feModel;
				$data = (array) $item->params;
				$str = $o->onRenderAdminSettings($data, $x);
				$str = addslashes(str_replace(array("\n", "\r"), "", $str));
				$location = $this->getPluginLocation($x);
				$event = $this->getPluginEvent($x);
				$opts = new stdClass;
				$opts->state = (bool) (trim(JArrayHelper::getValue($state, $x)));
				$return[] = array('plugin' => $plugin, 'html' => $str, 'location' => $location, 'event' => $event, 'opts' => $opts);
			}
		}
		return $return;
	}
}