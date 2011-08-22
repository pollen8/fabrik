<?php
/**
 * @package		Joomla
 * @subpackage fabrik
 * @copyright	Copyright (C) 2005 - 2008 Pollen 8 Design Ltd. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die( 'Restricted access');

jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.file');

/**
 * Joomla! Fabrik system
 *
 * @author		Rob Clayburn <rob@pollen-8.co.uk>
 * @package		Joomla
 * @subpackage	fabrik
 */
class plgSystemFabrik extends JPlugin
{

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @access	protected
	 * @param	object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since	1.0
	 */

	function plgSystemFabrik(& $subject, $config)
	{
		parent::__construct($subject, $config);

	}

	/**
	* @since 3.0
	 * need to call this here otherwise you get class exists error
	 */

	function onAfterInitialise()
	{
		jimport('joomla.filesystem.file');
		$p = JPATH_SITE.DS.'plugins'.DS.'system'.DS.'fabrik'.DS;
		$defines = JFile::exists($p.'user_defines.php') ? $p.'user_defines.php' : $p.'defines.php';
		require_once($defines);
	}

}