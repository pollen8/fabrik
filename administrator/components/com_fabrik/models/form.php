<?php
/*
 * Admin Form Model
 *
 * @package Joomla.Administrator
 * @subpackage Fabrik
 * @since	1.6
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access.
defined('_JEXEC') or die;

require_once('fabmodeladmin.php');

class FabrikModelForm extends FabModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_FORM';

	protected $abstractPlugins = null;

	protected $pluginType = 'Form';


	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */

	public function getTable($type = 'Form', $prefix = 'FabrikTable', $config = array())
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
		$form = $this->loadForm('com_fabrik.form', 'form', array('control' => 'jform', 'load_data' => $loadData));
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
		$data = JFactory::getApplication()->getUserState('com_fabrik.edit.form.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
		}
		return $data;
	}

	/**
	 * get the possible list plug-ins that can be selected for use
	 * @return array
	 */

	public function getAbstractPlugins()
	{
		if (isset($this->abstractPlugins))
		{
			return $this->abstractPlugins;
		}
		// create a new dispatcher so that we only collect admin html for validation rules
		$pluginDispatcher = new JDispatcher();
		//import the validation plugins and assign them to their custom dispatcher
		JPluginHelper::importPlugin('fabrik_form', null, true, $pluginDispatcher);
		$rules = array();
		//trigger the validation dispatcher to get hte validation rules html
		$plugins = JPluginHelper::getPlugin('fabrik_form');
		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');
		$feFormModel = JModel::getInstance('form', 'FabrikFEModel');
		$feFormModel->setId($this->getState('form.id'));
		foreach ($plugins as $x => $plugin)
		{
			$data = array();
			$o = $pluginManager->getPlugIn($plugin->name, 'Form');
			if ($o !== false)
			{
				$o->getJForm()->model = $feFormModel;
				// $$$ rob 0 was $x below but that rendered first set of plugins with indexes 1,2,3
				// think they should all be indexed 0
				$str = $o->onRenderAdminSettings($data, 0);
				$js = $o->onGetAdminJs($plugin->name, $plugin->name, $str);
				$str = addslashes(str_replace(array("\n", "\r"), "", $str));
				$attr = "class=\"inputbox elementtype\"";
				$rules[$plugin->name] = array('plugin'=>$plugin->name, 'html'=>$str, 'js'=>$js);
			}
		}
		asort($rules);
		$this->abstractPlugins = $rules;
		return $rules;
	}

	public function getJs()
	{
		$abstractPlugins = $this->getAbstractPlugins();
		$plugins = $this->getPlugins();
		$item = $this->getItem();

		JText::script('COM_FABRIK_ACTION');
		JText::script('COM_FABRIK_SELECT_DO');
		JText::script('COM_FABRIK_DELETE');
		JText::script('COM_FABRIK_IN');
		JText::script('COM_FABRIK_ON');
		JText::script('COM_FABRIK_OPTIONS');
		JText::script('COM_FABRIK_PLEASE_SELECT');
		JText::script('COM_FABRIK_FRONT_END');
		JText::script('COM_FABRIK_BACK_END');
		JText::script('COM_FABRIK_BOTH');
		JText::script('COM_FABRIK_NEW');
		JText::script('COM_FABRIK_EDIT');
		JText::script('COM_FABRIK_PUBLISHED');
		JText::script('JNO');
		JText::script('JYES');

		$js =
	"
  head.ready(function() {\n";
		$js .= "\t\tvar aPlugins = [];\n";
		foreach ($abstractPlugins as $abstractPlugin)
		{
			$js .= "\t\taPlugins.push(".$abstractPlugin['js'].");\n";
		}
		$js .= "controller = new fabrikAdminForm(aPlugins);\n";
		foreach ($plugins as $plugin)
		{
			$opts = array_key_exists('opts', $plugin) ? $plugin['opts'] : new stdClass();
			$opts->location = @$plugin['location'];
			$opts->event = @$plugin['event'];
			$opts = json_encode($opts);
			$js .= "controller.addAction('".$plugin['html']."', '".$plugin['plugin']."', ".$opts.", false);\n";
		}
		$js .= "
});";
		return $js;
	}


	protected function getPluginLocation($repeatCounter)
	{
		$item = $this->getItem();
		return $item->params['plugin_locations'][$repeatCounter];
	}

	protected function getPluginEvent($repeatCounter)
	{
		$item = $this->getItem();
		return $item->params['plugin_events'][$repeatCounter];
	}

	/**
	 * save the form
	 * @param array $data
	 */

	function save($data)
	{
		$post = JRequest::get('post');
		$data['params']['plugins'] = (array)JArrayHelper::getValue($post['jform'], 'plugin');
		$data['params']['plugin_locations'] = (array)JArrayHelper::getValue($post['jform'], 'plugin_locations');
		$data['params']['plugin_events'] = (array)JArrayHelper::getValue($post['jform'], 'plugin_events');

		// move back into the main data array some values we are rendering as
		// params (did that for ease of rendering admin output)

		$opts = array('reset_button_label', 'submit_button_label');
		foreach ($opts as $opt)
		{
			$data[$opt] = $data['params'][$opt];
		}
		$tmpName = JArrayHelper::getValue($data, 'db_table_name');
		unset($data['db_table_name']);
		$return = parent::save($data);
		if ($return)
		{
			$data['db_table_name'] = $tmpName;
			$this->saveFormGroups($data);
		}
		return $return;
	}

	/**
	 * After having saved the form we
	 * 1) Create a new group if none selected in edit form list
	 * 2) Delete all old form_group records
	 * 3) Recreate the form group records
	 * 4) Make a table view if needed
	 * @return bol true if you should display the form list, false if you're
	 * redirected elsewhere
	 */

	public function saveFormGroups($data)
	{
		//these are set in parent::save() and contain the updated form id and if the form is a new form
		$formid = $this->getState($this->getName() . '.id');
		$isnew = $this->getState($this->getName() . '.new');
		$db = FabrikWorker::getDbo(true);
		$currentGroups = (array)JArrayHelper::getValue($data, 'current_groups');
		$record_in_database = $data['record_in_database'];
		$createGroup = $data['_createGroup'];
		$form = $this->getForm();
		$fields = array('id' => 'internalid', 'date_time' => 'date');;
		//if new and record in db and group selected then we want to get those groups elements to create fields for in the db table
		if ($isnew && $record_in_database)
		{
			$groups = JArrayHelper::getValue($data, 'current_groups');
			if (!empty($groups))
			{
				$query = $db->getQuery(true);
				$query->select('plugin, name')->from('#__fabrik_elements')->where('group_id IN ('.implode(',', $groups).')');
				$db->setQuery($query);
				$rows = $db->loadObjectList();
				foreach ($rows as $row)
				{
					$fields[$row->name] = $row->plugin;
				}
			}
		}

		if ($createGroup)
		{
			$group = FabTable::getInstance('Group', 'FabrikTable');
			$group->name = $data['label'];
			$group->published = 1;
			$group->store();
			$currentGroups[] = $db->insertid();
		}
		$this->_makeFormGroups($data, $currentGroups);
		if ($record_in_database == '1')
		{
			$listModel = JModel::getInstance('List', 'FabrikModel');
			$item = $listModel->loadFromFormId($formid);
			if ($isnew)
			{
				$dbTableName = $data['db_table_name'] !== '' ? $data['db_table_name'] : $data['label'];
				// mysql will force db table names to lower case even if you set the db name to upper case - so use clean()
				$dbTableName = FabrikString::clean($dbTableName);
			}
			else
			{
				$dbTableName = $item->db_table_name == '' ? $data['database_name'] : $item->db_table_name;
			}
			$dbTableExists = $listModel->databaseTableExists($dbTableName);
			if (!$dbTableExists)
			{
				// @TODO - need to sanitize table name (get rid of non alphanumeirc or _),
				// just not sure whether to do it here, or above (before we test for existinance)
				// $$$ hugh - for now, just do it here, after we test for the 'unsanitized', as
				// need to do some more testing on MySQL table name case sensitivity
				// BUT ... as we're potentially changing the table name after testing for existance
				// we need to test again.
				//$$$ rob - was replacing with '_' but if your form name was 'x - y' then this was converted to x___y which then blows up element name code due to '___' being presumed to be the element splitter.
				$dbTableName = preg_replace('#[^0-9a-zA-Z_]#', '', $dbTableName);
				if ($listModel->databaseTableExists($dbTableName))
				{
					return JError::raiseWarning(500, JText::_("COM_FABRIK_DB_TABLE_ALREADY_EXISTS"));
				}
				$listModel->set('form.id', $formid);
				$listModel->createDBTable($dbTableName, $fields);
			}
			if (!$dbTableExists || $isnew)
			{
				$connection = FabrikWorker::getConnection(-1);
				$item->id = null;
				$item->label = $data['label'];
				$item->form_id = $formid;
				$item->connection_id = $connection->getConnection()->id;
				$item->db_table_name = $dbTableName;
				// store key without nameQuotes as thats db specific *which we no longer want
				$item->db_primary_key = $dbTableName . '.id';
				$item->auto_inc = 1;
				$item->published = $data['published'];
				$item->created = $data['created'];
				$item->created_by = $data['created_by'];
				$item->access = 1;
				$item->params = $listModel->getDefaultParams();
				$res = $item->store();
			}
			else
			{
				//update existing table (seems to need to reload here to ensure that _table is set
				$item = $listModel->loadFromFormId($formid);
				$listModel->ammendTable();
				// $$$ rob no longer in front end model?
				//$listModel->makeSafeTableColumns();
			}
		}
	}

	/**
	 * reinsert the groups ids into formgroup rows
	 * @param array jform post data
	 * @param array $currentGroups
	 */

	protected function _makeFormGroups($data, $currentGroups)
	{
		$formid = $this->getState($this->getName() . '.id');
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->delete('#__{package}_formgroup')->where('form_id = ' . (int)$formid);
		$db->setQuery($query);
		// delete the old form groups
		if (!$db->query())
		{
			JError::raiseError(500, $db->stderr());
		}
		$orderid = 1;
		$currentGroups = array_unique($currentGroups);
		foreach ($currentGroups as $group_id)
		{
			if ($group_id != '')
			{
				$group_id = (int) $group_id;
				$query = $db->getQuery(true);
				$query->insert('#__{package}_formgroup')->set(array('form_id =' . (int)$formid, 'group_id = ' . $group_id, 'ordering = ' . $orderid));
				$db->setQuery($query);
				if (!$db->query())
				{
					JError::raiseError(500, $db->stderr());
				}
				$orderid ++;
			}
		}
	}

	/**
	 * take an array of list ids and return the corresponding form_id's
	 * used in list publish code
	 * @param array list ids
	 * @return array form ids
	 */

	public function swapListToFormIds($ids = array())
	{
		if (empty($ids))
		{
			return array();
		}
		JArrayHelper::toInteger($ids);
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->select('form_id')->from('#__{package}_lists')->where('id IN (' . implode(',', $ids) . ')');
		return $db->setQuery($query)->loadColumn();
	}

	/**
	 * iterate over the form's elements and update its db table to match
	 */

	public function updateDatabase()
	{
		$cid = JRequest::getVar('cid', null, 'post', 'array');
		$formId = $cid[0];
		$model = JModel::getInstance('Form', 'FabrikFEModel');
		$model->setId($formId);
		$form = $model->getForm();
		//use this in case there is not table view linked to the form
		if ($form->record_in_database == 1)
		{
			//there is a table view linked to the form so lets load it
			$listModel = JModel::getInstance('List', 'FabrikModel');
			$listModel->loadFromFormId($formId);
			//$listModel->set('form.id', $formId);
			$listModel->setFormModel($model);
			$dbExisits = $listModel->databaseTableExists();
			if (!$dbExisits)
			{
				$listModel->createDBTable();
			}
			else
			{
				$listModel->ammendTable();
			}
		}
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
		if (!$ok)
		{
			return false;
		}
		//hack - must be able to add the plugin xml fields file to $form to include in validation
		// but cant see how at the moment
		$data['params'] = $params;
		return $data;
	}

	/**
	 *  delete form and form groups
	 * @param array $cids to delete
	 */
	
	public function delete($cids)
	{
		$res = parent::delete($cids);
		if ($res)
		{
			foreach ($cids as $cid)
			{
				$item = FabTable::getInstance('FormGroup', 'FabrikTable');
				$item->load(array('form_id'=> $cid));
				$item->delete();
			}
		}
		return $res;
	}

}
?>