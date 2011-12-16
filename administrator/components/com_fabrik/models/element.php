<?php
/*
 * Element Model
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


class FabrikModelElement extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_ELEMENT';

	protected $abstractPlugins = null;

	protected $core = array(
	  '#__assets',
'#__banner_clients',
'#__banner_tracks',
'#__banners',
'#__categories',
'#__contact_details',
'#__content',
'#__content_frontpage',
'#__content_rating',
'#__core_log_searches',
'#__extensions',
'#__fabrik_connections',
'#__{package}_cron',
'#__{package}_elements',
'#__{package}_form_sessions',
'#__{package}_formgroup',
'#__{package}_forms',
'#__{package}_groups',
'#__{package}_joins',
'#__{package}_jsactions',
'#__{package}_lists',
'#__{package}_log',
'#__{package}_packages',
'#__{package}_validations',
'#__{package}_visualizations',
'#__fb_contact_sample',
'#__languages',
'#__menu',
'#__menu_types',
'#__messages',
'#__messages_cfg',
'#__modules',
'#__modules_menu',
'#__newsfeeds',
'#__redirect_links',
'#__schemas',
'#__session',
'#__template_styles',
'#__update_categories',
'#__update_sites',
'#__update_sites_extensions',
'#__updates',
'#__user_profiles',
'#__user_usergroup_map',
'#__usergroups',
'#__users',
'#__viewlevels',
'#__weblinks'
	);

	/**
	 * Constructor.
	 * Ensure that we use the fabrik db model for the dbo
	 * @param	array	An optional associative array of configuration settings.
	 */

	public function __construct($config = array())
	{
		$config['dbo'] = FabriKWorker::getDbo(true);
		parent::__construct($config);
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'Element', $prefix = 'FabrikTable', $config = array())
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
		$form = $this->loadForm('com_fabrik.element', 'element', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
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
		$data = JFactory::getApplication()->getUserState('com_fabrik.edit.element.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	public function getElements()
	{
		$db = FabrikWorker::getDbo(true);
		$item = $this->getItem();
		$aEls = array();
		$aGroups = array();

		$query	= $db->getQuery(true);
		$query->select('form_id');
		$query->from($db->nameQuote('#__{package}_formgroup').' AS fg');
		$query->where('fg.group_id = '.(int)$item->group_id);
		$db->setQuery($query);
		$formrow = $db->loadObject();
		if (is_null($formrow)) {
			$aEls[] = $aGroups[] = JText::_('COM_FABRIK_GROUP_MUST_BE_IN_A_FORM');
		} else {
			$formModel = JModel::getInstance('Form', 'FabrikFEModel');
			$formModel->setId($formrow->form_id);

			//get available element types
			$groups = $formModel->getGroupsHiarachy();

			foreach ($groups as $groupModel) {
				$group = $groupModel->getGroup();
				$o = new stdClass();
				$o->label = $group->name;
				$o->value = "fabrik_trigger_group_group".$group->id;
				$aGroups[] = $o;
				$elementModels =& $groupModel->getMyElements();
				foreach ($elementModels as $elementModel) {
					$o = new stdClass();
					$element =& $elementModel->getElement();
					$o->label = FabrikString::getShortDdLabel($element->label);
					$o->value = "fabrik_trigger_element_".$elementModel->getFullName(false, true, false);
					$aEls[] = $o;
				}
			}
		}
		asort($aEls);
		$o = new StdClass();
		$o->groups = $aGroups;
		$o->elements = array_values($aEls);
		return $o;
	}

	/**
	 * toggle adding / removing the elment from the list view
	 * @param unknown_type $pks
	 * @param unknown_type $value
	 * @return bool
	 */
	public function addToListView(&$pks, $value = 1)
	{
		// Initialise variables.
		$dispatcher	= JDispatcher::getInstance();
		$user		= JFactory::getUser();
		$item		= $this->getTable();
		$pks		= (array) $pks;

		// Include the content plugins for the change of state event.
		JPluginHelper::importPlugin('content');

		// Access checks.
		foreach ($pks as $i => $pk) {
			if ($item->load($pk)) {
				if (!$this->canEditState($item)) {
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDIT_STATE_NOT_PERMITTED'));
				}
			}
		}

		// Attempt to change the state of the records.
		if (!$item->addToListView($pks, $value, $user->get('id'))) {
			$this->setError($item->getError());
			return false;
		}

		$context = $this->option.'.'.$this->name;

		// Trigger the onContentChangeState event.
		$result = $dispatcher->trigger($this->event_change_state, array($context, $pks, $value));
		if (in_array(false, $result, true)) {
			$this->setError($item->getError());
			return false;
		}

		return true;
	}

	/**
	 * get the js events that are used by the element
	 * @return array
	 */

	function getJsEvents()
	{
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$id = (int)$this->getItem()->id;
		$query->select('*')->from('#__{package}_jsactions')->where('element_id = '.$id);
		$db->setQuery($query);
		$items = $db->loadObjectList();
		for ($i=0; $i < count($items); $i++) {
			$items[$i]->params = json_decode($items[$i]->params);
		}
		return $items;
	}

	/**
	 * get plugins that could potentially be used
	 * @return array plugins
	 */

	public function getAbstractPlugins()
	{
		if (isset($this->abstractPlugins)) {
			return $this->abstractPlugins;
		}
		// create a new dispatcher so that we only collect admin html for validation rules
		$pluginDispatcher = new JDispatcher();

		//import the validation plugins and assign them to their custom dispatcher
		JPluginHelper::importPlugin('fabrik_validationrule', null, true, $pluginDispatcher);
		$this->abstractPlugins = array();

		//trigger the validation dispatcher to get the validation rules html
		$plugins = JPluginHelper::getPlugin('fabrik_validationrule');

		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');

		$item = $this->getItem();
		foreach ($item as $key => $val) {
			if ($key !== 'params') {
				$data[$key] = $val;
			}
		}

		foreach ($plugins as $x => $plugin) {
			$o = $pluginManager->getPlugIn($plugin->name, 'Validationrule');
			$str = $o->onRenderAdminSettings($data, 0);
			$js = $o->onGetAdminJs($plugin->name, $plugin->name, $str);
			$str = addslashes(str_replace(array("\n", "\r"), "", $str));
			$attr = "class=\"inputbox elementtype\"";
			$this->abstractPlugins[$plugin->name] = array('plugin'=>$plugin->name, 'html'=>$str, 'js'=>$js);
		}
		ksort($this->abstractPlugins);
		return $this->abstractPlugins;
	}

	/**
	 * load the actual validation plugins that the element uses
	 * @return array plugins
	 */

	public function getPlugins()
	{
		$item = $this->getItem();

		// load up the active validation rules for this element
		$dispatcher = &JDispatcher::getInstance();

		$validations = JArrayHelper::getValue($item->params, 'validations', array());
		$plugins = JArrayHelper::getValue($validations, 'plugin', array());
		$return = array();
		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');
		$pluginData = empty($item->params) ? array() : (array)$item->params;
		$locations = JArrayHelper::getValue($item->params, 'plugin_locations');
		$events = JArrayHelper::getValue($item->params, 'plugin_locations');
		foreach ($plugins as $x => $plugin) {

			$data = array();
			foreach ($item as $key => $val) {
				if ($key !== 'params') {
					$data[$key] = $val;
				}
			}
			//get the current data for repeated validation
			foreach ($pluginData as $key => $values) {
				if ($key == 'plugin') {
					continue;
				}
				$data[$key] = JArrayHelper::getValue($values, $x);
			}

			$o = $pluginManager->getPlugIn($plugin, 'Validationrule');
			if ($o !== false) {
				$str = $o->onRenderAdminSettings($data, $x);
				$str = addslashes(str_replace(array("\n", "\r"), "", $str));
				$attr = "class=\"inputbox elementtype\"";
				$location = JArrayHelper::getValue($locations, $x);
				$event = JArrayHelper::getValue($events, $x);
				$return[] = array('plugin'=>$plugin, 'html'=>$str, 'location'=>$location, 'event'=>$event);
			}
		}
		return $return;
	}

	/**
	 * get the js code to build the plugins etc
	 * @return string js code
	 */

	public function getJs()
	{
		$abstractPlugins = $this->getAbstractPlugins();
		$plugins = $this->getPlugins();
		$item = $this->getItem();
		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');

		$opts = new stdClass();
		$opts->plugin = $item->plugin;
		$opts->parentid = (int)$item->parent_id;
		$opts->jsevents = $this->getJsEvents();
		$opts->elements = $this->getElements();
		$opts->id = (int)$item->id;
		$opts = json_encode($opts);

		JText::script('COM_FABRIK_ACTION');
		JText::script('COM_FABRIK_CODE');
		JText::script('COM_FABRIK_OR');
		JText::script('COM_FABRIK_DELETE');
		JText::script('COM_FABRIK_SELECT_ON');
		JText::script('COM_FABRIK_SELECT_DO');
		JText::script('COM_FABRIK_WHERE_THIS');
		JText::script('COM_FABRIK_PLEASE_SELECT');
		$js =
	"
  head.ready(function() {
  	var opts = $opts;";

		$js .= "\t\tvar aPlugins = [];\n";
		foreach ($abstractPlugins as $abstractPlugin) {
			$js .= "\t\taPlugins.push(".$abstractPlugin['js'].");\n";
		}
		$js .= "controller = new fabrikAdminElement(aPlugins, opts);\n";
		foreach ($plugins as $plugin) {
			$opts = new stdClass();
			$opts->location = @$plugin['location'];
			$opts->event = @$plugin['event'];
			$opts = json_encode($opts);
			$js .= "controller.addAction('".$plugin['html']."', '".$plugin['plugin']."',".$opts.", false);\n";
		}
		$js .= "
});";
		return $js;
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
		JRequest::setvar('view', 'element');
		JPluginHelper::importPlugin('fabrik_element', $plugin);
		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');
		if ($plugin == '') {
			$str = JText::_('COM_FABRIK_SELECT_A_PLUGIN');
		} else {
			$plugin = $pluginManager->getPlugIn($plugin, 'Element');
			if (!is_object($plugin)) {
				JError::raiseNotice(500, 'Could not load plugin:' . $plugin);
			} else {
				$str = $plugin->onRenderAdminSettings(JArrayHelper::fromObject($item));
			}
		}
		return $str;
	}

	/**
	 * called when the table is saved
	 * here we are hacking various repeat data into the params
	 * data stored as a json object
	 * @param $item
	 */

	function prepareTable($item) {

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
		$ok = parent::validate($form, $data);
		//standard jform validation failed so we shouldn't test further as we can't
		//be sure of the data
		if (!$ok) {
			return false;
		}
		$db = FabrikWorker::getDbo(true);

		// validate name
		//$data['name'] = str_replace('-', '_', $data['name']);

		if (FabrikWorker::isReserved($data['name'])) {
			$this->setError(JText::_('COM_FABRIK_RESEVED_NAME_USED'));
		}

		$elementModel = $this->getElementPluginModel($data);

		$elementModel->getElement()->bind($data);
		if ($data['id'] === 0) {
			//have to forcefully set group id otherwise listmodel id is blank
			$elementModel->getElement()->group_id = $data['group_id'];
		}

		$listModel =& $elementModel->getListModel();

		//test for duplicate names
		//unlinking produces this error
		if (!JRequest::getVar('unlink', false) && (int)$data['id'] === 0) {

			$row->group_id = (int)$data['group_id'];

			$query = $db->getQuery(true);
			$query->select('t.id')->from('#__{package}_joins AS j');
			$query->join('INNER', "#__{package}_lists AS t ON j.table_join = t.db_table_name");
			$query->where("group_id = $row->group_id AND element_id = 0");
			$db->setQuery($query);
			$joinTblId = (int)$db->loadResult();
			$ignore = array($data['id']);
			if ($joinTblId === 0) {
				if ($listModel->fieldExists($data['name'], $ignore)) {
					$this->setError(JText::_('COM_FABRIK_ELEMENT_NAME_IN_USE'));
				}
			} else {
				$joinListModel = JModel::getInstance('list', 'FabrikFEModel');
				$joinListModel->setId($joinTblId);
				$joinEls = $joinListModel->getElements();

				foreach ($joinEls as $joinEl) {
					if ($joinEl->getElement()->name == $data['name']) {
						$ignore[] = $joinEl->getElement()->id;
					}
				}

				if ($joinListModel->fieldExists($data['name'], $ignore)) {
					$this->setError(JText::_('COM_FABRIK_ELEMENT_NAME_IN_USE'));
				}
			}
		}
		//end  duplicate name test
		// $$$ rob commented out as on new elemetns db join was creating
		// join records pointing to an el id of 0
		// should consider makeing an $element->onValidate() or similar
		/*if (!$elementModel->onSave()) {
		 $this->setError(JText::_('COM_FABRIK_ERROR_SAVING_ELEMENT_PLUGIN_OPTIONS'));
	 }*/

		return count($this->getErrors()) == 0 ? $data : false;
	}

	/**
	 * load the element plugin / model for the posted data
	 * @param array $data
	 */

	private function getElementPluginModel($data)
	{
		$pluginManager	= JModel::getInstance('Pluginmanager', 'FabrikFEModel');
		$id	= $data['id'];
		$elementModel = $pluginManager->getPlugIn($data['plugin'], 'element');
		// $$$ rob f3 - need to bind the data in here otherwise validate fails on dup name test (as no group_id set)
		$elementModel->getElement()->bind($data);
		$elementModel->setId($id);
		return $elementModel;
	}


	function save($data)
	{
		jimport('joomla.utilities.date');
		$user = JFactory::getUser();
		$app = JFactory::getApplication();

		$params = $data['params'];
		$name = $data['name'];

		$params['validations'] = JArrayHelper::getValue($data, 'validationrule', array());
		$elementModel = $this->getElementPluginModel($data);

		$row = $elementModel->getElement();

		if ($data['id'] === 0) {
			//have to forcefully set group id otherwise listmodel id is blank
			$elementModel->getElement()->group_id = $data['group_id'];
		}
		$listModel = $elementModel->getListModel();
		$item = $listModel->getTable();

		//are we updating the name of the primary key element?
		if ($row->name === FabrikString::shortColName($item->db_primary_key)) {
			if ($name !== $row->name) {
				//yes we are so update the table
				$item->db_primary_key = str_replace($row->name, $name, $item->db_primary_key);
				$item->store();
			}
		}

		$jsons = array('sub_values', 'sub_labels', 'sub_initial_selection');
		foreach ($jsons as $json) {
			if (array_key_exists($json, $data)) {
				$data[$json] = json_encode($data[$json]);
			}
		}

		//only update the element name if we can alter existing columns, otherwise the name and
		//field name become out of sync

		if ($listModel->canAlterFields() || $id == 0) {
			$data['name'] = $name;
		} else {
			$data['name'] = JRequest::getVar('name_orig', '', 'post', 'cmd');
		}

		$ar = array('published', 'use_in_page_title', 'show_in_list_summary', 'link_to_detail', 'can_order', 'filter_exact_match');
		foreach ($ar as $a) {
			if (!array_key_exists($a, $data)) {
				$data[$a] = 0;
			}
		}

		// $$$ rob - test for change in element type
		//(eg if changing from db join to field we need to remove the join
		//entry from the #__{package}_joins table
		// @TODO test this for j1.6
		$elementModel->beforeSave($row);

		//unlink linked elements
		if (JRequest::getVar('unlink') == 'on') {
			$data['parent_id'] = 0;
		}

		$datenow = new JDate();
		if ($row->id != 0) {
			$data['modified'] = $datenow->toMySQL();
			$data['modified_by'] = $user->get('id');
		} else {
			$data['created'] = $datenow->toMySQL();
			$data['created_by'] = $user->get('id');
			$data['created_by_alias'] = $user->get('username');
		}
		$data['params'] = json_encode($params);

		$cond = 'group_id = '.(int)$row->group_id;

		$new = $data['id'] == 0 ? true : false;
		if ($new) {
			$data['ordering'] = $row->getNextOrder($cond);
		}

		$row->reorder($cond);
		$this->updateChildIds($row);

		$elementModel->getElement()->bind($data);

		$origName = JRequest::getVar('name_orig', '', 'post', 'cmd');

		list($update, $q, $oldName, $newdesc, $origDesc) = $listModel->shouldUpdateElement($elementModel, $origName);


		if ($update) {

			$origplugin = JRequest::getVar('plugin_orig');

			$config = JFactory::getConfig();
			$prefix = $config->getValue('dbprefix');

			$tablename = $listModel->getTable()->db_table_name;
			$hasprefix = (strstr($tablename, $prefix) === false) ? false : true;
			$tablename = str_replace($prefix, '#__', $tablename);


			if (in_array($tablename, $this->core)) {
				$app->enqueueMessage(JText::_('COM_FABRIK_WARNING_UPDATE_CORE_TABLE'), 'notice');
			} else {
				if ($hasprefix) {
					$app->enqueueMessage(JText::_('COM_FABRIK_WARNING_UPDATE_TABLE_WITH_PREFIX'), 'notice');
				}
			}
			$app->setUserState('com_fabrik.confirmUpdate', 1);

			$app->setUserState('com_fabrik.plugin_orig', $origplugin);
			$app->setUserState('com_fabrik.q', $q);
			$app->setUserState('com_fabrik.newdesc', $newdesc);
			$app->setUserState('com_fabrik.origDesc', $origDesc);

			$app->setUserState('com_fabrik.origplugin', $origplugin);
			$app->setUserState('com_fabrik.oldname', $oldName);
			$app->setUserState('com_fabrik.origtask', JRequest::getCmd('task'));
			$app->setUserState('com_fabrik.plugin', $data['plugin']);
			$task = JRequest::getCmd('task');
			$app->setUserState('com_fabrik.redirect', 'index.php?option=com_fabrik&view=element&layout=confirmupdate&id='.(int)$row->id."&origplugin=$origplugin&&origtaks=$task&plugin=$row->plugin");

		} else {
			$app->setUserState('com_fabrik.confirmUpdate', 0);
		}

		if ((int)$listModel->getTable()->id !== 0) {
			$this->updateIndexes($elementModel, $listModel, $row);
		}

		$this->updateJavascript($data);
		$return = parent::save($data);
		if ($return) {
			$elementModel->_id = $this->getState($this->getName().'.id');
			$row->id = $elementModel->_id;
			$this->createRepeatElement($elementModel, $row);
			// If new, check if the element's db table is used by other tables and if so add the element
			// to each of those tables' groups

			if ($new) {

				$this->addElementToOtherDbTables($elementModel, $row);
			}

			if (!$elementModel->onSave($data)) {
				$this->setError(JText::_('COM_FABRIK_ERROR_SAVING_ELEMENT_PLUGIN_OPTIONS'));
				return false;
			}
		}
		return $return;
		//used for prefab
		//return $elementModel;
	}

	private function addElementToOtherDbTables($elementModel, $row)
	{
		$db = FabrikWorker::getDbo(true);
		$list = $elementModel->getListModel()->getTable();
		$origElid = $row->id;
		$tmpgroupModel =& $elementModel->getGroup();
		if ($tmpgroupModel->isJoin()) {
			$dbname = $tmpgroupModel->getJoinModel()->getJoin()->table_join;
		} else {
			$dbname = $list->db_table_name;
		}

		$query = $db->getQuery(true);
		$query->select("DISTINCT(l.id), db_table_name, l.id, l.label, l.form_id, l.label AS form_label, g.id AS group_id");
		$query->from("#__{package}_lists AS l");
		$query->join('INNER', '#__{package}_forms AS f ON l.form_id = f.id');
		$query->join('LEFT', '#__{package}_formgroup AS fg ON f.id = fg.form_id');
		$query->join('LEFT', '#__{package}_groups AS g ON fg.group_id = g.id');
		$query->where("db_table_name = ".$db->Quote($dbname)." AND l.id !=".(int)$list->id." AND is_join = 0");

		$db->setQuery($query);
		// $$$ rob load keyed on table id to avoid creating element in every one of the table's group
		$othertables = $db->loadObjectList('id');
		if ($db->getErrorNum() != 0) {
			JError::raiseError(500, $db->getErrorMsg());
		}
		if (!empty($othertables)) {
			// $$$ hugh - we use $row after this, so we need to work on a copy, otherwise
			// (for instance) we redirect to the wrong copy of the element
			$rowcopy = clone($row);
			foreach ($othertables as $listid => $t) {
				$rowcopy->id = 0;
				$rowcopy->parent_id = $origElid;
				$rowcopy->group_id = $t->group_id;
				$rowcopy->name = str_replace('`', '', $rowcopy->name);
				$rowcopy->store();
				//copy join records
				$join = $this->getTable('join');
				if ($join->load(array('element_id' => $origElid))) {
					$join->id = 0;
					unset($join->id);
					$join->element_id = $rowcopy->id;
					$join->list_id = $listid;
					//$join->group_id = $rowcopy->group_id;
					$join->store();
				}

			}
		}
	}

	/**
	 * update child elements
	 * @param object row element
	 * @return mixed
	 */

	private function updateChildIds(&$row)
	{
		if ((int)$row->id === 0)
		{
			//new element so don't update child ids
			return;
		}
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->select('id')->from('#__{package}_elements')->where("parent_id = ".(int)$row->id);
		$db->setQuery($query);
		$objs = $db->loadObjectList();
		$ignore = array('_tbl', '_tbl_key', '_db', 'id', 'group_id', 'created', 'created_by', 'parent_id', 'ordering');
		foreach ($objs as $obj) {
			$item = FabTable::getInstance('Element', 'FabrikTable');
			$item->load($obj->id);
			foreach ($row as $key=>$val) {
				if (!in_array($key, $ignore)) {
					// $$$rob - i can't replicate bug #138 but this should fix things anyway???
					if ($key == 'name') {
						$val = str_replace("`", "", $val);
					}
					$item->$key = $val;
				}
			}
			if (!$item->store()) {
				JError::raiseWarning(500, $item->getError());
			}
		}
		return true;
	}

	/**
	 * update table indexes based on element settings
	 * @param $elementModel
	 * @param $listModel
	 * @param $row
	 * @return unknown_type
	 */

	private function updateIndexes(&$elementModel, &$listModel, &$row)
	{
		if ($elementModel->getGroup()->isJoin()){
			return;
		}
		//update table indexes
		$ftype = $elementModel->getFieldDescription();
		//int elements cant have a index size attrib
		$size = stristr($ftype, 'int') || $ftype == 'DATETIME' ? '' : '10';
		if ($elementModel->getParams()->get('can_order')) {
			$listModel->addIndex($row->name, 'order', 'INDEX', $size);
		} else {
			$listModel->dropIndex($row->name, 'order', 'INDEX', $size);
		}
		if ($row->filter_type != '') {
			$listModel->addIndex($row->name, 'filter', 'INDEX', $size);
		} else {
			$listModel->dropIndex($row->name, 'filter', 'INDEX', $size);
		}
	}

	/**
	 * delete old javascript actions for the element
	 * & add new javascript actions
	 * @param array data to save
	 */

	protected function updateJavascript($data)
	{
		$id = $data['id'];
		$db = FabrikWorker::getDbo(true);
		$db->setQuery("DELETE FROM #__{package}_jsactions WHERE element_id = ".(int)$id);
		$db->query();
		$post = JRequest::get('post');
		if (array_key_exists('js_action', $post['jform']) && is_array($post['jform']['js_action'])) {
			for ($c = 0; $c < count($post['jform']['js_action']); $c ++) {
				$jsAction = $post['jform']['js_action'][$c];
				$params = new stdClass();
				$params->js_e_event = $post['js_e_event'][$c];
				$params->js_e_trigger = $post['js_e_trigger'][$c];
				$params->js_e_condition = $post['js_e_condition'][$c];
				$params->js_e_value = $post['js_e_value'][$c];
				$params = json_encode($params);
				if ($jsAction != '') {
					$code = $post['jform']['js_code'][$c];
					$code = str_replace("}", "}\n", $code);
					$code = str_replace('"', "'", $code);
					$query = $db->getQuery(true);
					$query->insert('#__{package}_jsactions');
					$query->set('element_id = '.(int)$id);
					$query->set('action = '.$db->quote($jsAction));
					$query->set('code = '.$db->quote($code));
					$query->set('params = \''.$params."'");
					$db->setQuery($query);
					$db->query();
				}
			}
		}
	}

	/**
	 * take an array of group ids and return the corresponding element
	 * used in list publish code
	 * @param array group ids
	 * @return array element ids
	 */

	public function swapGroupToElementIds($ids = array())
	{
		if (empty($ids)) {
			return array();
		}
		JArrayHelper::toInteger($ids);
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->select('id')->from('#__{package}_elements')->where('group_id IN ('. implode(',', $ids).')');
		return $db->setQuery($query)->loadResultArray();
	}

	/**
	 *  potentially drop fields then remove element record
	 * @param array $cids to delete
	 */

	public function delete($cids)
	{
		// Initialize variables
		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');
		$drops = (array)JRequest::getVar('drop');
		foreach ($cids as $id) {
			$drop = array_key_exists($id, $drops) && $drops[$id][0] == '1';
			if ((int)$id === 0) {
				continue;
			}
			$pluginModel = $pluginManager->getElementPlugin($id);
			$pluginModel->onRemove($drop);
			$element = $pluginModel->getElement();
			if ($drop) {
				if ($pluginModel->isRepeatElement()) {
					$listModel = $pluginModel->getListModel();
					$db = $listModel->getDb();
					$tableName = $db->nameQuote($this->getRepeatElementTableName($pluginModel));
					$db->setQuery("DROP TABLE $tableName");
					if (!$db->query()) {
						JError::raiseNotice(500, 'didnt drop joined db table '.$tableName);
					}
				}
				$listModel = $pluginModel->getListModel();
				$item = $listModel->getTable();
				// $$$ hugh - might be a tableless form!
				if (!empty($item->id)) {
					$db = $listModel->getDb();
					$db->setQuery("ALTER TABLE ".$db->nameQuote($item->db_table_name)." DROP ".$db->nameQuote($element->name));
					$db->query();
				}
			}
		}
		return parent::delete($cids);
	}

	/**
	 * copy an element
	 * @param int element id
	 */

	function copy()
	{
		$cid = JRequest::getVar('cid', null, 'post', 'array');
		JArrayHelper::toInteger($cid);
		$names = JRequest::getVar('name', null, 'post', 'array');
		//$db = FabrikWorker::getDbo(true);
		$rule	= $this->getTable('element');
		foreach ($cid as $id => $groupid) {
			if ($rule->load((int)$id)) {
				$name = JArrayHelper::getValue($names, $id, $rule->name);
				$elementModel = $this->getElementPluginModel(JArrayHelper::fromObject($rule));
				$newrule = $elementModel->copyRow($id, $rule->label, $groupid, $name);
				$elementModel = $this->getElementPluginModel(JArrayHelper::fromObject($newrule));
				$listModel = $elementModel->getListModel();
				$res = $listModel->shouldUpdateElement($elementModel);
				$this->addElementToOtherDbTables($elementModel, $rule);
			}
			else {
				return JError::raiseWarning(500, $rule->getError());
			}
		}
		return true;
	}

	/**
	 * if repeated element we need to make a joined db table to store repeated data in
	 * @param object element model
	 * @param object element item
	 */

	public function createRepeatElement($elementModel, $row)
	{
		if (!$elementModel->isJoin()) {
			return;
		}
		$row->name = str_replace('`', '', $row->name);
		$listModel = $elementModel->getListModel();
		$tableName = $this->getRepeatElementTableName($elementModel, $row);
		//create db table!
		$formModel = $elementModel->getForm();
		$db = $listModel->getDb();
		$desc = $elementModel->getFieldDescription();
		$name = $db->nameQuote($row->name);
		$db->setQuery("CREATE TABLE IF NOT EXISTS ".$db->nameQuote($tableName)." ( id INT( 6 ) NOT NULL AUTO_INCREMENT PRIMARY KEY, parent_id INT(6), $name $desc, ".$db->nameQuote('params')." TEXT );");
		$db->query();
		if ($db->getErrorNum() != 0) {
			JError::raiseError(500, $db->getErrorMsg());
		}
		//remove previous join records if found
		if ((int)$row->id !== 0) {
			$sql = "DELETE FROM #__{fabrik}_joins WHERE element_id = ".(int)$row->id;
			$jdb = FabrikWorker::getDbo(true);
			$jdb->setQuery($sql);
			$jdb->query();
		}
		//create or update fabrik join
		$data = array('list_id'=>$listModel->getTable()->id,
		'element_id'=>$row->id,
		'join_from_table'=>$listModel->getTable()->db_table_name,
		'table_join'=>$tableName,
		'table_key'=>$row->name,
		'table_join_key'=>'parent_id',
		'join_type'=>'left'
		);
		$join = $this->getTable('join');
		$join->load(array('element_id' => $data['element_id']));

		$opts = new stdClass();
		$opts->type = 'repeatElement';
		$data['params'] = json_encode($opts);
		$join->bind($data);
		$join->store();
	}

	/**
	 * get the name of the repeated elements table
	 * @param object element model
	 * @param object element item
	 * @return string table name
	 */

	protected function getRepeatElementTableName($elementModel, $row = null)
	{
		$listModel =& $elementModel->getListModel();
		if (is_null($row)) {
			$row = $elementModel->getElement();
		}
		$origTableName = $listModel->getTable()->db_table_name;
		return $origTableName . "_repeat_" . str_replace('`', '', $row->name);
	}

	/**
	 * gets the elemetns parent element
	 * @return mixed 0 if no parent, object if exists.
	 */

	public function getParent()
	{
		$item = $this->getItem();
		$item->parent_id = (int)$item->parent_id;
		if ($item->parent_id === 0) {
			$parent = 0;
		} else {
			$db = FabrikWorker::getDbo(true);
			$query = $db->getQuery(true);
			$query->select('*')->from('#__{package}_elements')->where('id = '.(int)$item->parent_id);
			$db->setQuery($query);
			$parent = $db->loadObject();
			if (is_null($parent)) {
				//perhaps the parent element was deleted?
				$parent = 0;
				$item->parent_id = 0;
			}
		}
		return $parent;
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   object  $table  A JTable object.
	 *
	 * @return  array  An array of conditions to add to ordering queries.
	 * @since   Fabrik 3.0b
	 */

	protected function getReorderConditions($table)
	{
		return array('group_id = '.$table->group_id);
	}
}
