<?php

/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');
require_once(COM_FABRIK_FRONTEND.DS.'models'.DS.'parent.php'); //required for fabble

class FabrikPlugin extends JPlugin
{

	/** @var bol determines if the admin settings are visible or hidden when rendered */
	var $_adminVisible = false;

	/** @var string path to xml file **/
	var $_xmlPath = null;

	/** @var object params **/
	protected $_params  = null;

	var $attribs = null;

	var $_id = null;

	var $_row = null;

	/** @var int order that the plugin is rendered */
	var $renderOrder = null;

	protected $_counter;

	protected $_pluginManager = null;

	/** @var object jform */
	public $jform = null;

	function setId($id)
	{
		$this->_id = $id;
	}

	function getName()
	{
		return $this->name;
	}

	/**
	 * Constructor
	 *
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since       1.5
	 */
	public function __construct(& $subject, $config = array())
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * get the JForm object for the plugin
	 * @return object jform
	 */

	function getJForm()
	{
		if (!isset($this->jform)) {
			$type = str_replace('fabrik_', '', $this->_type);
			$formType = $type . '-options';
			$formName = 'com_fabrik.'.$formType;
			//$controlName = 'jform[plugin-options]';
			// $$$ rob - NO! the params option should be set in the plugin fields.xml file <fields name="params">
			// allows for params which update actual db fields
			//$controlName = 'jform[params]';
			$controlName = 'jform';
			$this->jform = new JForm($formName, array('control' => $controlName));
		}
		return $this->jform;
	}

	/**
	 * render the element admin settings
	 * @return string admin html
	 */

	function onRenderAdminSettings($data = array(), $repeatCounter = null)
	{
		$document = JFactory::getDocument();
		$type = str_replace('fabrik_', '', $this->_type);
		//$this->loadLanguage(); //now done in contruct
		JForm::addFormPath(JPATH_SITE.DS.'plugins'.DS.$this->_type.DS.$this->_name);

		$xmlFile = JPATH_SITE.DS.'plugins'.DS.$this->_type.DS.$this->_name.DS.'forms'.DS.'fields.xml';
		$form = $this->getJForm();

		$repeatScript = '';
		// Used by fields when rendering the [x] part of their repeat name
		// see administrator/components/com_fabrik/classes/formfield.php getName()
		$form->repeatCounter = $repeatCounter;
		// Add the plugin specific fields to the form.
		$form->loadFile($xmlFile, false);

		//copy over the data into the params array - plugin fields can have data in either
		//jform[params][name] or jform[name]
		$pluginData = array();
		if (!array_key_exists('params', $data)) {
			$data['params'] = array();
		}
		foreach ($data as $key => $val) {
			$data['params'][$key] = is_array($val) ? JArrayHelper::getValue($val, $repeatCounter) : $val;
		}
		//bind the plugins data to the form
		$form->bind($data);
		//$$$ rob 27/04/2011 - listfields element needs to know things like the group_id, and
		// as bind() onlys saves the values from $data with a corresponding xml field we set the raw data as well
		$form->rawData = $data;
		$str = '';

		$repeatGroupCounter = 0;
		//filer the forms fieldsets for those starting with the correct $serachName prefix
		foreach ($form->getFieldsets() as $fieldset) {
			$class = 'adminform '.$type.'Settings page-'.$this->_name;
			$repeat = isset($fieldset->repeatcontrols) && $fieldset->repeatcontrols == 1;

			//bind data for repeat groups
			$repeatDataMax = 1;
			if ($repeat) {

				$opts = new stdClass();
				$opts->repeatmin = (isset($fieldset->repeatmin)) ? $fieldset->repeatmin : 1;
				$repeatScript[] = "new FbRepeatGroup('$fieldset->name', ".json_encode($opts). ");";

				$repeatData = array();

				foreach ($form->getFieldset($fieldset->name) as $field) {
					if ($repeatDataMax < count($field->value)) {
						$repeatDataMax = count($field->value);
					}
				}
				$form->bind($repeatData);
			}


			$id = isset($fieldset->name) ? ' id="'.$fieldset->name.'"' : '';
			$str .= '<fieldset class="'.$class.'"'.$id.'>';

			$form->repeat = $repeat;
			if ($repeat) {
				$str .= '<a class="addButton" href="#">'.JText::_('COM_FABRIK_ADD').'</a> | ';
			}
			$str .= '
			<legend>'.JText::_($fieldset->label).'</legend>';
			for($r = 0; $r < $repeatDataMax; $r ++) {
				if ($repeat) {
					$str .= '<div class="repeatGroup">';
					$form->repeatCounter = $r;
				}
				$str .= '
			 <ul class="adminformlist">';
				foreach ($form->getFieldset($fieldset->name) as $field) {
					if ($repeat) {
						if (is_array($field->value)) {
							$field->setValue($field->value[$r]);
						}

					}
					$str .= '<li>'. $field->label . $field->input . '</li>';
				}

				if ($repeat) {
					$str .= '<li><a class="removeButton delete" href="#">'.JText::_('COM_FABRIK_REMOVE').'</a></li>';
				}
				$str .= '</ul>';
				if ($repeat) {
					$str .= "</div>";
				}
			}

			$str .= '</fieldset>';
		}
		if (!empty($repeatScript)) {
			$repeatScript = implode("\n", $repeatScript);
			FabrikHelperHTML::script('administrator/components/com_fabrik/models/fields/repeatgroup.js', $repeatScript);
		}
		return $str;
	}

