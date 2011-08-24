<?php
/*
 * Cron Model
 *
 * @package Joomla.Administrator
 * @subpackage Fabrik
 * @since		1.6
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');


class FabrikModelCron extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_CRON';


	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */

	public function getTable($type = 'Cron', $prefix = 'FabrikTable', $config = array())
	{
		$config['dbo'] = FabriKWorker::getDbo();
		return FabTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */

	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_fabrik.cron', 'cron', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */

	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_fabrik.edit.cron.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * get html form fields for a plugin (filled with
	 * current element's plugin data
	 * @param string $plugin
	 * @return string html form fields
	 */

	function getPluginHTML($plugin = null)
	{
		$item = $this->getItem();
		if (is_null($plugin)) {

			$plugin = $item->plugin;
		}
		JPluginHelper::importPlugin('fabrik_cron');

		//JModel::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models');
		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');

		if ($plugin == '') {
			$str = JText::_('COM_FABRIK_SELECT_A_PLUGIN');
		} else {
			$plugin = $pluginManager->getPlugIn($plugin, 'Cron');
			$str = $plugin->onRenderAdminSettings(JArrayHelper::fromObject($item));
		}
		return $str;
	}

	/**
	 * save the cron job - merging plugin parameters
	 * (non-PHPdoc)
	 * @see JModelAdmin::save()
	 */

	public function save($data)
	{
		$data['params'] = json_encode($data['params']);
		return  parent::save($data);
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param	object		$form		The form to validate against.
	 * @param	array		$data		The data to validate.
	 * @return	mixed		Array of filtered data if valid, false otherwise.
	 * @since	1.1
	 */
	function validate($form, $data)
	{
		$params = $data['params'];
		$ok = parent::validate($form, $data);
		//standard jform validation failed so we shouldn't test further as we can't
		//be sure of the data
		if (!$ok) {
			return false;
		}
		//hack - must be able to add the plugin xml fields file to $form to include in validation
		// but cant see how at the moment
		$data['params'] = $params;
		return $data;
	}

}
