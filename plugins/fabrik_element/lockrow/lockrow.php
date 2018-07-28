<?php
/**
* Plugin element to render internal id
* @package fabrikar
* @author Rob Clayburn
* @copyright (C) Rob Clayburn
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once JPATH_SITE . '/components/com_fabrik/models/element.php';

class PlgFabrik_ElementLockrow extends PlgFabrik_Element {

	/**
	 * Db table field type
	 *
	 * @var string
	 */
	protected $fieldDesc = 'VARCHAR(32)';

	public function isLocked($value)
	{
		if (!empty($value)) {
			list($time,$locking_user_id) = explode(';', $value);
			$this_user = JFactory::getUser();
			// $$$ decide what to do about guests
			$this_user_id = $this_user->get('id');
			if ((int)$this_user_id === (int)$locking_user_id)
			{
				return false;
			}
			$params = $this->getParams();
			$ttl = (int) $params->get('lockrow_ttl', '24');
			$ttl_time = (int) $time + ($ttl * 60);
			$time_now = time();
			if ($time_now < $ttl_time)
			{
				return true;
			}
		}
		return false;
	}

	public function showLocked($value, $this_user_id = null)
	{
		if (!empty($value)) {
			if (!isset($this_user_id))
			{
				$this_user = JFactory::getUser();
				$this_user_id = (int)$this_user->get('id');
			}
			else
			{
				$this_user_id = (int)$this_user_id;
			}
			list($time,$locking_user_id) = explode(';', $value);
			/*
			$this_user = JFactory::getUser();
			// $$$ decide what to do about guests
			$this_user_id = $this_user->get('id');
			if ((int)$this_user_id === (int)$locking_user_id)
			{
				return false;
			}
			*/
			$params = $this->getParams();
			$ttl = (int) $params->get('lockrow_ttl', '24');
			$ttl_time = (int) $time + ($ttl * 60);
			$time_now = time();
			if ($time_now < $ttl_time)
			{
				return true;
			}
		}
		return false;
	}

	private function canUnlock($value, $this_user_id = null)
	{
		$can_unlock = false;
		if (!empty($value))
		{
			if (!isset($this_user_id))
			{
				$this_user = JFactory::getUser();
				$this_user_id = (int)$this_user->get('id');
			}
			else
			{
				$this_user_id = (int)$this_user_id;
			}
			list($time,$locking_user_id) = explode(';', $value);
			$locking_user_id = (int)$locking_user_id;
			if ($this_user_id === $locking_user_id)
			{
				$can_unlock = true;
			}
		}
		return $can_unlock;
	}

	private function canLock($value, $this_user_id = null)
	{
		return false;
	}

	/**
	 * draws the form element
	 * @param int repeat group counter
	 * @return string returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		$name 		= $this->getHTMLName($repeatCounter);
		$id			= $this->getHTMLId($repeatCounter);
		$params 	= $this->getParams();
		$element 	= $this->getElement();
		$value 		= $this->getValue($data, $repeatCounter);

		$element->hidden = true;

		if (!$this->editable || !$this->canUse() || ($this->app->input->get('view', '', 'string') === 'details')) {
			return '';
		}

		$rowid = (int) $this->getFormModel()->getRowId();

		if (empty($rowid)) {
			return "";
		}

		$ttl_unlock = false;
		if ($value != 0) {
			list($time,$locking_user_id) = explode(';', $value);
			$this_user = JFactory::getUser();
			// $$$ decide what to do about guests
			$this_user_id = $this_user->get('id');
			if ((int)$this_user_id === (int)$locking_user_id)
			{
				return "";
			}
			$ttl = (int) $params->get('lockrow_ttl', '24');
			$ttl_time = (int) $time + ($ttl * 60);
			if (time() < $ttl_time)
			{
				$app = JFactory::getApplication();
				$app->enqueueMessage('ROW IS LOCKED!');
				return "";
			}
			else
			{
				$app = JFactory::getApplication();
				$app->enqueueMessage('ROW UNLOCKED!');
				$ttl_unlock = true;
			}
		}

		$db_table_name = $this->getTableName();
		$field_name = FabrikString::safeColName($this->getFullName(false, false));
		$listModel = $this->getListModel();
		$pk = $listModel->getTable()->db_primary_key;
		$db = $listModel->getDb();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();
		$user_id = $user->get('id');
		$lockstr = time() . ";" . $user_id;
		//$query = "UPDATE $db_table_name SET $field_name = " . $db->quote($lockstr) . " WHERE $pk = " . $db->quote($rowid);
		$query->update($db->quoteName($db_table_name))
			->set($field_name . ' = ' . $db->quote($lockstr))
			->where($pk . ' = ' . $db->quote($rowid));

		$db->setQuery($query);
		$db->execute();

		// $$$ @TODO - may need to clean com_content cache as well
		$cache = JFactory::getCache('com_fabrik');
		$cache->clean();

		return "";
	}

	/**
	 * shows the data formatted for the table view
	 * @param   string    $data
	 * @param   stdClass  &$thisRow  All the data in the lists current row
	 * @param   array     $opts      Rendering options
	 */
	function renderListData($data, &$thisRow, $opts = array())
	{
		if (!isset($data))
		{
			$data = '';
		}

		$data = FabrikWorker::JSONtoData($data, true);
		
		for ($i=0; $i <count($data); $i++) {
			$data[$i] = $this->_renderListData($data[$i], $thisRow, $opts);
		}
		
		$data = json_encode($data);
		
		return parent::renderListData($data, $thisRow, $opts);
	}

	function _renderListData($data, $thisRow, $opts)
	{
		$layout = $this->getLayout('list');
		$layoutData = new StdClass();
		$layoutData->tmpl = $this->tmpl;
		$imagepath = COM_FABRIK_LIVESITE.'/plugins/fabrik_element/lockrow/images/';
		if ($this->showLocked($data))
		{
			$layoutData->icon = 'lock';
			$layoutData->alt = 'Locked';
			$layoutData->class = 'fabrikElement_lockrow_locked';
		}
		else
		{
			$layoutData->icon = 'unlock';
			$layoutData->alt = 'Not Locked';
			$layoutData->class = 'fabrikElement_lockrow_unlocked';
		}

		//$str = "<img src='" . $imagepath . $icon . "' alt='" . $alt . "' class='fabrikElement_lockrow " . $class . "' />";
		return $layout->render($layoutData);
	}

	function storeDatabaseFormat($val, $data)
	{
		return '0';
	}

	/**
	 * defines the type of database table field that is created to store the element's data
	 */

	function getFieldDescription()
	{
		return "VARCHAR(32)";
	}

	/**
	 * return tehe javascript to create an instance of the class defined in formJavascriptClass
	 * @return string javascript to create instance. Instance name must be 'el'
	 */

	function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		return array('fbLockrow', $id, $opts);
	}

	function isHidden()
	{
		return true;
	}

	public function elementListJavascript()
	{
		$user = JFactory::getUser();

		$params = $this->getParams();
		$user = JFactory::getUser();
		$userid = $user->get('id');
		$id = $this->getHTMLId();
		$listModel = $this->getListModel();
		$list = $listModel->getTable();
		$formid = $list->form_id;
		$data = $listModel->getData();
		$gKeys = array_keys($data);
		$el_name = $this->getFullName(true, false);
		$el_name_raw = $el_name . '_raw';
		$row_locks = array();
		$can_unlocks = array();
		$can_locks = array();
		foreach ($gKeys as $gKey) {
			$groupedData = $data[$gKey];
			foreach ($groupedData as $rowkey) {
				$row_locks[$rowkey->__pk_val] = isset($rowkey->$el_name_raw) ? $this->showLocked($rowkey->$el_name_raw, $userid) : false;
				$can_unlocks[$rowkey->__pk_val] =  isset($rowkey->$el_name_raw) ? $this->canUnlock($rowkey->$el_name_raw, $userid) : false;
				$can_locks[$rowkey->__pk_val] = isset($rowkey->$el_name_raw) ? $this->canLock($rowkey->$el_name_raw, $userid) : false;
			}
		}
		$opts = new stdClass();

		$crypt = FabrikWorker::getCrypt('aes');
		$crypt_userid = $crypt->encrypt($userid);

		$opts->tableid      = $list->id;
		$opts->livesite     = COM_FABRIK_LIVESITE;
		$opts->imagepath    = COM_FABRIK_LIVESITE.'/plugins/fabrik_element/lockrow/images/';
		$opts->elid         = $this->getElement()->id;
		$opts->userid       = urlencode($crypt_userid);
		$opts->row_locks	= $row_locks;
		$opts->can_unlocks	= $can_unlocks;
		$opts->can_locks	= $can_locks;
		$opts->listRef      = $listModel->getRenderContext();
		$opts->formid        = $listModel->getFormModel()->getId();
		$opts->lockIcon     = FabrikHelperHTML::icon("icon-lock", '', '', true);
		$opts->unlockIcon   = FabrikHelperHTML::icon("icon-unlock", '', '', true);
		$opts->keyIcon      = FabrikHelperHTML::icon("icon-key", '', '', true);
		$opts               = json_encode($opts);
		return "new FbLockrowList('$id', $opts);\n";
	}

	function onAjax_unlock()
	{
		$input = $this->app->input;
		$this->setId($input->getInt('element_id'));
		$this->loadMeForAjax();

		$crypt = FabrikWorker::getCrypt('aes');

		$listModel = $this->getListModel();
		$list      = $listModel->getTable();
		$listId    = $list->id;
		$formId    = $listModel->getFormModel()->getId();
		$rowid = $this->app->input->get('row_id', '', 'string');
		$userid = $this->app->input->get('userid', '', 'string');

		$db_table_name = $this->getTableName();
		$field_name = FabrikString::safeColName($this->getFullName(false, false));
		$listModel = $this->getListModel();
		$pk = $listModel->getTable()->db_primary_key;
		$db = $listModel->getDb();
		$query = $db->getQuery(true);
		//$this_user = JFactory::getUser();
		//$this_user_id = $this_user->get('id');
		$this_user_id = $crypt->decrypt(urldecode($userid));

		//$query = "SELECT $field_name FROM $db_table_name WHERE $pk = " . $db->quote($rowid);
		$query->select($field_name)
			->from($db->quoteName($db_table_name))
			->where($pk . ' = ' . $db->quote($rowid));
		$db->setQuery($query);
		$value = $db->loadResult();

		$ret['status'] = 'unlocked';
		$ret['msg'] = 'Row unlocked';
		if (!empty($value))
		{
			if ($this->canUnlock($value, $this_user_id))
			{
				//$query = "UPDATE $db_table_name SET $field_name = 0 WHERE $pk = " . $db->quote($rowid);
				$query->clear()
					->update($db->quoteName($db_table_name))
					->set($field_name . ' = "0"')
					->where($pk . ' = ' . $db->quote($rowid));
				$db->setQuery($query);
				$db->execute();

				// $$$ @TODO - may need to clean com_content cache as well
				$cache = JFactory::getCache('com_fabrik');
				$cache->clean();
			}
			else
			{
				$ret['status'] = 'locked';
				$ret['msg'] = 'Row was not unlocked!';
			}
		}
		echo json_encode($ret);
	}

}
?>
