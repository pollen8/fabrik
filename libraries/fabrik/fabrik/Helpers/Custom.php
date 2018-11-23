<?php
/*
* Send sms's
*
* @package     Joomla
* @subpackage  Fabrik.helpers
* @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
* @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
*/

namespace Fabrik\Helpers;

// No direct access
defined('_JEXEC') or die('Restricted access');


/**
* Custom code
*
* @package     Joomla
* @subpackage  Fabrik.helpers
* @since       3.8
*/
class Custom
{
	private static $init = null;

	private static $config = null;

	private static $user = null;

	private static $app = null;

	private static $lang = null;

	private static $date = null;

	private static $session = null;

	private static $formModel = null;

	public function __constructNOT($config = array())
	{
		$this->config  = ArrayHelper::getValue($config, 'config', \JFactory::getConfig());
		$this->user    = ArrayHelper::getValue($config, 'user', \JFactory::getUser());
		$this->app     = ArrayHelper::getValue($config, 'app', \JFactory::getApplication());
		$this->lang    = ArrayHelper::getValue($config, 'lang', \JFactory::getLanguage());
		$this->date    = ArrayHelper::getValue($config, 'date', \JFactory::getDate());
		$this->session = ArrayHelper::getValue($config, 'session', \JFactory::getSession());
		$this->formModel = ArrayHelper::getValue($config, 'formModel', null);
	}

	public static function __initStatic($config = array())
	{
		if (!isset(self::$init))
		{
			self::$config  = ArrayHelper::getValue($config, 'config', \JFactory::getConfig());
			self::$user    = ArrayHelper::getValue($config, 'user', \JFactory::getUser());
			self::$app     = ArrayHelper::getValue($config, 'app', \JFactory::getApplication());
			self::$lang    = ArrayHelper::getValue($config, 'lang', \JFactory::getLanguage());
			self::$date    = ArrayHelper::getValue($config, 'date', \JFactory::getDate());
			self::$session = ArrayHelper::getValue($config, 'session', \JFactory::getSession());
			self::$init    = true;
		}
	}

	public static function doMyThing()
	{
		return true;
	}
}
