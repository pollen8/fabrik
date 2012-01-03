<?php
/**
 * Plugin element to render dropdown list to select user
 * @package fabrikar
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once(JPATH_SITE.DS.'plugins'.DS.'fabrik_element'.DS.'databasejoin'.DS.'databasejoin.php');

class plgFabrik_ElementUser extends plgFabrik_ElementDatabasejoin
{

	/** @var bol is a join element */
	var $_isJoin = true;

	protected $fieldDesc = 'INT(11)';

	/**
	 * bit of a hack to set join_db_name in params
	 * @return params
	 */

	function &getParams()
	{
		$params = parent::getParams();
		if (empty($params->join_db_name)) {
			$params->set('join_db_name', '#__users');
		}
		return $params;
	}

	/**
	 * draws the form element
	 * @param int repeat group counter
	 * @return string returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		$element = $this->getElement();
		$name = $this->getHTMLName($repeatCounter);
		$html_id = $this->getHTMLId($repeatCounter);
		$id = $html_id;
		$params = $this->getParams();

		// $$$ rob - if embedding a form inside a details view then rowid is true (for the detailed view) but we are still showing a new form
		// instead take a look at the element form's _rowId;
		//$rowid = JRequest::getVar('rowid', false);
		$rowid = $this->getForm()->_rowId;
		//@TODO when editing a form with joined repeat group the rowid will be set but
		//the record is in fact new
		if ($params->get('update_on_edit') || !$rowid || ($this->_inRepeatGroup && $this->_inJoin &&  $this->_repeatGroupTotal == $repeatCounter)) {
			//set user to logged in user
			if ($this->_editable) {
				$user = JFactory::getUser();
			} else {
				$user = JFactory::getUser((int)$this->getValue($data, $repeatCounter));
			}
		} else {
			// $$$ hugh - this is blowing away the userid, as $element->default is empty at this point
			// so for now I changed it to the $data value
			//keep previous user
			//$user  		=& JFactory::getUser((int)$element->default);

			// $$$ hugh ... what a mess ... of course if it's a new form, $data doesn't exist ...
			if (empty($data)) {
				// if $data is empty, we must (?) be a new row, so just grab logged on user
				$user = JFactory::getUser();
			}
			else {
				//$$$ rob - changed from $name to $id as if your element is in a repeat group name as "[]" at the end
				//$user  		=& JFactory::getUser((int)$data[$name . '_raw']);
				if ($this->_inDetailedView) {
					//$id = FabrikString::rtrimWord($id, "_ro");
					$id = preg_replace('#_ro$#', '_raw', $id);
				}
				else {
					// $$$ rob 31/07/2011 not sure this is right - causes js error when field is hidden in form
					// $$$ hugh 10/31/2011 - but if we don't do it, $id is the label not the value (like 'username')
					// so wrong uid is written to form, and wipes out real ID when form is submitted.
					// OK, problem was we were using $id firther on as the html ID, so if we added _raw, element
					// on form had wrong ID.  Added $html_id above, to use as (duh) html ID instead of $id.
					if (!strstr($id,'_raw') && array_key_exists($id . '_raw', $data)) {
						$id .= '_raw';
					}
				}
				$uid = JArrayHelper::getValue($data, $id, '');
				if ($uid === '') {
					$uid = $this->getValue($data, $repeatCounter);
				}
				$user = JFactory::getUser((int)$uid);
			}
		}

		// if the table database is not the same as the joomla database then
		// we should simply return a hidden field with the user id in it.
		if (!$this->inJDb()) {

			return $this->_getHiddenField($name, $user->get('id'), $html_id);
		}
		$str = '';
		if ($this->_editable) {
			$value = $user->get('id');
			if ($element->hidden) {
				$str = $this->_getHiddenField($name, $value, $html_id);
			} else {
				$str = parent::render($data, $repeatCounter);
			}
		} else {
			$displayParam = $this->_getValColumn();
			if (is_a($user, 'JUser')) {
				$str = $user->get($displayParam);
			} else {
				JError::raiseWarning(E_NOTICE, "didnt load for $element->default");
			}
		}
		return $str;
	}

	/**
	 * get element's hidden field
	 *
	 * @access private
	 * @param string $name
	 * @param string $value
	 * @param string $id
	 * @return strin
	 */
	function _getHiddenField($name, $value, $id )
	{
		return "<input class='fabrikinput inputbox' type='hidden' name='$name' value='$value' id='$id' />\n";
	}

	/**
	 * if the table db isnt the same as the joomla db the element
	 * will be rendered as a hidden field so return true from isHidden()
	 *
	 * @return bol
	 */

	function isHidden()
	{
		if ($this->inJDb()) {
			return parent::isHidden();
		} else {
			return true;
		}
	}

	/**
	 * if we are creating a new record, and the element was set to readonly
	 * then insert the users data into the record to be stored
	 *
	 * @param unknown_type $data
	 */

	function onStoreRow(&$data)
	{
		// $$$ hugh - special case, if we have just run the fabrikjuser plugin, we need to
		// use the 'newuserid' as set by the plugin.
		$newuserid = JRequest::getInt('newuserid', 0);
		if (!empty($newuserid)) {
			$newuserid_element = JRequest::getVar('newuserid_element', '');
			$this_fullname = $this->getFullName(false, true, false);
			if ($newuserid_element == $this_fullname) {
				return;
			}
		}
		$element = $this->getElement();
		// $$$ rob if in joined data then $data['rowid'] isnt set - use JRequest var instead
		//if ($data['rowid'] == 0 && !in_array($element->name, $data)) {
		// $$$ rob also check we aren't importing from CSV - if we are ingore
		if (JRequest::getInt('rowid') == 0 && JRequest::getCmd('task') !== 'doimport') {

			// $$$ rob if we cant use the element or its hidden force the use of current logged in user
			if (!$this->canUse() || $this->getElement()->hidden == 1) {
				$user		=& JFactory::getUser();
				$data[$element->name] = $user->get('id');
				$data[$element->name . '_raw'] = $data[$element->name];
			}
		}
		// $$$ hugh
		// If update-on-edit is set, we always want to store as current user??

		// $$$ rob NOOOOOO!!!!! - if its HIDDEN OR set to READ ONLY then yes
		// otherwise selected dropdown option is not taken into account

		// $$$ hugh - so how come we don't do the same thing on a new row?  Seems inconsistant to me?
		else {
			$params = $this->getParams();
			if ($params->get('update_on_edit',0)) {
				if (!$this->canUse() || $this->getElement()->hidden == 1) {
					$user		=& JFactory::getUser();
					$data[$element->name] = $user->get('id');
					$data[$element->name . '_raw'] = $data[$element->name];
				}
			}
		}
	}

	/**
	 * when processing the form, we always want to store the current userid
	 * (subject to save-on-edit, but that's done elsewhere), regardless of
	 * element access settings, see:
	 * http://fabrikar.com/forums/showthread.php?p=70554#post70554
	 * So overriding the element model canView and returning true in that
	 * case allows _addDefaultDataFromRO to do that, whilst still enforcing
	 * Read Access settings for detail/table view
	 */

	function canView()
	{
		if (JRequest::getVar('task', '') == 'processForm') {
			return true;
		}
		return parent::canView();
	}

	/**
	 * shows the data formatted for the table view
	 * @param string data
	 * @param object all the data in the tables current row
	 * @return string formatted value
	 */

	function renderListData($data, $oAllRowsData)
	{
		return parent::renderListData($data, $oAllRowsData);
	}

	/**
	 * get js ini code
	 * overwritten in plugin classes
	 * @param int repeat group counter
	 */

	function elementJavascript($repeatCounter)
	{
		$opts = parent::elementJavascriptOpts($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		return "new FbUser('$id', $opts)";
	}

	/**
	 * load the javascript class that manages interaction with the form element
	 * should only be called once
	 * @return string javascript class file
	 */

	function formJavascriptClass(&$srcs)
	{
		plgFabrik_Element::formJavascriptClass($srcs, 'plugins/fabrik_element/databasejoin/databasejoin.js');
		parent::formJavascriptClass($srcs);
	}

	protected function _getSelectLabel()
	{
		return $this->getParams()->get('user_noselectionlabel', JText::_('COM_FABRIK_PLEASE_SELECT'));
	}

	/**
	 * can be overwritten in the plugin class - see database join element for example
	 * @param array containing field sql
	 * @param array containing field aliases
	 * @param array options
	 */

	function getAsField_html(&$aFields, &$aAsFields, $opts = array())
	{
		$table = $this->actualTableName();
		$element 	= $this->getElement();
		$params 	= $this->getParams();

		$fullElName = JArrayHelper::getValue($opts, 'alias', $table . "___" . $element->name);

		//check if main database is the same as the elements database
		if ($this->inJDb()) {
			//it is so continue as if it were a database join
			//make sure same connection as this table

			$join = $this->getJoin();
			//$$$ rob in csv import keytable not set
			$k = isset($join->keytable ) ? $join->keytable : $join->join_from_table;
			$k = FabrikString::safeColName("`$k`.`$element->name`");
			$k2 = FabrikString::safeColName($this->getJoinLabelColumn());
			if (JArrayHelper::getValue($opts, 'inc_raw', true)) {
				$aFields[]				= "$k AS `$fullElName" . "_raw`";
				$aAsFields[]			= "`$fullElName". "_raw`";
			}
			$aFields[] 				= "$k2 AS `$fullElName`";
			$aAsFields[] 			= "`$fullElName`";
		} else {
			$k = FabrikString::safeColName("`$table`.`$element->name`");
			//its not so revert back to selecting the id
			$aFields[]				= "$k AS `$fullElName" . "_raw`";
			$aAsFields[]			= "`$fullElName". "_raw`";
			$aFields[]				= "$k AS `$fullElName`";
			$aAsFields[]			= "`$fullElName`";
		}
	}

	/**
	 * called when the element is saved
	 * // @TODO this *MIGHT* work for j1.6 - still need to test it
	 */

	function onSave($data)
	{
		$params = json_decode($data['params']);
		if (!$this->canEncrypt() && !empty($params->encrypt)) {
			JError::raiseNotice(500, 'The encryption option is only available for field and text area plugins');
			return false;
		}
		$label = (isset($params->my_table_data) && $params->my_table_data !== '') ? $params->my_table_data : 'username';
		$this->updateFabrikJoins($data, '#__users', 'id', $label);
		return true;
	}


	protected function getJoinLabel()
	{
		$label = parent::getJoinLabel();
		if ($label == 'gid') {
			$label = 'username';
		}
		return $label;
	}

	/**
	 * this really does get just the default value (as defined in the element's settings)
	 * @return unknown_type
	 */

	function getDefaultValue($data = array() )
	{
		if (!isset($this->_default)) {
			$user = JFactory::getUser();
			$this->_default = $user->get('id');
		}
		return $this->_default;
	}

	/**
	 * get the value
	 *
	 * @param array $data
	 * @param int $repeatCounter
	 * @param array options
	 * @return unknown
	 */

	function getValue($data, $repeatCounter = 0, $opts = array() )
	{

		//cludge for 2 scenarios
		if (array_key_exists('rowid', $data)) {
			//when validating the data on form submission
			$key = 'rowid';
		} else {
			//when rendering the element to the form
			$key = '__pk_val';
		}

		//empty(data) when you are saving a new record and this element is in a joined group
		// $$$ hugh - added !array_key_exists(), as ... well, rowid doesn't always exist in the query string

		// $$$ rob replaced ALL references to rowid with __pk_val as rowid doesnt exists in the data :O

		//$$$ rob
		//($this->_inRepeatGroup && $this->_inJoin &&  $this->_repeatGroupTotal == $repeatCounter)
		//is for saying that the last record in a repeated join group should be treated as if it was in a new form

		// $$$ rob - erm why on earth would i want to do that! ?? (see above!) - test case:
		// form with joined data - make record with on repeated group (containing this element)
		// edit record and the commented out if statement below meant the user dd reverted
		// to the current logged in user and not the previously selected one
		if (empty($data) || !array_key_exists($key, $data) || (array_key_exists($key, $data) && empty($data[$key])) ) {
			//if (empty($data) || !array_key_exists($key, $data) || (array_key_exists($key, $data) && empty($data[$key])) || ($this->_inRepeatGroup && $this->_inJoin &&  $this->_repeatGroupTotal == $repeatCounter)) {
			//new record
			//$$$ rob huh - whats with this else statement - the code is the same for both???
	  // $$$ hugh - I was chasing a bug with user elements in joined data, but this bit was a blind alley
	  // just forgot to get rid of it.
			/*if($this->_inRepeatGroup && $this->_inJoin &&  $this->_repeatGroupTotal == $repeatCounter && $this->_editable) {

			$user = JFactory::getUser();
			// $$$ hugh - need to actually set $this->default
			$element = $this->getElement();
			$element->default = $user->get('id');
			return $element->default;
			}else{
			$user = JFactory::getUser();
			// $$$ hugh - need to actually set $this->default
			$element = $this->getElement();
			$element->default = $user->get('id');
			return $element->default;
			}*/
			// 	$$$rob - if no search form data submitted for the search element then the default
			// selection was being applied instead
			// $$$ rob - added check on task to ensure that we are searching and not submitting a form
			// as otherwise not empty valdiation failed on user element
			if (JArrayHelper::getValue($opts, 'use_default', true) == false && !in_array(JRequest::getCmd('task'), array('processForm', 'view'))) {
				return '';
			} else {
				return $this->getDefaultValue($data);
			}
		}
		$res = parent::getValue($data, $repeatCounter, $opts);
		return $res;
	}

	/**
	 * Get the table filter for the element
	 * @param bol do we render as a normal filter or as an advanced searc filter
	 * if normal include the hidden fields as well (default true, use false for advanced filter rendering)
	 * @return string filter html
	 */

	function getFilter($counter = 0, $normal = true)
	{
		$listModel = $this->getlistModel();
		$formModel = $listModel->getFormModel();
		$elName2 = $this->getFullName(false, false, false);
		if (!$formModel->hasElement($elName2)) {
			return '';
		}
		$table = $listModel->getTable();
		$element	= $this->getElement();
		$params	= $this->getParams();

		$elName = $this->getFullName(false, true, false);
		$htmlid	= $this->getHTMLId() . 'value';
		$v = $this->filterName($counter, $normal);

		//corect default got
		$default = $this->getDefaultFilterVal($normal, $counter);
		$return = array();
		$tabletype = $this->_getValColumn();
		$join = $this->getJoin();
		$joinTableName = FabrikString::safeColName($join->table_join_alias);
		// if filter type isn't set was blowing up in switch below 'cos no $rows
		// so added '' to this test.  Should probably set $element->filter_type to a default somewhere.
		if (in_array($element->filter_type, array('range', 'dropdown', ''))) {
			$rows = $this->filterValueList($normal, '', $joinTableName.'.'.$tabletype, '', false);
			$rows = (array)$rows;
			array_unshift($rows, JHTML::_('select.option',  '', $this->filterSelectLabel()));
		}

		switch ( $element->filter_type )
		{
			case "range":
				$attribs = 'class="inputbox fabrik_filter" size="1" ';
				$default1 = is_array($default) ? $default[0] : '';
				$return[] = JHTML::_('select.genericlist', $rows , $v.'[]', $attribs, 'value', 'text', $default1, $element->name . "_filter_range_0");
				$default1 = is_array($default) ? $default[1] : '';
				$return[] = JHTML::_('select.genericlist', $rows , $v.'[]', $attribs, 'value', 'text', $default1 , $element->name . "_filter_range_1");
				break;
			case "dropdown":
			default:
				$return[] = JHTML::_('select.genericlist',  $rows , $v, 'class="inputbox fabrik_filter" size="1" ', 'value', 'text', $default, $htmlid);
				break;

			case "field":
				if (get_magic_quotes_gpc()) {
					$default = stripslashes($default);
				}
				$default = htmlspecialchars($default);
				$return[] = '<input type="text" name="'.$v.'" class="inputbox fabrik_filter" value="'.$default.'" id="'.$htmlid.'" />';
				break;
				
				case "hidden":
					if (get_magic_quotes_gpc()) {
						$default = stripslashes($default);
					}
					$default = htmlspecialchars($default);
					$return[] = '<input type="hidden" name="'.$v.'" class="inputbox fabrik_filter" value="'.$default.'" id="'.$htmlid.'" />';
					break;

			case "auto-complete":
				if (get_magic_quotes_gpc()) {
					$default = stripslashes($default);
				}
				$default = htmlspecialchars($default);
				$return[] = '<input type="hidden" name="'.$v.'" class="inputbox fabrik_filter" value="'.$default.'" id="'.$htmlid.'" />';
				$return[] = '<input type="text" name="'.$v.'-auto-complete" class="inputbox fabrik_filter autocomplete-trigger" value="'.$default.'" id="'.$htmlid.'-auto-complete" />';
				$selector = '#listform_'.$listModel->getRenderContext().' .'.$id;
				FabrikHelperHTML::autoComplete($selector, $this->getElement()->id, 'user');
				break;
		}
		if ($normal) {
			$return[] = $this->getFilterHiddenFields($counter, $elName);
		} else {
			$return[] = $this->getAdvancedFilterHiddenFields();
		}
		return implode("\n", $return);
	}

	/**
	 * (non-PHPdoc)
	 * @see components/com_fabrik/models/plgFabrik_Element::_buildFilterJoin()
	 */

	protected function _buildFilterJoin()
	{
		$params 			=& $this->getParams();
		$joinTable 	= FabrikString::safeColName($params->get('join_db_name'));
		$join = $this->getJoin();
		$joinTableName  	=  FabrikString::safeColName($join->table_join_alias);
		$joinKey		= $this->getJoinValueColumn();
		$elName 			= FabrikString::safeColName($this->getFullName(false, true, false));
		return 'INNER JOIN '.$joinTable.' AS '.$joinTableName.' ON '.$joinKey.' = '.$elName;
	}

	/**
	 * build the filter query for the given element.
	 * @param $key element name in format `tablename`.`elementname`
	 * @param $condition =/like etc
	 * @param $value search string - already quoted if specified in filter array options
	 * @param $originalValue - original filter value without quotes or %'s applied
	 * @param string filter type advanced/normal/prefilter/search/querystring/searchall
	 * @return string sql query part e,g, "key = value"
	 */

	function getFilterQuery($key, $condition, $value, $originalValue, $type = 'normal')
	{
		if (!$this->inJDb()) {
			return "$key $condition $value";
		}
		$element = $this->getElement();
		// $$$ hugh - we need to use the join alias, not hard code #__users
		$join = $this->getJoin();
		$joinTableName  =  $join->table_join_alias;
		if (empty($joinTableName)) {
			$joinTableName = '#__users';
		}
		if ($type == 'querystring') {
			$key = FabrikString::safeColNameToArrayKey($key);
			// $$$ rob no matter whether you use elementname_raw or elementname in the querystring filter
			// by the time it gets here we have normalized to elementname. So we check if the original qs filter was looking at the raw
			// value if it was then we want to filter on the key and not the label
			if (!array_key_exists($key, JRequest::get('get'))) {
				$key = "`$joinTableName`.`id`";
				$this->encryptFieldName($key);
				return "$key $condition $value";
			}
		}
		if ($type == 'advanced') {
			$key = "`$joinTableName`.`id`";
			$this->encryptFieldName($key);
			return "$key $condition $value";
		}
		$params = $this->getParams();

		if ($type != 'prefilter') {
			switch ($element->filter_type) {
				case 'range':
				case 'dropdown':
					$tabletype = 'id';
					break;
				case 'field':
				default:
					$tabletype = $this->_getValColumn();
					break;
			}
			$k = '`' . $joinTableName . '`.`' . $tabletype.'`';
		} else {
			if ($this->_rawFilter) {
				$k = '`' . $joinTableName . '`.`id`';
			}else{
				$tabletype = $this->_getValColumn();
				$k = '`' . $joinTableName . '`.`' . $tabletype.'`';
			}
		}
		$this->encryptFieldName($k);
		$str = "$k $condition $value";
		return $str;
	}

	/**
	 * get database object for the user element
	 * (non-PHPdoc)
	 * @see /plugins/fabrik_element/databasejoin/FabrikModelFabrikDatabasejoin#getDb()
	 */

	function getDb()
	{
		return FabrikWorker::getDbo(true);
	}

	/**
	 * used to format the data when shown in the form's email
	 * @param mixed element's data
	 * @param array form records data
	 * @param int repeat group counter
	 * @return string formatted value
	 */

	function getEmailValue($value, $data, $c)
	{
		$key = $this->getFullName(false, true, false) . "_raw";
		$userid = $data[$key];
		if (is_array($userid)) {
			$userid = (int)array_shift($userid);
		}
		$user = JFactory::getUser($userid);

		return $this->getUserDisplayProperty($user);
	}

	/**
	 * @since 3.0b
	 * get the user's property to show, if gid raise warning and revert to username (no gid in J1.7)
	 * @param object $user
	 */

	protected function getUserDisplayProperty($user)
	{
		static $displayMessage;
		$params = $this->getParams();
		$displayParam = $this->_getValColumn();
		return $user->get($displayParam);
	}

	function getJoinValueColumn()
	{
		$params = $this->getParams();
		$join = $this->getJoin();
		$db = FabrikWorker::getDbo();
		return $db->nameQuote($join->table_join_alias).'.id';
	}

	/**
	 * used for the name of the filter fields
	 * Over written here as we need to get the label field for field searches
	 *
	 * @return string element filter name
	 */

	function getFilterFullName()
	{
		$elName = $this->getFullName(false, true, false);
		return FabrikString::safeColName($elName);
	}

	/**
	 * called when copy row table plugin called
	 * @param mixed value to copy into new record
	 * @return mixed value to copy into new record
	 */

	public function onCopyRow($val)
	{
		$params = $this->getParams();
		if ($params->get('update_on_edit')) {
			$user = JFactory::getUser();
			$val = $user->get('id');
		}
		return $val;
	}

	/**
	 * called when save as copy form button clicked
	 * @param mixed value to copy into new record
	 * @return mixed value to copy into new record
	 */

	public function onSaveAsCopy($val)
	{
		$params = $this->getParams();
		if ($params->get('update_on_copy', false)) {
			$user = JFactory::getUser();
			$val = $user->get('id');
		}
		return $val;
	}

	/**
	 * get the element name or concat statement used to build the dropdown labels or
	 * table data field
	 *
	 * @return string
	 */

	function _getValColumn()
	{
		static $displayMessage;
		$params = $this->getParams();
		$displayParam = $params->get('my_table_data', 'username');
		if ($displayParam == 'gid') {
			$displayParam == 'username';
			if (!isset($displayMessage)) {
				JError::raiseNotice(200, 'The user plugin (id = '. $this->getElement()->id.') uses the defunct gid property. Please edit it and change it');
				$displayMessage = true;
			}
		}
		return $displayParam;
	}
}
?>