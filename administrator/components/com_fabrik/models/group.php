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

require_once('fabmodeladmin.php');

class FabrikModelGroup extends FabModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_GROUP';

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */

	public function getTable($type = 'Group', $prefix = 'FabrikTable', $config = array())
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
		$form = $this->loadForm('com_fabrik.group', 'group', array('control' => 'jform', 'load_data' => $loadData));
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
		$data = JFactory::getApplication()->getUserState('com_fabrik.edit.group.data', array());
		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * take an array of forms ids and return the corresponding group ids
	 * used in list publish code
	 * @param array form ids
	 * @return array group ids
	 */

	public function swapFormToGroupIds($ids = array())
	{
		if (empty($ids)) {
			return array();
		}
		JArrayHelper::toInteger($ids);
		$db = FabrikWorker::getDbo();
		$query = $db->getQuery(true);
		$query->select('group_id')->from('#__{package}_formgroup')->where('form_id IN ('. implode(',', $ids).')');
		$db->setQuery($query);
		$res = $db->loadResultArray();
		return $res;
	}
	
	protected function checkRepeatAndPK($data)
	{
		$groupModel = JModel::getInstance('Group', 'FabrikFEModel');
		$groupModel->setId($data['id']);
		$listModel = $groupModel->getListModel();
		$pk = $listModel->getTable()->db_primary_key;
		$elementModels = $groupModel->getMyElements();
		foreach ($elementModels as $elementModel) {
			if(FabrikString::safeColName($elementModel->getFullName(false, false, false)) == $pk) {
				return false;
			}
		}
		return true;
	}

	/**
	 * (non-PHPdoc)
	 * @see JModelAdmin::save()
	 */

	public function save($data)
	{
		if ($data['id'] == 0) {
			$user = JFactory::getUser();
			$data['created_by'] = $user->get('id');
			$data['created_by_alias'] = $user->get('username');
			$data['created'] = JFactory::getDate()->toMySQL();

		}
		if ($this->checkRepeatAndPK($data)) {
		
			$makeJoin = ($data['params']['repeat_group_button'] == 1);
			if ($makeJoin) {
				$data['is_join'] = 1;
			}
		} else {
			if (($data['params']['repeat_group_button'] == 1)) {
				$data['params']['repeat_group_button'] = 0;
				JError::raiseNotice(500, 'You can not set the group containing the list primary key to be repeatableeee');
			}
		}
		$data['params'] = json_encode($data['params']);
		$return = parent::save($data);
		
		$data['id'] = $this->getState($this->getName().'.id');
		if ($return) {
			$this->makeFormGroup($data);
			if ($makeJoin) {
				// $$$ rob added this check as otherwise toggling group from repeat 
				// to norepeat back to repeat incorrectly created a 2nd join 
				if (!$this->joinedGroupExists($data['id'])) {
					$return = $this->makeJoinedGroup($data);
				}
				//update for the is_join change
				if ($return) {
					$return = parent::save($data);
				}
			} else {
				//$data['is_join'] =  0; // NO! none repeat joined groups were getting unset here - not right!
				$return = parent::save($data);
			}
		}
		return $return;
	}
	
	/**
	 * check if a group id has an associated join already created
	 * @param int group id
	 * @return boolean
	 */
	
	protected function joinedGroupExists($id)
	{
		$item = FabTable::getInstance('Group', 'FabrikTable');
		$item->load($id);
		return $item->join_id == '' ? false : true;
	}

	/**
	 * clears old form group entries if found and adds new ones
	 * @param array $data
	 */

	protected function makeFormGroup($data)
	{
		if ($data['form'] == '') {
			return;
		}
		$formid = (int)$data['form'];
		$id = (int)$data['id'];
		$item = FabTable::getInstance('FormGroup', 'FabrikTable');
		$item->load(array('form_id' => $formid, 'group_id' => $id));
		if ($item->id == '') {
			//get max group order
			$db = FabrikWorker::getDbo(true);
			$query = $db->getQuery(true);
			$query->select('MAX(ordering)')->from('#__{package}_formgroup')->where('form_id = '.$formid);
			$db->setQuery($query);
			$next = (int)$db->loadResult() + 1;
			$item->ordering = $next;
			$item->form_id = $formid;
			$item->group_id = $id;
			$item->store();
		}
	}

	/**
	 * a group has been set to be repeatable but is not part of a join
	 * so we want to:
	 * Create a new db table for the groups elements ( + check if its not already there)
	 *
	 * @param unknown_type $data
	 */

	public function makeJoinedGroup(&$data)
	{
		$groupModel = JModel::getInstance('Group', 'FabrikFEModel');
		$groupModel->setId($data['id']);
		$listModel = $groupModel->getListModel();
		$pluginManager = FabrikWorker::getPluginManager();
		$db = $listModel->getDb();
		$list = $listModel->getTable();
		$elements = (array)$groupModel->getMyElements();
		$names = array();
		$fields = $listModel->getDBFields(null, 'Field');
		$names['id'] = "id INT( 6 ) NOT NULL AUTO_INCREMENT PRIMARY KEY";
		$names['parent_id'] = "parent_id INT(6)";
		foreach ($elements as $element) {
			$fname = $element->getElement()->name;
			// if we are making a repeat group from the primary group then we dont want to
			// overwrite the repeat group tables id definition with that of the main tables
			if (!array_key_exists($fname, $names)) {
				$str = FabrikString::safeColName($fname);
				$field = JArrayHelper::getValue($fields, $fname);
				if (is_object($field)) {
					$str .= " ".$field->Type." ";
					if ($field->Null == 'NO') {
						$str .= "NOT NULL ";
					}
					$names[$fname] = $str;
				} else {
					$names[$fname] = $db->nameQuote($fname).' '.$element->getFieldDescription();
				}
			}

		}
		$db->setQuery("show tables");
		$newTableName = $list->db_table_name.'_'.$data['id'].'_repeat';
		$existingTables = $db->loadResultArray();
		if (!in_array($newTableName, $existingTables)) {
			// no existing repeat group table found so lets create it
			$query = "CREATE TABLE IF NOT EXISTS ".$db->nameQuote($newTableName)." (".implode(",", $names).")";
			$db->setQuery($query);
			if (!$db->query()) {
				JError::raiseError(500, $db->getErrorMsg());
			}
			//create id and parent_id elements
			$listModel->makeIdElement($data['id']);
			$listModel->makeFkElement($data['id']);

			
		} else {
			if (trim($list->db_table_name) == '') {
				//new group not attached to a form
				$this->setError(JText::_('COM_FABRIK_GROUP_CANT_MAKE_JOIN_NO_DB_TABLE'));
				return false;
			}
			//repeat table already created - lets check its structure matches the group elements
			$db->setQuery("DESCRIBE ".$db->nameQuote($newTableName));
			$existingFields = $db->loadObjectList('Field');
			$newFields = array_diff(array_keys($names), array_keys($existingFields));
			if (!empty($newFields)) {
				$lastfield = array_pop($existingFields);
				$lastfield = $lastfield->Field;
				foreach ($newFields as $newField) {
					$info = $names[$newField];
					$db->setQuery("ALTER TABLE ".$db->nameQuote($newTableName)." ADD COLUMN $info AFTER $lastfield");
					if (!$db->query()) {
						JError::raiseError(500, $db->getErrorMsg());
					}
				}
			}
		}
		// create the join as well
		//create fabrik join (was prevously only when creating new db table, but that gave issue when you
		//toggled on/off the group repeat property)
		
		$jdata = array('list_id' => $list->id,
					'element_id' => 0,
					'join_from_table' => $list->db_table_name,
					'table_join' => $newTableName,
					'table_key' => FabrikString::shortColName($list->db_primary_key),
					'table_join_key' => 'parent_id',
					'join_type' => 'left',
					'group_id' => $data['id']
		);
		//load the matching join if found.
		$join = $this->getTable('join');
		$join->load($jdata);
		
		$opts = new stdClass();
		$opts->type = 'group';
		$jdata['params'] = json_encode($opts);
		$join->bind($jdata);
		//update or save a new join
		$join->store();
		$data['is_join'] =  1;
		return true;
	}

	public function delete(&$pks, $deleteElements = false)
	{
		if (empty($pks)) {
			return true;
		}
		if (parent::delete($pks)) {
			if ($this->deleteFormGroups($pks)) {
				if ($deleteElements) {
					return $this->deleteElements($pks);
				} else {
					return true;
				}
			}
		}
		return false;
	}

	public function deleteElements($pks)
	{
		$db = FabrikWorker::getDbo(true);
		JArrayHelper::toInteger($pks);
		$query = $db->getQuery(true);
		$query->select('id')->from('#__{package}_elements')->where('group_id IN ('.implode(',', $pks).')');
		$db->setQuery($query);
		$elids = $db->loadResultArray();
		$elementModel = JModel::getInstance('Element', 'FabrikModel');
		return $elementModel->delete($elids);
	}

	public function deleteFormGroups($pks)
	{
		$db = FabrikWorker::getDbo(true);
		JArrayHelper::toInteger($pks);
		$query = $db->getQuery(true);
		$query->delete('#__{package}_formgroup')->where('group_id IN ('.implode(',', $pks).')');
		$db->setQuery($query);
		return $db->query();
	}

}