	/**
	 *
	 * used in plugin manager runPlugins to set the correct repeat set of
	 * data for the plugin
	 * @param object original params $params
	 * @param int plugin $repeatCounter
	 */

	function setParams(&$params, $repeatCounter)
	{
		$opts = $params->toArray();
		$data = array();
		foreach ($opts as $key => $val) {
			if (is_array($val)) {
				$data[$key] = JArrayHelper::getValue($val, $repeatCounter);
			}
		}
		$this->_params = new JParameter(json_encode($data));
		return $this->_params;
	}

	/**
	 * load params
	 */

	function &getParams()
	{
		if (!isset($this->_params)) {
			return $this->_loadParams();
		}else{
			return $this->_params;
		}
	}

	function &_loadParams()
	{
		if (!isset($this->attribs)) {
			$row = $this->getRow();
			$a = $row->params;
		} else {
			$a = $this->params;
		}
		if (!isset($this->_params)) {
			$this->_params = new fabrikParams($a, $this->_xmlPath, 'component');
		}
		return $this->_params;
	}

	function getRow()
	{
		if (!isset($this->_row)) {
			$this->_row = $this->getTable($this->_type);
			$this->_row->load($this->_id);
		}
		return $this->_row;
	}

	function setRow($row)
	{
		$this->_row = $row;
	}

	function getTable()
	{
		return FabTable::getInstance('Extension', 'JTable');
	}
	
	/**
	 * determine if we use the plugin or not
	 * both location and event criteria have to be match
	 * @param object calling the plugin table/form
	 * @param string location to trigger plugin on
	 * @param string event to trigger plugin on
	 * @return bol true if we should run the plugin otherwise false
	 */

	function canUse(&$model, $location, $event)
	{
		$ok = false;
		$app = JFactory::getApplication();
		switch ($location) {
			case 'front':
				if (!$app->isAdmin()) {
					$ok = true;
				}
				break;
			case 'back':
				if ($app->isAdmin()) {
					$ok = true;
				}
				break;
			case 'both':
				$ok = true;
				break;
		}
		if ($ok) {
			$k = array_key_exists('_origRowId', $model) ? '_origRowId' : '_rowId';
			switch ($event) {
				case 'new':
					if ($model->$k != 0) {
						$ok = false;
					}
					break;
				case 'edit':
					if ($model->$k == 0) {
						$ok = false;
					}
					break;
			}
		}
		return $ok;
	}

	function customProcessResult()
	{
		return true;
	}

	/**
	 * J1.6 plugin wrapper for ajax_tables
	 */

	function onAjax_tables()
	{
		$this->ajax_tables();
	}


