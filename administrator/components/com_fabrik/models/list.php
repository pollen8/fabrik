<?php
/*
 * List Model
*
* @package Joomla.Administrator
* @subpackage Fabrik
* @since		1.6
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// No direct access.
defined('_JEXEC') or die;

require_once('fabmodeladmin.php');

class FabrikModelList extends FabModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */

	protected $text_prefix = 'COM_FABRIK_LIST';

	protected $formModel = null;

	/** @var object model - front end table model */
	protected $feListModel = null;

	/** @var object currently loaded list row */
	protected $tables = array();

	/** @var string */
	protected $pluginType = 'List';

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */

	public function getTable($type = 'List', $prefix = 'FabrikTable', $config = array())
	{
		$sig = $type.$prefix.implode('.', $config);
		if (!array_key_exists($sig, $this->tables)) {
			$config['dbo'] = FabriKWorker::getDbo(true);
			$this->tables[$sig] = FabTable::getInstance($type, $prefix, $config);
		}
		return $this->tables[$sig];
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
		$form = $this->loadForm('com_fabrik.list', 'list', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		$form->model = $this;
		return $form;
	}

	/**
	 * Method to get the confirm list delete form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */

	public function getConfirmDeleteForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_fabrik.confirmdelete', 'confirmlistdelete', array('control' => 'jform', 'load_data' => $loadData));
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
		$data = JFactory::getApplication()->getUserState('com_fabrik.edit.list.data', array());
		if (empty($data)) {
			$data = $this->getItem();
		}
		return $data;
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param	array	A list of the primary keys to change.
	 * @param	int		The value of the published state.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */

	function publish(&$pks, $value = 1)
	{
		// Initialise variables.
		$dispatcher	= JDispatcher::getInstance();
		$user	= JFactory::getUser();
		$table = $this->getTable();
		$pks = (array) $pks;

		// Include the content plugins for the change of state event.
		JPluginHelper::importPlugin('content');

		// Access checks.
		foreach ($pks as $i => $pk) {
			if ($table->load($pk)) {
				if (!$this->canEditState($table)) {
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDIT_STATE_NOT_PERMITTED'));
				}
			}
		}

		// Attempt to change the state of the records.
		if (!$table->publish($pks, $value, $user->get('id'))) {
			$this->setError($table->getError());
			return false;
		}

		$context = $this->option.'.'.$this->name;

		// Trigger the onContentChangeState event.
		$result = $dispatcher->trigger($this->event_change_state, array($context, $pks, $value));
		if (in_array(false, $result, true)) {
			$this->setError($table->getError());
			return false;
		}
		return true;
	}

	/**
	 * @param bol add slashes to reutrn data
	 * @return string dropdown
	 */

	protected function getFilterJoinDd($addslashes = true, $name = 'join')
	{
		$aConditions = array();
		$aConditions[] = JHTML::_('select.option', 'AND');
		$aConditions[] = JHTML::_('select.option', 'OR');
		$dd = str_replace("\n", "", JHTML::_('select.genericlist', $aConditions, $name, "class=\"inputbox\"  size=\"1\" ", 'value', 'text', ''));
		if ($addslashes) {
			$dd = addslashes($dd);
		}
		return $dd;
	}

	/**
	 *@param bol add slashes to reutrn data
	 *@param string name of the drop down
	 *@param int mode - states what values get put into drop down
	 *@return string dropdown
	 */

	protected function getFilterConditionDd($addslashes = true, $name = 'conditions', $mode = 1)
	{
		$aConditions = array();
		switch ($mode) {
			case 1:
				/* used for search filter */
				$aConditions[] = JHTML::_('select.option', '<>', 'NOT EQUALS');
				$aConditions[] = JHTML::_('select.option', '=', 'EQUALS');
				$aConditions[] = JHTML::_('select.option', 'like', 'BEGINS WITH');
				$aConditions[] = JHTML::_('select.option', 'like', 'CONTAINS');
				$aConditions[] = JHTML::_('select.option', 'like', 'ENDS WITH');
				$aConditions[] = JHTML::_('select.option', '>', 'GREATER THAN');
				$aConditions[] = JHTML::_('select.option', '>=', 'GREATER THAN OR EQUALS');
				$aConditions[] = JHTML::_('select.option', '<', 'LESS THAN');
				$aConditions[] = JHTML::_('select.option', '<=', 'LESS THAN OR EQUALS');
				break;
			case 2:
				/* used for prefilter */
				$aConditions[] = JHTML::_('select.option', 'equals', 'EQUALS');
				$aConditions[] = JHTML::_('select.option', 'notequals', 'NOT EQUAL TO');
				$aConditions[] = JHTML::_('select.option', 'begins', 'BEGINS WITH');
				$aConditions[] = JHTML::_('select.option', 'contains', 'CONTAINS');
				$aConditions[] = JHTML::_('select.option', 'ends', 'ENDS WITH');
				$aConditions[] = JHTML::_('select.option', '>', 'GREATER THAN');
				$aConditions[] = JHTML::_('select.option', '>=', 'GREATER THAN OR EQUALS');
				$aConditions[] = JHTML::_('select.option', '<', 'LESS THAN');
				$aConditions[] = JHTML::_('select.option', 'IS NULL', 'IS NULL');
				$aConditions[] = JHTML::_('select.option', '<=', 'LESS THAN OR EQUALS');
				$aConditions[] = JHTML::_('select.option', 'in', 'IN');
				$aConditions[] = JHTML::_('select.option', 'not_in', 'NOT IN');
				$aConditions[] = JHTML::_('select.option', 'earlierthisyear', JText::_('COM_FABRIK_EARLIER_THIS_YEAR'));
				$aConditions[] = JHTML::_('select.option', 'laterthisyear', JText::_('COM_FABRIK_LATER_THIS_YEAR'));
				break;
		}
		$dd = str_replace("\n", "", JHTML::_('select.genericlist',  $aConditions, $name, "class=\"inputbox\"  size=\"1\" ", 'value', 'text', ''));
		if ($addslashes) {
			$dd = addslashes( $dd);
		}
		return $dd;
	}

	protected function getCnn()
	{
		$item = $this->getItem();
		//JModel::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models');
		$connModel 	=& JModel::getInstance('Connection', 'FabrikFEModel');
		$connModel->setId($item->connection_id);
		$connModel->getConnection($item->connection_id);
		return $connModel;
	}

	/**
	 * create the js that manages the edit list view page
	 */

	public function getJs()
	{
		$abstractPlugins = $this->getAbstractPlugins();
		$connModel = $this->getCnn();
		$plugins = $this->getPlugins();
		$item = $this->getItem();
		//JModel::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models');
		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');
		JText::script('COM_FABRIK_ACTION');
		JText::script('COM_FABRIK_DO');
		JText::script('COM_FABRIK_IN');
		JText::script('COM_FABRIK_ON');
		JText::script('COM_FABRIK_OPTIONS');
		JText::script('COM_FABRIK_JOIN');
		JText::script('COM_FABRIK_FIELD');
		JText::script('COM_FABRIK_CONDITION');
		JText::script('COM_FABRIK_VALUE');
		JText::script('COM_FABRIK_EVAL');
		JText::script('COM_FABRIK_APPLY_FILTER_TO');
		JText::script('COM_FABRIK_DELETE');
		JText::script('JYES');
		JText::script('JNO');
		JText::script('COM_FABRIK_QUERY');
		JTEXT::script('COM_FABRIK_NO_QUOTES');
		JText::script('COM_FABRIK_TEXT');
		JText::script('COM_FABRIK_TYPE');
		JText::script('COM_FABRIK_PLEASE_SELECT');
		JText::script('COM_FABRIK_GROUPED');
		JText::script('COM_FABRIK_TO');
		JText::script('COM_FABRIK_FROM');
		JText::script('COM_FABRIK_JOIN_TYPE');
		JText::script('COM_FABRIK_FROM_COLUMN');
		JText::script('COM_FABRIK_TO_COLUMN');
		JText::script('COM_FABRIK_REPEAT_GROUP_BUTTON_LABEL');

		$joinTypeOpts = array();
		$joinTypeOpts[] = array('inner', JText::_('INNER JOIN'));
		$joinTypeOpts[] = array('left', JText::_('LEFT JOIN'));
		$joinTypeOpts[] = array('right', JText::_('RIGHT JOIN'));
		$activetableOpts[] = "";
		$activetableOpts[] = $item->db_table_name;

		$joins = $this->getJoins();

		for ($i = 0; $i < count($joins); $i ++) {
			$j = $joins[$i];
			$activetableOpts[] = $j->table_join;
			$activetableOpts[] = $j->join_from_table;
		}
		$activetableOpts = array_unique($activetableOpts);
		$activetableOpts = array_values($activetableOpts);
		$opts = new stdClass();
		$opts->joinOpts = $joinTypeOpts;
		$opts->tableOpts = $connModel->getThisTables(true);
		$opts->activetableOpts = $activetableOpts;
		$opts = json_encode($opts);

		$filterOpts = new stdClass();
		$filterOpts->filterJoinDd = $this->getFilterJoinDd(false, 'jform[params][filter-join][]');
		$filterOpts->filterCondDd = $this->getFilterConditionDd(false, 'jform[params][filter-conditions][]', 2);
		$filterOpts->filterAccess 	= JHtml::_('access.level', 'jform[params][filter-access][]', $item->access);
		$filterOpts->filterAccess = str_replace(array("\n", "\r"), '', $filterOpts->filterAccess);
		$filterOpts = json_encode($filterOpts);

		$formModel =& $this->getFormModel();
		$filterfields = $formModel->getElementList('jform[params][filter-fields][]', '', false, false, true);
		$filterfields = addslashes(str_replace(array("\n", "\r"), '', $filterfields));
		$js =
	"
  head.ready(function() {

		oAdminTable = new ListForm($opts);
	oAdminTable.watchJoins();\n";
		for ($i = 0; $i < count($joins); $i ++) {
			$joinGroupParams = json_decode($joins[$i]->params);
			$j = $joins[$i];
			$joinFormFields = json_encode($j->joinFormFields);
			$joinToFields =  json_encode($j->joinToFields);
			$repeat = $joinGroupParams->repeat_group_button == 1 ? 1 :0;
			$js .= "	oAdminTable.addJoin('{$j->group_id}','{$j->id}','{$j->join_type}','{$j->table_join}',";
			$js .= "'{$j->table_key}','{$j->table_join_key}','{$j->join_from_table}', $joinFormFields, $joinToFields, $repeat);\n";
		}
		$js .= "var aPlugins = [];\n";
		foreach ($abstractPlugins as $abstractPlugin) {
			$js .= "aPlugins.push(".$abstractPlugin['js'].");\n";
		}
		$js .= "controller = new ListPluginManager(aPlugins);\n";
		foreach ($plugins as $plugin) {
			$js .= "controller.addAction('".$plugin['html']."', '".$plugin['plugin']."', '".@$plugin['location']."', '".@$plugin['event']."', false);\n";
		}

		$js .= "oAdminFilters = new adminFilters('filterContainer', '$filterfields', $filterOpts);\n";

		$form = $this->getForm();

		$afilterJoins	= $form->getValue('params.filter-join');

		$afilterFields = $form->getValue('params.filter-fields');
		$afilterConditions	= $form->getValue('params.filter-conditions');
		$afilterEval = $form->getValue('params.filter-eval');
		$afilterValues= $form->getValue('params.filter-value');
		$afilterAccess= $form->getValue('params.filter-access');
		$aGrouped = $form->getValue('params.filter-grouped');
		for ($i=0; $i < count($afilterFields); $i++) {
			$selJoin = JArrayHelper::getValue($afilterJoins, $i, 'and');
			$selFilter = $afilterFields[$i];
			$grouped = $aGrouped[$i];
			$selCondition = $afilterConditions[$i];
			$filerEval = JArrayHelper::getValue($afilterEval, $i, '1');
			if ($selCondition == '&gt;') {
				$selCondition = '>';
			}
			if ($selCondition == '&lt;') {
				$selCondition = '<';
			}
			$selValue = JArrayHelper::getValue($afilterValues, $i, '');
			$selAccess = $afilterAccess[$i];

			//alow for multiline js variables ?
			$selValue = htmlspecialchars_decode($selValue, ENT_QUOTES);
			$selValue = json_encode($selValue);

			if ($selFilter != '') {
				$js .= "	oAdminFilters.addFilterOption('$selJoin', '$selFilter', '$selCondition', $selValue, '$selAccess', '$filerEval', '$grouped');\n";
			}
		}
		$js .= "
});";
		return $js;
	}

	/**
	 * get the table's join objects
	 * @return array
	 */

	protected function getJoins()
	{
		$item = $this->getItem();
		if ((int)$item->id === 0) {
			return array();
		}
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->select('*, j.id AS id')->from('#__{package}_joins AS j')->join('INNER', '#__{package}_groups AS g ON g.id = j.group_id')->where('j.list_id = '.(int)$item->id);
		$db->setQuery($query);
		$joins = $db->loadObjectList();
		$fabrikDb = $this->getFEModel()->getDb();
		for ($i = 0; $i < count($joins); $i++) {
			$join =& $joins[$i];
			$fields = $fabrikDb->getTableFields(array($join->join_from_table, $join->table_join));
			$join->joinFormFields = array_keys($fields[$join->join_from_table]);
			$join->joinToFields = array_keys($fields[$join->table_join]);
		}
		return $joins;
	}

	/**
	 * get the possible list plug-ins that can be selected for use
	 * @return array
	 */

	public function getAbstractPlugins()
	{
		// create a new dispatcher so that we only collect admin html for validation rules
		$pluginDispatcher = new JDispatcher();

		//import the plugins and assign them to their custom dispatcher
		JPluginHelper::importPlugin('fabrik_list', null, true, $pluginDispatcher);
		$rules = array();
		//trigger the dispatcher to get the plug-in rules html

		$plugins = JPluginHelper::getPlugin('fabrik_list');

		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');

		$feListModel = JModel::getInstance('List', 'FabrikFEModel');
		$feListModel->setId($this->getState('list.id'));

		foreach ($plugins as $x => $plugin) {

			$data = array();

			$o = $pluginManager->getPlugIn($plugin->name, 'List');
			$o->getJForm()->model = $feListModel;

			// $$$ rob 0 was $x below but that rendered first set of plugins with indexes 1,2,3
			// think they should all be indexed 0
			$str = $o->onRenderAdminSettings($data, 0);
			$js = $o->onGetAdminJs($plugin->name, $plugin->name, $str);
			$str = addslashes(str_replace(array("\n", "\r"), "", $str));
			$rules[] = array('plugin'=>$plugin->name, 'html'=>$str, 'js'=>$js);
		}

		return $rules;
	}

	protected function getPluginLocation($repeatCounter)
	{
		return '';
	}

	protected function getPluginEvent($repeatCounter)
	{
		return '';
	}
	/**
	 * load up a front end form model - used in saving the table
	 * @return object front end form model
	 */

	public function getFormModel()
	{
		if (is_null($this->formModel)) {
			$config = array();
			$config['dbo'] = FabrikWorker::getDbo(true);
			$this->formModel = JModel::getInstance('Form', 'FabrikFEModel', $config);
			$this->formModel->setDbo($config['dbo']);

			// $$$ rob commenting out as this loads up an empty form when saving a new list
			//$item = $this->getItem();
			//$this->formModel->setId($this->getState('list.form_id', $item->id));
			//$this->formModel->getForm();

			// $$$ rob isnt this wrong as normally the front end form models list model is the fe list model?
			//$this->formModel->setListModel($this);
		}
		return $this->formModel;
	}

	/**
	 * load up the front end list model so we can use some of its methods
	 * @return object front end list model
	 */

	public function getFEModel()
	{
		if (is_null($this->feListModel)) {
			$this->feListModel = JModel::getInstance('List', 'FabrikFEModel');
			$this->feListModel->setState('list.id', $this->getState('list.id'));
		}
		return $this->feListModel;
	}

	/**
	 * Validate the form
	 * @param object $form
	 * @param array $data
	 */

	public function validate($form, $data)
	{
		$params = $data['params'];
		$data = parent::validate($form, $data);
		if (!$data) {
			return false;
		}
		if (empty($data['_database_name']) && JArrayHelper::getValue($data, 'db_table_name') == '') {
			$this->setError(JText::_('COM_FABRIK_SELECT_DB_OR_ENTER_NAME'));
			return false;
		}
		//hack - must be able to add the plugin xml fields file to $form to include in validation
		// but cant see how at the moment
		$data['params'] = $params;
		return $data;
	}

	/**
	 * save the form
	 * @param array $data (the jform part of the request data)
	 */

	function save($data)
	{
		$this->populateState();
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$config = JFactory::getConfig();
		$date = JFactory::getDate();
		$row = $this->getTable();
		$id = $data['id'];
		$row->load($id);

		$this->setState('list.id', $id);
		$this->setState('list.form_id', $row->form_id);
		$feModel = $this->getFEModel();
		$formModel = $this->getFormModel();

		if (!$row->bind($data)) {
			$this->setError($row->getError());
			return false;
		}

		$filter	= new JFilterInput(null, null, 1, 1);
		//$introduction = JRequest::getVar('introduction', '', 'post', 'string', JREQUEST_ALLOWRAW);
		$introduction = JArrayHelper::getValue(JRequest::getVar('jform', array(), 'post', 'array', JREQUEST_ALLOWRAW), 'introduction');

		$row->introduction = $filter->clean($introduction);

		$row->order_by = json_encode(JRequest::getVar('order_by', array(), 'post', 'array'));
		$row->order_dir = json_encode(JRequest::getVar('order_dir', array(), 'post', 'array'));

		if (!$row->check()) {
			$this->setError($row->getError());
			return false;
		}
		$isNew = true;
		if ($id == 0) {
			if ($row->created == '') {
				$row->created = $date->toMySQL();
			}
			//save the row now
			$row->store();

			$isNew = false;
			$newtable = trim(JArrayHelper::getValue($data, '_database_name'));
			// $$$ hugh - added some more sanity checking on table name, get rid of non-alphanumeric and _
			// @TODO - should prolly use a helper for this, like FabrikString::clean()
			// but need to think about case issues first
			$newtable = preg_replace('#[^0-9a-zA-Z_]#', '_', $newtable);
			//check the entered database table doesnt already exist
			if ($newtable != '' && $this->databaseTableExists($newtable)) {
				$this->setError(JText::_('COM_FABRIK_DATABASE_TABLE_ALREADY_EXISTS'));
				return false;
			}

			if (!$this->canCreateDbTable()) {
				$this->setError(Jtext::_('COM_FABRIK_INSUFFICIENT_RIGHTS_TO_CREATE_TABLE'));
				return false;
			}
			//create fabrik form
			$formModel = $this->createLinkedForm();

			$row->form_id = $this->getState('list.form_id');
			//create fabrik group
			$groupData = array("name" => $row->label, "label" => $row->label);

			JRequest::setVar('_createGroup', 1, 'post');

			$groupId = $this->createLinkedGroup($groupData, false);

			if ($newtable == '') {
				//new fabrik list but existing db table
				$this->createLinkedElements($groupId);
			} else {
				$row->db_table_name = $newtable;
				$row->auto_inc = 1;
				$res = $this->createDBTable($newtable, JRequest::getVar('defaultfields', array('id' => 'internalid', 'date_time' => 'date')));
				if (is_array($res)) {
					$row->db_primary_key = $newtable.'.'.$res[0];
				}
			}
		}

		// 	save params - this file no longer exists? do we use models/table.xml instead??
		$params = new fabrikParams($row->params, JPATH_COMPONENT.DS.'xml'.DS.'table.xml');

		if ($row->id != 0) {
			$datenow = JFactory::getDate();
			$row->modified = $datenow->toMySQL();
			$row->modified_by = $user->get('id');
		}
		FabrikHelper::prepareSaveDate($row->publish_down);
		FabrikHelper::prepareSaveDate($row->created);
		FabrikHelper::prepareSaveDate($row->publish_up);
		$pk = JArrayHelper::getValue($data, 'db_primary_key');
		if ($pk == '') {
			$fields = $row->getFields();
			$key = $row->getKeyName();
			// $$$ rob erm ??? isnt $key the id for jos_fabrik_lists?
			//store without namequotes as thats db specific
			$row->db_primary_key = $row->db_primary_key == '' ? $row->db_table_name.".".$key : $row->db_primary_key;
			$row->auto_inc = stristr($fields[$key]->Extra, 'auto_increment') ? true : false;
		}
		if (!$row->store()) {
			$this->setError($row->getError());
			return false;
		}
		$pk = $row->db_primary_key;
		$this->updateJoins($data);
		$feModel->setTable($row); //needed to ensure pk field is not quoted
		if (!$feModel->isView()) {
			// this was only run on a new table - but I've put it here so that if you upload a new table you can ensure that its columns are fixed
			//$this->makeSafeTableColumns();
			$this->updatePrimaryKey($row->db_primary_key, $row->auto_inc);
		}
		//make an array of elments and a presumed index size
		//map is then used in creating indexes
		$map = array();
		$groups = $this->getFormModel()->getGroupsHiarachy();
		foreach ($groups as $groupModel) {
			$elementModels = $groupModel->getMyElements();
			foreach ($elementModels as $element) {
				//int elements cant have a index size attrib
				// $$$ hugh neither can DATETIME
				$coltype = $element->getFieldDescription();
				if (JString::stristr($coltype, 'int')) {
					$size = '';
				}
				else if (JString::stristr($coltype, 'datetime')) {
					$size = '';
				}
				else {
					$size = '10';
				}
				$map[$element->getFullName(false, false, false)] = $size;
				$map[$element->getElement()->id] = $size;
			}
		}
		//update indexes (added array_key_exists check as these may be during after CSV import)
		if (!empty($aOrderBy) && array_key_exists($row->order_by, $map)) {
			foreach ($aOrderBy as $orderBy) {
				if (array_key_exists($orderBy, $map)) {
					$feModel->addIndex($orderBy, 'tableorder', 'INDEX', $map[$orderBy]);
				}
			}
		}
		if ($row->group_by !== '' && array_key_exists($row->group_by, $map)) {
			$feModel->addIndex($row->group_by, 'groupby', 'INDEX', $map["$row->group_by"]);
		}
		if ($params->get('group_by_order') !== '') {
			$feModel->addIndex($params->get('group_by_order'), 'groupbyorder', 'INDEX', $map[$params->get('group_by_order')]);
		}
		$afilterFields = $params->get('filter-fields', '', '_default', 'array');
		foreach ($afilterFields as $field) {
			if (array_key_exists($field, $map)) {
				$feModel->addIndex($field, 'prefilter', 'INDEX', $map[$field]);
			}
		}
		$this->updateElements($row);
		/* $$$rob - joomfish not available for j1.7
		 if (JFolder::exists(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomfish'.DS.'contentelements')) {
		if ($params->get('allow-data-translation')) {
		if (!$this->makeJoomfishXML()) {
		$this->setError(JTEXT::_( "Unable to make Joomfish XML file"));
		return false;
		}
		} else {
		$this->removeJoomfishXML();
		}
		} */
		$pkName = $row->getKeyName();
		if (isset($row->$pkName)) {
			$this->setState($this->getName().'.id', $row->$pkName);
		}
		$this->setState($this->getName().'.new', $isNew);
		return true;
	}

	/**
	 * the list view now enables us to alter en-mass some element properties
	 * @param unknown_type $row
	 */
	protected function updateElements($row)
	{
		$params = json_decode($row->params);
		$searchElements = json_decode($params->list_search_elements[0])->search_elements;
		$elementModels = $this->getFEModel()->getElements(0, false, false);
		foreach ($elementModels as $elementModel) {
			$element = $elementModel->getElement();
			$elParams = $elementModel->getParams();
			$s = (in_array($element->id, $searchElements)) ? 1 : 0;
			$elParams->set('inc_in_search_all', $s);
			$element->params = (string)$elParams;
			$element->store();
		}
	}

	/**
	 * check to see if a table exists
	 * @param string name of table (ovewrites form_id val to test)
	 * @return boolean false if no table found true if table found
	 */

	function databaseTableExists($tableName = null)
	{
		if ($tableName === '') {
			return false;
		}
		$table = $this->getTable();
		if (is_null($tableName)) {
			$tableName = $table->db_table_name;
		}
		$fabrikDatabase = $this->getDb();
		$sql = "SHOW TABLES LIKE ".$fabrikDatabase->Quote($tableName);
		$fabrikDatabase->setQuery($sql);
		$total = $fabrikDatabase->loadResult();
		echo $fabrikDatabase->getError();
		return ($total == "") ? false : true;
	}

	/**
	 * deals with ensuring joins are managed correctly when table is saved
	 * @param array data
	 */

	private function updateJoins($data)
	{
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		// if we are creating a new list then don't update any joins - can result in groups and elements being removed.
		if ((int)$this->getState('list.id') === 0) {
			return;
		}

		$query->select('*')->from('#__{package}_joins')->where('list_id = '.(int)$this->getState('list.id'));
		$db->setQuery($query);
		$aOldJoins = $db->loadObjectList();
		$params = $data['params'];
		$aOldJoinsToKeep 	= array();
		$joinModel			= JModel::getInstance('Join', 'FabrikFEModel');
		$joinIds 				= JArrayHelper::getValue($params, 'join_id', array());
		$joinTypes 			= JArrayHelper::getValue($params, 'join_type', array());
		$joinTableFrom  = JArrayHelper::getValue($params, 'join_from_table', array());
		$joinTable 			= JArrayHelper::getValue($params, 'table_join', array());
		$tableKey				= JArrayHelper::getValue($params, 'table_key', array());
		$joinTableKey		= JArrayHelper::getValue($params, 'table_join_key', array());
		$groupIds				= JArrayHelper::getValue($params, 'group_id', array());
		$repeats = JArrayHelper::getValue($params, 'join_repeat', array());
		$jc = count($joinTypes);
		//test for repeat elements to eusure their join isnt removed from here

		foreach ($aOldJoins as $oldJoin) {
			if ($oldJoin->params !== '') {
				$oldParams = json_decode($oldJoin->params);
				if ($oldParams->type == 'repeatElement') {
					$aOldJoinsToKeep[] = $oldJoin->id;
				}
			}
		}
		for ($i = 0; $i < $jc ; $i++) {
			$existingJoin = false;
			foreach ($aOldJoins as $oOldJoin) {
				if ($joinIds[$i] == $oOldJoin->id) {
					$existingJoin = true;
				}
			}
			//$$$rob make an index on the join element (fk)
			$els = $this->getFEModel()->getElements();
			foreach ($els as $el) {
				if ($el->getElement()->name == $tableKey[$i]) {
					$size = JString::stristr($el->getFieldDescription(), 'int') ? '' : '10';
				}
			}
			$this->getFEModel()->addIndex($tableKey[$i], 'join', 'INDEX', $size);
			if (!$existingJoin) {
				$this->makeNewJoin($tableKey[$i], $joinTableKey[$i], $joinTypes[$i], $joinTable[$i], $joinTableFrom[$i], $repeats[$i][0]);
			} else {
				/* load in the exisitng join
				 * if the table_join has changed we need to create a new join
				* (with its corresponding group and elements)
				*  and mark the loaded one as to be deleted
				*/
				$joinModel->setId($joinIds[$i]);
				$joinModel->_join = null;
				$join = $joinModel->getJoin();

				if ($join->table_join != $joinTable[$i]) {
					$this->makeNewJoin($tableKey[$i], $joinTableKey[$i], $joinTypes[$i], $joinTable[$i], $joinTableFrom[$i], $repeats[$i][0]);
				} else {
					//the table_join has stayed the same so we simply update the join info
					$join->table_key = str_replace('`', '', $tableKey[$i]);
					$join->table_join_key = $joinTableKey[$i];
					$join->join_type = $joinTypes[$i];
					$join->store();
					//update group
					$group = $this->getTable('Group');
					$group->load($join->group_id);
					$gparams = json_decode($group->params);
					$gparams->repeat_group_button =  $repeats[$i][0] == 1 ? 1 : 0;
					$group->params = json_encode($gparams);
					$group->store();

					$aOldJoinsToKeep[] = $joinIds[$i];
				}
			}
		}
		/* remove non exisiting joins */
		if (is_array($aOldJoins)) {
			foreach ($aOldJoins as $oOldJoin) {
				if (!in_array($oOldJoin->id, $aOldJoinsToKeep)) {
					/* delete join */
					$join = $this->getTable('Join');
					$joinModel->setId($oOldJoin->id);
					unset($joinModel->_join);
					$joinModel->getJoin();
					$joinModel->deleteAll($oOldJoin->group_id);
				}
			}
		}
	}

	/**
	 *  new join make the group, group elements and formgroup entries for the join data
	 * @param string table key
	 * @param string join to table key
	 * @param string join type
	 * @param string join to table
	 * @param string join table
	 * @param bool is the group a repeat
	 */

	protected function makeNewJoin($tableKey, $joinTableKey, $joinType, $joinTable, $joinTableFrom, $isRepeat)
	{
		$formModel = $this->getFormModel();
		$aData = array(
			"name" => $this->getTable()->label ."- [" .$joinTable. "]",
			"label" =>  $joinTable,
		);
		$groupId = $this->createLinkedGroup($aData, true, $isRepeat);

		//$origTable = JRequest::getVar('db_table_name');
		$origTable = JArrayHelper::getValue(JRequest::getVar('jform'), 'db_table_name');
		$_POST['jform']['db_table_name'] = $joinTable;
		$this->createLinkedElements($groupId);
		$_POST['jform']['db_table_name'] = $origTable;
		$join = $this->getTable('Join');
		$join->id = null;
		$join->list_id 		= $this->getState('list.id');
		$join->join_from_table = $joinTableFrom;
		$join->table_join 		= $joinTable;
		$join->table_join_key 	= $joinTableKey;
		$join->table_key 		= str_replace('`', '', $tableKey);
		$join->join_type 		= $joinType;
		$join->group_id 		= $groupId;
		if (!$join->store()) {
			return JError::raiseWarning(500, $join->getError());
		}
	}

	/**
	 * when saving a table that links to a database for the first time we
	 * need to create all the elements based on the database table fields and their
	 * column type
	 *
	 * @access private
	 * @param int group id
	 */

	private function createLinkedElements($groupId)
	{
		$db = FabrikWorker::getDbo(true);
		$user = JFactory::getUser();
		$config	= JFactory::getConfig();
		$createdate = JFactory::getDate();
		$createdate = $createdate->toMySQL();
		$post = JRequest::get('post');
		$tableName = $post['jform']['db_table_name'];
		$formModel = $this->getFormModel();
		$pluginManager = FabrikWorker::getPluginManager();
		$ordering = 0;
		$fabrikDb =& $this->getFEModel()->getDb();

		$groupTable = FabTable::getInstance('Group', 'FabrikTable');
		$groupTable->load($groupId);

		//here we're importing directly from the database schema
		$query = $db->getQuery(true);
		$query->select('id')->from('#__{package}_lists')->where('db_table_name = '.$db->Quote($tableName));
		$db->setQuery($query);
		$id = $db->loadResult();
		$dispatcher = JDispatcher::getInstance();
		$elementModel = new plgFabrik_Element($dispatcher);

		if ($id) {
			//a fabrik table already exists - so we can copy the formatting of its elements
			$groupListModel = JModel::getInstance('list', 'FabrikFEModel');

			$groupListModel->setId($id);
			$groupListModel->getTable();
			//$this->formModel = null; //reset form so that it loads new table form
			$groups = $groupListModel->getFormGroupElementData();
			$newElements = array();
			$ecount = 0;
			foreach ($groups as $groupModel) {
				// if we are saving a new table and the previously found tables group is a join
				// then don't add its elements to the table as they don't exist in the database table
				// we are linking to
				if ($groupModel->isJoin() && JRequest::getCmd('task') == 'save' && JRequest::getInt('id') == 0) {
					continue;
				}
				$elementModels =& $groupModel->getMyElements();
				foreach ($elementModels as $elementModel) {
					$ecount++;
					$element = $elementModel->getElement();
					$copy = $elementModel->copyRow($element->id, $element->label, $groupId);
					if (!Jerror::isError($copy)) {
						$newElements[$element->id] = $copy->id;
					}
				}

			}
			foreach ($newElements as $origId => $newId) {
				$plugin = $pluginManager->getElementPlugin($newId);
				$plugin->finalCopyCheck($newElements);
			}
			// hmm table with no elements - lets create them from the structure anyway
			if ($ecount == 0) {
				$this->makeElementsFromFields($groupId, $tableName);
			}
		} else {
			//no previously found fabrik list
			$this->makeElementsFromFields($groupId, $tableName);
		}
	}

	/**
	 * take a table name and make elements for all of its fields
	 * @param int group id
	 * @param string $tableName
	 */

	function makeElementsFromFields($groupId, $tableName)
	{
		$fabrikDb = $this->getFEModel()->getDb();
		$dispatcher = JDispatcher::getInstance();
		$elementModel = new plgFabrik_Element($dispatcher);
		$pluginManager = FabrikWorker::getPluginManager();
		$user = JFactory::getUser();
		$elementTypes = JRequest::getVar('elementtype', array());
		$fields = $fabrikDb->getTableFields(array($tableName));
		$fields = $fields[$tableName];
		$createdate = JFactory::getDate()->toMySQL();
		$key = $this->getFEModel()->getPrimaryKeyAndExtra($tableName);
		$ordering = 0;
		// no existing fabrik table so we take a guess at the most
		//relavent element types to  create
		$elementLabels = JRequest::getVar('elementlabels', array());
		foreach ($fields as $label => $type) {
			$element = FabTable::getInstance('Element', 'FabrikTable');
			if (array_key_exists($ordering, $elementTypes)) {
				//if importing from a CSV file then we have userselect field definitions
				$plugin = $elementTypes[$ordering];
			} else {
				//if the field is the primary key and it's an INT type set the plugin to be the fabrik internal id
				if ($key[0]['colname'] == $label && strtolower(substr($key[0]['type'], 0, 3)) === 'int') {
					$plugin = 'internalid';
				} else {
					//otherwise guestimate!
					switch ($type)
					{
						case "int" :
						case "tinyint" :
						case "varchar" :
							$plugin = 'field';
							break;
						case "text" :
						case "tinytext" :
						case "mediumtext" :
						case "longtext" :
							$plugin = 'textarea';
							break;
						case "datetime" :
						case "date" :
						case "time" :
						case "timestamp" :
							$plugin = 'date';
							break;
						default :
							$plugin = 'field';
						break;
					}
				}
			}
			$element->plugin 						= $plugin;
			$element->hidden 						= $element->label == 'id' ? '1' : '0';
			$element->group_id 					= $groupId;
			$element->name				 			= $label;
			$element->created 					= $createdate;
			$element->created_by 				= $user->get('id');
			$element->created_by_alias 	= $user->get('username');
			$element->published 						= '1';
			$element->show_in_list_summary = '1';
			switch ($plugin) {
				case 'textarea':
					$element->width = '40';
					break;
				case 'date':
					$element->width = '10';
					break;
				default:
					$element->width = '30';
				break;
			}
			$element->height 	= '6';
			$element->ordering 	= $ordering;
			$element->params 	= $elementModel->getDefaultAttribs();
			$element->label 	= JArrayHelper::getValue($elementLabels, $ordering, str_replace("_", " ", $label));

			if (!$element->store()) {
				return JError::raiseError(500, $element->getError());
			}

			$elementModel =& $pluginManager->getPlugIn($element->plugin, 'element');
			$elementModel->setId($element->id);
			$elementModel->_element = $element;
			// hack for user element
			$details = array(
					'group_id' => $element->group_id
			);
			JRequest::setVar('details', $details);

			$elementModel->onSave();
			$ordering ++;
		}
	}

	/**
	 * when saving a table that links to a database for the first time we
	 * automatically create a form to allow the update/creation of that tables
	 * records
	 * @access private
	 * @param int form id to copy from. If = 0 then create a default form. If not 0 then copy the form id passed in
	 * @return object form model
	 */

	private function createLinkedForm($formid = 0)
	{
		$config	= JFactory::getConfig();
		$user	= JFactory::getUser();
		$this->getFormModel();

		if ($formid == 0) {
			// $$$ rob required otherwise the JTable is loaed with db_table_name as a property
			//which then generates an error - not sure why its loaded like that though?
			// 18/08/2011 - could be due to the Form table class having it in its bind method - (have now overridden form table store() to remove thoes two params)
			$this->formModel->getForm();
			jimport('joomla.utilities.date');
			$createdate = JFactory::getDate();
			$createdate = $createdate->toMySQL();

			$form = $this->getTable('Form');
			$item =& $this->getTable('List');
			$form->label = $item->label;
			$form->record_in_database = 1;

			$form->created 						= $createdate;
			$form->created_by 				= $user->get('id');
			$form->created_by_alias 	= $user->get('username');
			$form->error							= JText::_('COM_FABRIK_FORM_ERROR_MSG_TEXT');
			$form->submit_button_label 	= JText::_('SAVE');
			$form->published							= $item->published;
			$form->form_template			= 'default';
			$form->view_only_template	= 'default';

			if (!$form->store()) {
				return JError::raiseError(500, $form->getError());
			}
			$this->setState('list.form_id', $form->id);
			$this->formModel->setId($form->id);

		} else {
			$this->setState('list.form_id', $formid);
			$this->formModel->setId($formid);
			$this->formModel->getTable();
			if (!$this->formModel->copy()) {
				return JError::raiseError(500, $form->getError());
			}
		}
		$this->formModel->getForm();
		return $this->formModel;
	}

	/**
	 * create a group
	 * used when creating a fabrik table from an existing db table
	 *
	 * NEW also creates the formgroup
	 *
	 * @access private
	 * @param array group data
	 * @param bol is the group a join default false
	 * @param bol is the group repeating
	 * @return int group id
	 */

	private function createLinkedGroup($data, $isJoin = false, $isRepeat = false)
	{
		$user = JFactory::getUser();
		$createdate = JFactory::getDate();
		$group = $this->getTable('Group');
		$group->bind($data);
		$group->id = null;
		$group->created = $createdate->toMySQL();
		$group->created_by = $user->get('id');
		$group->created_by_alias = $user->get('username');
		$group->published = 1;

		$opts = new stdClass();
		$opts->repeat_group_button =  $isRepeat ? 1 : 0;
		$opts->repeat_group_show_first = 1;
		$group->params = json_encode($opts);

		$group->is_join = ($isJoin == true) ? 1 : 0;

		$group->store();
		if (!$group->store()) {
			JError::raiseError(500, $group->getError());
		}
		//create form group
		$formid = $this->getState('list.form_id');
		$formGroup = $this->getTable('FormGroup');
		$formGroup->id = null;
		$formGroup->form_id = $formid;
		$formGroup->group_id = $group->id;
		$formGroup->ordering = 999999;

		if (!$formGroup->store()) {
			JError::raiseError(500, $formGroup->getError());
		}
		$formGroup->reorder(" form_id = '$formid'");
		return $group->id;
	}

	/**
	 * test if the main J user can create mySQL tables
	 * @return bol
	 */

	private function canCreateDbTable()
	{
		return true;
		//@todo run create table test once when you install fabrik instead
		// dont use method below but simply try to create a table and if you cant give error
		// if you can remove tmp created table
		/*$db 		=& FabrikWorker::getDbo();
		$conf =& JFactory::getConfig();
		$host 		= $conf->getValue('config.host');
		$user 		= $conf->getValue('config.user');
		$db->setQuery("SELECT Create_priv FROM mysql.user WHERE (Host = '$host' OR Host = '%') AND user = '$user'");
		$res = $db->loadResult();
		if ($res == 'N' || is_null($res)) {
		return false;
		} else {
		return true;
		}*/
	}

	/**
	 * Method to copy one or more records.
	 *
	 * @param	array	$pks	An array of record primary keys.
	 *
	 * @return	boolean	True if successful, false if an error occurs.
	 * @since	1.6
	 */

	public function copy()
	{
		$db = FabrikWorker::getDbo(true);
		$user = JFactory::getUser();
		$pks = JRequest::getVar('cid', array());
		$post = JRequest::get('post');
		foreach ($pks as $i => $pk) {
			$item =& $this->getTable();
			$item->load($pk);
			$item->id = null;
			JRequest::setVar('newFormLabel', $post['names'][$pk]['formLabel']);

			JRequest::setVar('newGroupNames', $post['names'][$pk]['groupNames']);
			$formModel = $this->createLinkedForm($item->form_id);
			if (!$formModel) {
				return;
			}
			$item->form_id = $formModel->getTable()->id;
			$createdate = JFactory::getDate();
			$createdate = $createdate->toMySQL();
			$item->label = $post['names'][$pk]['listLabel'];
			$item->created = $createdate;
			$item->modified = $db->getNullDate();
			$item->modified_by = $user->get('id');
			$item->hits = 0;
			$item->checked_out = 0;
			$item->checked_out_time = $db->getNullDate();
			if (!$item->store()) {
				$this->setError($item->getError());
				return false;
			}
			$this->setState('list.id', $item->id);
			//test for seeing if joins correctly stored when coping new table
			$this->copyJoins($pk, $item->id, $formModel->groupidmap);
		}
		return true;
	}

	/**
	 * when copying a table we need to copy its joins as well
	 * note that the group and elements already exists - just the join needs to be saved
	 * @param int $fromid table id to copy from
	 * @param int table id to copy to
	 * @param array group id map saying which groups got copied to which new group id (key = old id, value = new id)
	 * @return null
	 */

	function copyJoins($fromid, $toid, $groupidmap)
	{
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->select('*')->from('#__{package}_joins')->where('list_id = '.(int)$fromid);
		$db->setQuery($query);
		$joins = $db->loadObjectList();
		$feModel = $this->getFEModel();
		foreach ($joins as $join) {
			$size = 10;
			$els =& $feModel->getElements();
			foreach ($els as $el) {
				if( $el->getElement()->name == $join->table_key) {
					$size = JString::stristr($el->getFieldDescription(), 'int') ? '' : '10';
				}
			}
			$feModel->addIndex($join->table_key, 'join', 'INDEX', $size);
			$joinTable =$this->getTable('Join');
			$joinTable->load($join->id);
			$joinTable->id = 0;
			$joinTable->group_id = $groupidmap[$joinTable->group_id];
			$joinTable->list_id 		= $toid;
			if (!$joinTable->store()) {
				return JError::raiseWarning(500, $join->getError());
			}
		}
	}

	/**
	 * @depreciated
	 *
	 * replaces the table column names with a safer name - ie removes white
	 * space and none alpha numeric characters
	 * @depreciated fabrik3.0
	 */

	private function makeSafeTableColumns()
	{
		//going to test allowing non safe names - as they should be quoted when accessed
		return;
	}

	/**
	 * adds a primary key to the database table
	 * @param string the column name to make into the primary key
	 * @param bol is the column an auto incrementing number
	 * @param string column type definition (eg varchar(255))
	 */

	function updatePrimaryKey($fieldName, $autoIncrement, $type = 'int(11)')
	{
		$feModel = $this->getFEModel();
		if (!$feModel->canAlterFields()) {
			return;
		}
		$fabrikDatabase = $feModel->getDb();
		$post = JRequest::get('post');
		$tableName = ($post['jform']['db_table_name'] != '') ? $post['jform']['db_table_name'] : $post['jform']['_database_name'];
		$tableName = preg_replace('#[^0-9a-zA-Z_]#', '_', $tableName);
		$aPriKey = $feModel->getPrimaryKeyAndExtra($tableName);

		if (!$aPriKey) {
			// no primary key set so we should set it
			$this->addKey($fieldName, $autoIncrement, $type);
		} else {
			if (count($aPriKey ) > 1){
				// $$$ rob multi field pk - ignore for now
				return;
			}
			$aPriKey = $aPriKey[0];
			$shortKey = FabrikString::shortColName($fieldName);
			//$shortKey = $feModel->_shortKey($fieldName, true); // added true for second arg so it strips quotes, as was never matching colname with quotes
			if ($fieldName !=  $aPriKey['colname'] && $shortKey != $aPriKey['colname']) {
				$this->dropKey($aPriKey); // primary key already exists so we should drop it
				$this->addKey($fieldName, $autoIncrement, $type);
			} else {
				//update the key
				// $$$ hugh - only update it if we need to
				$priInc = $aPriKey['extra'] == 'auto_increment' ? '1' : '0';
				if ($priInc != $autoIncrement || $type != $aPriKey['type']) {
					$this->updateKey($fieldName, $autoIncrement, $type);
				}
			}
		}
	}

	/**
	 * internal function: add a key to the table
	 * @param string primary key column name
	 * @param bol is the column auto incrementing
	 * @param string the primary keys column type (if autoincrement true then int(6) is always used as the type)
	 */

	private function addKey($fieldName, $autoIncrement, $type = "INT(6)")
	{
		$db = $this->getFEModel()->getDb();
		$type = $autoIncrement != true ? $type : 'INT(6)';
		//$table     =& $this->getTable();
		//$table->load($this->getState('list.id'));
		$post = JRequest::get('post');
		$tableName = ($post['jform']['db_table_name'] != '') ? $post['jform']['db_table_name'] : $post['jform']['_database_name'];
		$tableName = preg_replace('#[^0-9a-zA-Z_]#', '_', $tableName);
		$tableName = FabrikString::safeColName($tableName);
		$fieldName = FabrikString::shortColName($fieldName);
		if ($fieldName === "") {
			return false;
		}
		$sql = "ALTER TABLE ".$tableName." ADD PRIMARY KEY ($fieldName)";
		/* add a primary key */

		$db->setQuery($sql);
		if (!$db->query()) {
			return JError::raiseWarning(500, $db->getErrorMsg());
		}
		if ($autoIncrement) {
			$sql = "ALTER TABLE ".$tableName." CHANGE $fieldName $fieldName $type NOT NULL AUTO_INCREMENT"; //add the autoinc
			$db->setQuery($sql);
			if (!$db->query()) {
				return JError::raiseError(500, 'add key: ' . $db->getErrorMsg());
			}
		}
	}

	/**
	 * internal function: drop the table's key
	 * @param array existing key data
	 * @return bol true if ke droped
	 */

	private function dropKey($aPriKey)
	{
		$db = $this->getFEModel()->getDb();
		//$table =& $this->getTable();
		//$table->load($this->getState('list.id'));
		$post = JRequest::get('post');
		$tableName = FabrikString::safeColName($post['jform']['db_table_name']);
		$sql = "ALTER TABLE ".$tableName." CHANGE " . FabrikString::safeColName($aPriKey['colname']) . ' '. FabrikString::safeColName($aPriKey['colname']) . ' '  . $aPriKey['type'] . " NOT NULL";
		/* removes the autoinc */
		$db->setQuery($sql);
		if (!$db->query()) {
			JError::raiseWarning(500, $db->getErrorMsg()) ;
			return false;
		}
		$sql = "ALTER TABLE ".$tableName." DROP PRIMARY KEY";
		/* drops the primary key */
		$db->setQuery($sql);
		if (!$db->query()) {
			JError::raiseWarning(500, 'alter table: ' . $db->getErrorMsg()) ;
			return false;
		}
		return true;
	}

	/**
	 * internal function: update an exisitng key in the table
	 * @param string primary key column name
	 * @param bol is the column auto incrementing
	 * @param string the primary keys column type
	 */

	function updateKey($fieldName, $autoIncrement, $type = "INT(11)")
	{
		$post = JRequest::get('post');
		$tableName = FabrikString::safeColName($post['jform']['db_table_name']);
		$db = $this->getFEModel()->getDb();
		if (strstr($fieldName, '.')) {
			$fieldName = array_pop(explode(".", $fieldName));
		}
		$table =& $this->getTable();
		$table->load($this->getState('list.id'));
		$sql = "ALTER TABLE ".$tableName." CHANGE ".FabrikString::safeColName($fieldName).' '.FabrikString::safeColName($fieldName)." $type NOT NULL ";
		/* update primary key */
		if ($autoIncrement) {
			$sql .= " AUTO_INCREMENT";
		}
		$db->setQuery($sql);
		if (!$db->query()) {
			$this->setError('update key:'.$db->getErrorMsg());
		}
	}

	/**
	 * translation has been turned off for the table so delete the content
	 * element xml file
	 */

	private function removeJoomfishXML()
	{
		$file = JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomfish'.DS.'contentelements'.DS.'fabrik-' . $this->getTable()->db_table_name . '.xml';
		if (JFile::exists($file)) {
			JFile::delete($file);
		}
	}

	/**
	 * write out the Joomfish contentelement xml file for the tables elements
	 * @return bol true if written out ok
	 */

	private function makeJoomfishXML()
	{
		$config = JFactory::getConfig();
		$db = FabrikWorker::getDbo(true);
		$elements = $this->getElements();

		//get all database join elements and check if we need to create xml files for them

		$table =& $this->getTable();
		$tableName = str_replace($config->getValue('dbprefix'), '',$table->db_table_name);
		$params =& $this->getParams();
		$titleElement = $params->get('joomfish-title');
		$str = '<?xml version="1.0" ?>
<joomfish type="contentelement">
  <name>Fabrik - ' . $table->label . '</name>
  <author>rob@pollen-8.co.uk</author>
  <version>1.0 for Fabrik 2.0</version>
  <description>Definition for Fabrik Table data - ' . $table->label . '</description>
  <reference type="content">
  	<table name="' . $tableName . '">';
		$titleset = false;
		foreach ($elements as $element) {
			if ($table->db_primary_key == FabrikString::safeColName($element->getFullName(false, false, false)))  {
				//primary key element
				$type = 'referenceid';
				$t = 0;
			} else {
				if (!$titleset && $titleElement == '') {
					$type ='titletext';
					$titleset = true;
				} else {
					if ($titleElement == $element->getFullName(false, false, false)) {
						$type ='titletext';
						$titleset = true;
					} else {
						$type = $element->getJoomfishTranslationType();
					}
				}
				$t = $element->getJoomfishTranslatable();
			}
			$opts = $element->getJoomfishOptions();
			$el = $element->getElement();
			$str .= "\n\t\t" . '<field type="'.$type.'" name="'.$el->name.'" translate="'.$t.'"';
			foreach ($opts as $k=>$v) {
				$str .= " $k=\"$v\"";
			}
			$str .='>' . $el->label . '</field>';
		}
		$str .='
  	</table>
  </reference>
</joomfish>';
		//file name HAS to be the same as the table name MINUS db extension
		return JFile::write(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_joomfish'.DS.'contentelements'.DS.$tableName.'.xml', $str);
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param	array	$pks	An array of record primary keys.
	 *
	 * @return	boolean	True if successful, false if an error occurs.
	 * @since	1.6
	 */

	public function delete(&$pks)
	{
		// Initialise variables.
		$dispatcher	= JDispatcher::getInstance();
		$user	= JFactory::getUser();
		$pks = (array)$pks;
		$table = $this->getTable();
		$app = JFactory::getApplication();
		// Include the content plugins for the on delete events.
		JPluginHelper::importPlugin('content');

		$post = JRequest::get('post');
		$deleteDepth = $post['jform']['recordsDeleteDepth'];
		$drop = $post['jform']['dropTablesFromDB'];

		$feModel = $this->getFEModel();
		$fabrikDatabase = $feModel->getDb();
		$dbconfigprefix = JApplication::getCfg("dbprefix");
		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk) {
			$feModel->setId($pk);
			if ($table->load($pk)) {
				$feModel->set('_table', $table);

				if ($drop) {
					if (strncasecmp($table->db_table_name, $dbconfigprefix, strlen($dbconfigprefix)) == 0) {
						$app->enqueueMessage(JText::sprintf('COM_FABRIK_TABLE_NOT_DROPPED_PREFIX', $table->db_table_name, $dbconfigprefix), 'notice');
					} else {
						$feModel->drop();
						$app->enqueueMessage(JText::sprintf('COM_FABRIK_TABLE_DROPPED', $table->db_table_name));
					}
				} else {
					$app->enqueueMessage(JText::sprintf('COM_FABRIK_TABLE_NOT_DROPPED', $table->db_table_name));
				}
				if ($this->canDelete($table)) {
					$context = $this->option.'.'.$this->name;

					// Trigger the onContentBeforeDelete event.
					$result = $dispatcher->trigger($this->event_before_delete, array($context, $table));
					if (in_array(false, $result, true)) {
						$this->setError($table->getError());
						return false;
					}

					if (!$table->delete($pk)) {
						$this->setError($table->getError());
						return false;
					}

					// Trigger the onContentAfterDelete event.
					$dispatcher->trigger($this->event_after_delete, array($context, $table));

					// get the tables form
				} else {

					// Prune items that you can't change.
					unset($pks[$i]);
					return JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDIT_STATE_NOT_PERMITTED'));
				}

				switch ($deleteDepth) {
					case 0: //list only
					default:
						break;
					case 1: //list and form
						$form = $this->deleteAssociatedForm($table);
						break;
					case 2://list form and groups
						$form = $this->deleteAssociatedForm($table);
						$this->deleteAssociatedGroups($form, false);
						break;
					case 3://list form groups and elements
						$form = $this->deleteAssociatedForm($table);
						$this->deleteAssociatedGroups($form, true);
						break;
				}

			} else {
				$this->setError($table->getError());
				return false;
			}
		}
		return true;
	}

	private function deleteAssociatedForm(&$table)
	{
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$form = $this->getTable('form');
		$form->load($table->form_id);
		if ((int)$form->id === 0) {
			return false;
		}
		$query->delete()->from('#__{package}_forms')->where('id = '.(int)$form->id);
		$db->setQuery($query);
		$db->query();
		return $form;
	}

	private function deleteAssociatedGroups(&$form, $deleteElements = false)
	{
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		//get group ids
		if ((int)$form->id === 0) {
			return false;
		}
		$query->select('group_id')->from('#__{package}_formgroup')->where('form_id = '.(int)$form->id);
		$db->setQuery($query);
		$groupids = (array)$db->loadResultArray();

		//delete groups
		$groupModel = JModel::getInstance('Group', 'FabrikModel');
		$groupModel->delete($groupids, $deleteElements);
		return $form;
	}

	public function dbTableFromXML($key, $name, $xml)
	{
		$row = $xml[0];
		$data = array();
		//	get which field types to use
		foreach ($row->children() as $child) {
			$value = sprintf("%s", $child);
			$type = $child->attributes()->type;
			if ($type == '') {
				$objtype = strtotime($value) == false ? "VARCHAR(255)" : "DATETIME";
				if (strstr($value, "\n")) {
					$objtype = 'TEXT';
				}
			} else {
				switch (strtolower($type)) {
					case 'integer':
						$objtype = 'INT';
						break;
					case 'datetime':
						$objtype = "DATETIME";
						break;
					case 'float':
						$objtype = "DECIMAL(10,2)";
						break;
					default:
						$objtype = "VARCHAR(255)";
					break;
				}
			}

			$data[$child->getName()] = $objtype;
		}
		if (empty($data)) {
			return false;
		}
		$db = $this->_db;
		$query = $db->getQuery();
		$query = "CREATE TABLE IF NOT EXISTS " . $db->nameQuote($name) . " (";
		foreach ($data as $fname => $objtype) {
			$query .= $db->nameQuote($fname) . " $objtype, \n";
		}
		$query .= " primary key ($key))";
		$query .= " ENGINE = MYISAM ";
		$db->setQuery($query);
		$db->query();

		//get a list of existinig ids
		$query = $db->getQuery(true);
		$query->select($key)->from($name);
		$db->setQuery($query);
		$existingids = $db->loadResultArray();
		//build the row object to insert/update
		foreach ($xml as $row) {
			$o = new stdClass();
			foreach ($row->children() as $child) {
				$k = $child->getName();
				$o->$k = sprintf("%s", $child);
			}
			//either update or add records
			if (in_array($o->$key, $existingids)) {
				$db->updateObject($name, $o, $key);
			} else {
				$db->insertObject($name, $o, $key);
			}
		}
	}

	public function loadFromFormId($formId)
	{
		$item = $this->getTable();
		$item->load(array('form_id' => $formId));
		$this->_table = $item;
		$this->setState('list.id', $item->id);
		return $item;
	}

	/**
	 * @since 3.0b
	 * load the database object associated with the list
	 *@return object database
	 */

	public function &getDb()
	{
		return FabrikWorker::getConnection($this->getItem())->getDb();
	}

	/**
	 * Create a table to store the forms' data depending upon what groups are assigned to the form
	 * @param object form model
	 * @param string table name - taken from the table oject linked to the form
	 * @param array list of default elements to add. (key = element name, value = plugin
	 * @return mixed false if fail otherwise array of primary keys
	 */

	public function createDBTable($dbTableName = null, $fields = array('id' => 'internalid', 'date_time' => 'date'))
	{
		$db = FabrikWorker::getDbo(true);
		$fabrikDb = $this->getDb();
		$user	= JFactory::getUser();
		$config = JFactory::getConfig();
		if (is_null($dbTableName)) {
			$dbTableName = $this->getTable()->db_table_name;
		}
		$sql = "CREATE TABLE IF NOT EXISTS ".$db->nameQuote($dbTableName)." (";

		$post = JRequest::get('post');
		if (array_key_exists('jform', $post) && ($post['jform']['id'] == 0 && array_key_exists('current_groups', $post['jform']))) {
			//saving a new form
			$groupIds = $post['jform']['current_groups'];
		} else {
			$query = $db->getQuery(true);
			$formid = (int)$this->get('form.id', $this->getFormModel()->id);
			$query->select('group_id')->from('#__{package}_formgroup')->where('form_id = '.$formid);
			$db->setQuery($query);
			$groupIds = $db->loadResultArray();
		}
		$i = 0;
		foreach ($fields as $name => $plugin) {
			//installation demo data sets 2 groud ids
			if (is_string($plugin)) {
				$plugin = array('plugin' => $plugin, 'group_id' => $groupIds[0]);
			}
			$plugin['ordering'] = $i;
			$element = $this->makeElement($name, $plugin);
			if (!$element) {
				return false;
			}
			$elementModels[] = clone($element);
			$i ++;
		}

		$arAddedObj = array();
		$keys = array();
		$lines = array();

		foreach ($elementModels as $elementModel) {
			$element = $elementModel->getElement();

			/* replace all non alphanumeric characters with _ */
			$objname = preg_replace("/[^A-Za-z0-9]/", "_", $element->name);
			if ($element->primary_key) {
				$keys[] = $objname;
			}
			/* any elements that are names the same (eg radio buttons) can not be entered twice into the database */
			if (!in_array($objname, $arAddedObj)) {
				$arAddedObj[] = $objname;
				$objtype = $elementModel->getFieldDescription();
				if ($objname != "" && !is_null($objtype)) {
					if (JString::stristr($objtype, 'not null')) {
						$lines[] = $fabrikDb->nameQuote($objname) . " $objtype";
					} else {
						$lines[] = $fabrikDb->nameQuote($objname) . " $objtype null";
					}
				}
			}
		}

		$func = create_function('$value', '$db = FabrikWorker::getDbo(true);;return $db->nameQuote($value);');

		$sql .= implode(', ', $lines);
		if (!empty($keys)) {
			$sql .= ", PRIMARY KEY (".implode(',', array_map($func, $keys))."))";
		}
		$sql .= " ENGINE = MYISAM ";
		$fabrikDb->setQuery($sql);
		if (!$fabrikDb->query()) {
			JError::raiseError(500, $fabrikDb->getErrorMsg());
			return false;
		}
		return $keys;
	}

	/**
	 * create an element
	 * @param string $name
	 * @param array $plugin properties
	 * @return mixed false if failed, otherwise element plugin
	 */

	public function makeElement($name, $data)
	{
		$pluginMananger = FabrikWorker::getPluginManager();
		$element = $pluginMananger->loadPlugIn($data['plugin'], 'element');
		$item = $element->getDefaultProperties();
		$item->id = null;
		$item->name = $item->label = $name;
		$item->bind($data);
		if (!$item->store()) {
			JError::raiseWarning(500, $item->getError());
			return false;
		}
		return $element;
	}

	/**
	 * return the default set of attributes when creating a new
	 * fabrik list
	 *
	 * @return string json enocoded Params
	 */

	public function getDefaultParams()
	{
		$a = array('advanced-filter' => 0,
			'show-table-nav' => 1,
			'show-table-filters' => 1, 'show-table-add' => 1, 'require-filter' => 0);
		$o = (object)$a;
		$o->admin_template = 'admin';
		$o->detaillink = 0;
		$o->empty_data_msg = 'No data found';
		$o->pdf = '';
		$o->rss = 0;
		$o->feed_title= '';
		$o->feed_date= '';
		$o->rsslimit= 150;
		$o->rsslimitmax= 2500;
		$o->csv_import_frontend= 3;
		$o->csv_export_frontend = 3;
		$o->csvfullname = 0;
		$o->access = 1;
		$o->allow_view_details = 1;
		$o->allow_edit_details = 1;
		$o->allow_add = 1;
		$o->allow_delete = 1;
		$o->group_by_order = '';
		$o->group_by_order_dir = 'ASC';
		$o->prefilter_query = '';
		return json_encode($o);
	}

	/**
	 * Alter the forms' data collection table when the forms' groups and/or
	 * elements are altered
	 */

	public function ammendTable()
	{
		$db = FabrikWorker::getDbo(true);
		$user = JFactory::getUser();
		$table = $this->getTable();
		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');
		$ammend = false;
		$formModel = $this->getFormModel();
		$tableName = $table->db_table_name;
		$fabrikDb = $this->getDb();
		$existingfields = array_map('strtolower', array_keys($fabrikDb->getTableColumns($tableName)));
		$lastfield = $existingfields[count($existingfields)-1];
		$sql = "ALTER TABLE ".$db->nameQuote($tableName)." ";
		$sql_add = array();
		if (!isset($_POST['current_groups_str'])) {
			/* get a list of groups used by the form */
			$groupsql = "SELECT group_id FROM #__{package}_formgroup WHERE form_id = ".(int)$formModel->id;
			$db->setQuery($groupsql);
			$groups = $db->loadObjectList();
			if ($db->getErrorNum()) {
				JError::raiseWarning(500,  'ammendTable: ' . $db->getErrorMsg());
			}
			$arGroups = array();
			foreach ($groups as $g) {
				$arGroups[] = $g->group_id;
			}
		} else {
			$current_groups_str = JRequest::getVar('current_groups_str');

			$arGroups = explode(",", $current_groups_str);
		}
		$arAddedObj = array();
		foreach ($arGroups as $group_id) {
			$group = FabTable::getInstance('Group', 'FabrikTable');
			$group->load($group_id);
			if ($group->is_join == '0') {
				$groupsql = "SELECT * FROM #__{package}_elements WHERE group_id = ".(int)$group_id;
				$db->setQuery($groupsql);
				$elements = $db->loadObjectList();
				foreach ($elements as $obj) {
					// do this in the add element form (but in any event names should be quoted)
					//$objname = strtolower(preg_replace("/[^A-Za-z0-9]/", "_", $obj->name));
					$objname = $obj->name;
					if (!in_array($objname, $existingfields)) {
						/* make sure that the object is not already in the table*/
						if (!in_array($objname, $arAddedObj)) {
							/* any elements that are names the same (eg radio buttons) can not be entered twice into the database*/
							$arAddedObj[] 		= $objname;
							$objtypeid 				= $obj->plugin;
							$pluginClassName 	= $obj->plugin;
							$plugin 					= $pluginManager->getPlugIn($pluginClassName, 'element');
							$plugin->setId($obj->id);
							$objtype 					= $plugin->getFieldDescription();
							if ($objname != "" && !is_null($objtype)) {
								$ammend = true;
								$sql_add[] = "ADD COLUMN ".$db->nameQuote($objname)." $objtype null AFTER ".$db->nameQuote($lastfield);
							}
						}
					}
				}
			}
		}
		if ($ammend) {
			$sql .= implode(', ', $sql_add);
			$fabrikDb->setQuery($sql);
			if (!$fabrikDb->query()) {
				return JError::raiseWarning(500, 'amend table: ' . $fabrikDb->getErrorMsg());
			}
		}
	}
}
?>