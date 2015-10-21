<?php
/**
 * Abstract Fabrik Admin model
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       1.6
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modeladmin');

/**
 * Abstract Fabrik Admin model
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       3.0
 */
abstract class FabModelAdmin extends JModelAdmin
{
	/**
	 * @var JApplicationCMS
	 */
	protected $app;

	/**
	 * @var JUser
	 */
	protected $user;

	/**
	 * @var Registry
	 */
	protected $config;

	/**
	 * @var JSession
	 */
	protected $session;

	/**
	 * @var JDatabaseDriver
	 */
	protected $db;

	/**
	 * @var FabrikFEModelPluginmanager
	 */
	protected $pluginManager;

	/**
	 * Component name
	 *
	 * @var  string
	 */
	protected $option = 'com_fabrik';

	/**
	 * Constructor.
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 *
	 * @see     JModelLegacy
	 * @since   12.2
	 */
	public function __construct($config = array())
	{
		$this->app     = JArrayHelper::getValue($config, 'app', JFactory::getApplication());
		$this->user    = JArrayHelper::getValue($config, 'user', JFactory::getUser());
		$this->config  = JArrayHelper::getValue($config, 'config', JFactory::getConfig());
		$this->session = JArrayHelper::getValue($config, 'session', JFactory::getSession());
		$this->db      = JArrayHelper::getValue($config, 'db', JFactory::getDbo());
		$this->pluginManager = JArrayHelper::getValue($config, 'pluginManager',  JModelLegacy::getInstance('Pluginmanager', 'FabrikFEModel'));
		parent::__construct($config);
	}

	/**
	 * Get the list's active/selected plug-ins
	 *
	 * @return array
	 */
	public function getPlugins()
	{
		$item = $this->getItem();

		// Load up the active plug-ins
		$plugins = FArrayHelper::getValue($item->params, 'plugins', array());

		return $plugins;
	}
}
