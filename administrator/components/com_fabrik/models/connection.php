<?php
/*
 * Connection Model
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


class FabrikModelConnection extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_CONNECTION';


	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'Connection', $prefix = 'FabrikTable', $config = array())
	{
		// not sure if we should be loading JTable or FabTable here
		// issue with using Fabtable is that it will always load the cached verion of the cnn
		// which might cause issues when migrating from test to live sites???
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
		$form = $this->loadForm('com_fabrik.connection', 'connection', array('control' => 'jform', 'load_data' => $loadData));
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
		$data = JFactory::getApplication()->getUserState('com_fabrik.edit.connection.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}


	/**
	 * Method to set the default item
	 *
	 * @param	int	id of connection to set as default
	 * @param	int		The value of the home state.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */

	function setDefault($id)
	{
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->update('#__fabrik_connections')->set($db->nameQuote('default')." = 0");
		$db->setQuery($query);
		$db->query();
		$query->clear();
		$query->update('#__fabrik_connections')->set($db->nameQuote('default')." = 1")->where("id = ".(int)$id);
		$db->setQuery($query);
		$db->query();
		return true;
	}

	/**
	 * save the connection- test first if its vald
	 * if it is remove the session instance of the connection then call parent save
	 * @param array $data
	 */

	function save($data)
	{

		$session = JFactory::getSession();
		//JModel::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models');
		$model = JModel::getInstance('Connection', 'FabrikFEModel');
		$model->setId($data['id']);

		$options = $model->getConnectionOptions(JArrayHelper::toObject($data));
		$db = JDatabase::getInstance($options);

		if (JError::isError($db)) {
			$this->setError(JText::_('COM_FABRIK_UNABLE_TO_CONNECT'));
			return false;
		}
		$key = 'fabrik.connection.'.$data['id'];
		// erm yeah will remove the session connection for the admin user, but not any other user whose already using the site
		// would need to clear out the session table i think - but that would then log out all users.
		$session->clear($key);
		return parent::save($data);
	}
	
	/**
	* Validate the form
	* @param object $form
	* @param array $data
	*/
	
	public function validate($form, $data)
	{
		if ($data['password'] !== $data['passwordConf']) {
			$this->setError(JText::_('COM_FABRIK_PASSWORD_MISMATCH'));
			return false;
		}
		return parent::validate($form, $data);
	}
}
