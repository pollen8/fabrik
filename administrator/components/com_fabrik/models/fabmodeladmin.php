<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       1.6
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Abstract Fabrik Admin model
 *
 * @package  Fabrik
 * @since    3.0
 */

abstract class FabModelAdmin extends JModelAdmin
{

	/**
	 * get the list's active/selected plug-ins
	 *
	 * @return array
	 */

	public function getPlugins()
	{
		$item = $this->getItem();

		// Load up the active plug-ins
		$plugins = JArrayHelper::getValue($item->params, 'plugins', array());
		return $plugins;
	}

}
