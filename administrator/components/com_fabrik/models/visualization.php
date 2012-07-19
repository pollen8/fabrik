<?php
/*
 * Group Model
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

class FabrikModelVisualization extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_VISUALIZATION';

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */

	public function getTable($type = 'Visualization', $prefix = 'FabrikTable', $config = array())
	{
		$config['dbo'] = FabriKWorker::getDbo(true);
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
		$form = $this->loadForm('com_fabrik.visualization', 'visualization', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		$form->model = $this;
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
		$data = JFactory::getApplication()->getUserState('com_fabrik.edit.visualization.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
		}
		return $data;
	}

	/**
	 * get html form fields for a plugin (filled with
	 * current element's plugin data
	 * @param	string	$plugin
	 * @return	string	html form fields
	 */

	function getPluginHTML($plugin = null)
	{
		$item = $this->getItem();
		if (is_null($plugin))
		{
			$plugin = $item->plugin;
		}
		JRequest::setvar('view', 'visualization');
		JPluginHelper::importPlugin('fabrik_visualizaton', $plugin);
		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');
		if ($plugin == '')
		{
			$str = JText::_('COM_FABRIK_SELECT_A_PLUGIN');
		}
		else
		{
			$plugin = $pluginManager->getPlugIn($plugin, 'Visualization');
			$str = $plugin->onRenderAdminSettings(JArrayHelper::fromObject($item));
		}
		return $str;
	}

	/**
	 * (non-PHPdoc)
	 * @see JModelForm::validate()
	 */

	public function validate($form, $data, $group = null)
	{
		parent::validate($form, $data);
		return $data;
	}

	/**
	 * save the form
	 * @param array $data (the jform part of the request data)
	 */

	function save($data)
	{
		parent::cleanCache('com_fabrik');
		return parent::save($data);
	}

}