	/**
	 * ajax function to return a string of table drop down options
	 * based on cid variable in query string
	 *
	 */
	function ajax_tables()
	{
		$cid = JRequest::getInt('cid', -1);
		$rows = array();
		$showFabrikLists = JRequest::getVar('showf', false);
		if ($showFabrikLists) {
			$db = FabrikWorker::getDbo(true);
			if ($cid !== 0) {
				$sql = "SELECT id, label FROM #__{package}_lists WHERE connection_id = $cid ORDER BY label ASC";
				$db->setQuery($sql);
				$rows = $db->loadObjectList();
			}
			$default = new stdClass;
			$default->id = '';
			$default->label = JText::_('COM_FABRIK_PLEASE_SELECT');
			array_unshift($rows, $default);
		} else {
			if ($cid !== 0) {
				$cnn = JModel::getInstance('Connection', 'FabrikFEModel');
				$cnn->setId($cid);
				$db = $cnn->getDb();
				$db->setQuery("SHOW TABLES");
				$rows = (array)$db->loadColumn();
			}
			array_unshift($rows, '');
		}
		echo json_encode($rows);
	}

	/**
	 * J1.6 plugin wrapper for ajax_fields
	 */

	function onAjax_fields()
	{
		$this->ajax_fields();
	}

	function ajax_fields()
	{
		$tid = JRequest::getVar('t');
		$keyType = JRequest::getVar('k', 1);
		$showAll = JRequest::getVar('showall', false);//if true show all fields if false show fabrik elements

		//only used if showall = false, includes validations as separate entries
		$incCalculations = JRequest::getVar('calcs', false);
		$arr = array();
		if ($showAll) { //show all db columns
			$cid = JRequest::getVar('cid', -1);
			$cnn = JModel::getInstance('Connection', 'FabrikFEModel');
			$cnn->setId($cid);
			$db = $cnn->getDb();
			if ($tid != '') {
				if (is_numeric($tid)) { //if loading on a numeric list id get the list db table name
					$query = $db->getQuery(true);
					$query->select('db_table_name')->from('#__{package}_lists')->where('id = ' . (int)$tid);
					$db->setQuery($query);
					$tid = $db->loadResult();
				} 
				$db->setQuery("DESCRIBE ".$db->nameQuote($tid));

				$rows = $db->loadObjectList();
				if (is_array($rows)) {
					foreach ($rows as $r) {
						$c = new stdClass();
						$c->value = $r->Field;
						$c->label = $r->Field;
						$arr[$r->Field] = $c;
					}
					ksort($arr);
					$arr = array_values($arr);
				}
			}
		} else {
			//show fabrik elements in the table
			//$keyType 1 = $element->id;
			//$keyType 2 = tablename___elementname
			$model = JModel::getInstance('List', 'FabrikFEModel');
			$model->setId($tid);
			$table = $model->getTable();
			$groups = $model->getFormGroupElementData();
			$published = JRequest::getVar('published', false);
			$showintable = JRequest::getVar('showintable', false);
			foreach ($groups as $g => $groupModel) {
				if ($groupModel->isJoin()) {
					if (JRequest::getVar('excludejoined') == 1) {
						continue;
					}
					$joinModel = $groupModel->getJoinModel();
					$join = $joinModel->getJoin();
				}
				if ($published == true) {
					$elementModels = $groups[$g]->getPublishedElements();
				} else {
					$elementModels = $groups[$g]->getMyElements();
				}

				foreach ($elementModels as $e => $eVal) {
					$element = $eVal->getElement();
					if ($showintable == true && $element->show_in_list_summary == 0) {
						continue;
					}
					if ($keyType == 1) {
						$v = $element->id;
					} else {
						//@TODO if in repeat group this is going to add [] to name - is this really
						// what we want? In timeline viz options i've simply stripped out the [] off the end
						// as a temp hack
						$v = $eVal->getFullName(false);
					}
					$c = new stdClass();
					$c->value = $v;
					$label = FabrikString::getShortDdLabel( $element->label);
					if ($groupModel->isJoin()) {
						$label = $join->table_join.'.'.$label;
					}
					$c->label = $label;
					$arr[] = $c; //dont use =
					if ($incCalculations) {
						$params = $eVal->getParams();
						if ($params->get('sum_on', 0)) {
							$c = new stdClass();
							$c->value = 'sum___'.$v;
							$c->label = JText::_('COM_FABRIK_SUM') . ": " .$label;
							$arr[] = $c; //dont use =
						}
						if ($params->get('avg_on', 0)) {
							$c = new stdClass();
							$c->value = 'avg___'.$v;
							$c->label = JText::_('COM_FABRIK_AVERAGE') . ": " .$label;
							$arr[] = $c; //dont use =
						}
						if ($params->get('median_on', 0)) {
							$c = new stdClass();
							$c->value = 'med___'.$v;
							$c->label = JText::_('COM_FABRIK_MEDIAN') . ": " .$label;
							$arr[] = $c; //dont use =
						}
						if ($params->get('count_on', 0)) {
							$c = new stdClass();
							$c->value = 'cnt___'.$v;
							$c->label = JText::_('COM_FABRIK_COUNT') . ": " .$label;
							$arr[] = $c; //dont use =
						}
						if ($params->get('custom_calc_on', 0)) {
							$c = new stdClass();
							$c->value = 'cnt___'.$v;
							$c->label = JText::_('COM_FABRIK_CUSTOM') . ": " .$label;
							$arr[] = $c; //dont use =
						}
					}
				}
			}
		}
		array_unshift($arr, JHTML::_('select.option', '', JText::_('COM_FABRIK_PLEASE_SELECT'), 'value', 'label'));
		echo json_encode($arr);
	}

	public function onGetAdminJs($name, $label, $html)
	{
		$opts = $this->getAdminJsOpts($html);
		$opts = json_encode($opts);
		$script = "new fabrikAdminPlugin('$name', '$label', $opts)";
		return $script;
	}

	protected function getAdminJsOpts($html)
	{
		$opts = new stdClass();
		$opts->livesite = COM_FABRIK_LIVESITE;
		$opts->html = $html;
		return $opts;
	}

	/**
	 * if true then the plugin is stating that any subsequent plugin in the same group
	 * should not be run.
	 * @param string current plug-in call method e.g. onBeforeStore
	 * @return bool
	 */

	public function runAway($method)
	{
		return false;
	}

	/**
	 * process the plugin, called when form is submitted
	 *
	 * @param string param name which contains the PHP code to eval
	 * @param array data
	 */

	function shouldProcess($paramName, $data = null)
	{
		if (is_null($data)) {
			$data = $this->data;
		}
		$params = $this->getParams();
		$condition = $params->get($paramName);
		if (trim($condition) == '') {
			return true;
		}
		$w = new FabrikWorker();
		$condition = trim($w->parseMessageForPlaceHolder($condition, $data));
		$res = @eval($condition);
		if (is_null($res)) {
			return true;
		}
		return $res;
	}

	function replace_num_entity($ord)
	{
		$ord = $ord[1];
		if (preg_match('/^x([0-9a-f]+)$/i', $ord, $match)) {
			$ord = hexdec($match[1]);
		} else {
			$ord = intval($ord);
		}
		$no_bytes = 0;
		$byte = array();
		if ($ord < 128) {
			return chr($ord);
		}
		elseif ($ord < 2048)
		{
			$no_bytes = 2;
		}
		elseif ($ord < 65536)
		{
			$no_bytes = 3;
		}
		elseif ($ord < 1114112)
		{
			$no_bytes = 4;
		}
		else
		{
			return;
		}

		switch($no_bytes)
		{
			case 2:
				{
					$prefix = array(31, 192);
					break;
				}
			case 3:
				{
					$prefix = array(15, 224);
					break;
				}
			case 4:
				{
					$prefix = array(7, 240);
				}
		}
		for ($i = 0; $i < $no_bytes; $i++) {
			$byte[$no_bytes - $i - 1] = (($ord & (63 * pow(2, 6 * $i))) / pow(2, 6 * $i)) & 63 | 128;
		}
		$byte[0] = ($byte[0] & $prefix[0]) | $prefix[1];
		$ret = '';
		for ($i = 0; $i < $no_bytes; $i++) {
			$ret .= chr($byte[$i]);
		}
		return $ret;
	}

	/**
	 * @since 3.0
	 * get the plugin manager
	 * @return plugin manager
	 */

	protected function getPluginManager()
	{
		if (!isset($this->_pluginManager)) {
			$this->_pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');
		}
		return $this->_pluginManager;
	}
}
?>