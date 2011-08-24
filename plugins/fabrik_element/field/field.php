<?php
/**
 * Plugin element to render fields
 * @package fabrikar
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

class plgFabrik_ElementField extends plgFabrik_Element
{

	/**
	 * shows the data formatted for the table view
	 * @param string data
	 * @param object all the data in the tables current row
	 * @return string formatted value
	 */

	function renderListData($data, $oAllRowsData)
	{
		$params =& $this->getParams();
		$data = $this->numberFormat($data);
		$format = $params->get('text_format_string');
		if ($format  != '') {
			$data = sprintf($format, $data);
		}
		if ($params->get('password') == "1") {
			$data = str_pad('', strlen($data), '*');
		}
		$this->_guessLinkType($data, $oAllRowsData, 0);
		return parent::renderListData($data, $oAllRowsData);
	}

	/**
	 * determines if the element can contain data used in sending receipts, e.g. field returns true
	 */

	function isReceiptElement()
	{
		return true;
	}

	/**
	 * draws the form element
	 * @param array data to preopulate element with
	 * @param int repeat group counter
	 * @return string returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		$name 			= $this->getHTMLName($repeatCounter);
		$id 				= $this->getHTMLId($repeatCounter);
		$params 		=& $this->getParams();
		$element 		= $this->getElement();
		$size 			= $element->width;
		$maxlength  = $params->get('maxlength');
		if ($maxlength == "0" or $maxlength == "") {
			$maxlength = $size;
		}
		$bits = array();
		// $$$ rob - not sure why we are setting $data to the form's data
		//but in table view when getting read only filter value from url filter this
		// _form_data was not set to no readonly value was returned
		// added little test to see if the data was actually an array before using it
		if (is_array($this->_form->_data)) {
			$data 	=& $this->_form->_data;
		}
		$value 	= $this->getValue($data, $repeatCounter);
		$type = $params->get('password') == "1" ?"password" : "text";
		if (isset($this->_elementError) && $this->_elementError != '') {
			$type .= " elementErrorHighlight";
		}
		if ($element->hidden == '1') {
			$type = "hidden";
		}
		// $$$ hugh - if the form just failed validation, number formatted fields will already
		// be formatted, so we need to un-format them before formatting them!
		$value = $this->numberFormat($this->unNumberFormat($value));
		if (!$this->_editable) {
			$this->_guessLinkType($value, $data, $repeatCounter);
			//$value = $this->numberFormat($value);
			$format = $params->get('text_format_string');
			if ($format != '') {
				//$value =  eval(sprintf($format,$value));
				//not sure why this was being evald??
				$value =  sprintf($format, $value);
			}
			if ($params->get('password') == "1") {
				$value = str_pad('', strlen($value), '*');
			}
			return($element->hidden == '1') ? "<!-- " . $value . " -->" : $value;
		}

		$bits['class'] = "fabrikinput inputbox $type";
		$bits['type']		= $type;
		$bits['name']		= $name;
		$bits['id']			= $id;
		if ($params->get('autocomplete', 1) == 0) {
			$bits['autocomplete'] = 'off';
		}
		//stop "'s from breaking the content out of the field.
		// $$$ rob below now seemed to set text in field from "test's" to "test&#039;s" when failed validation
		//so add false flag to ensure its encoded once only
		// $$$ hugh - the 'double encode' arg was only added in 5.2.3, so this is blowing some sites up
		if (version_compare( phpversion(), '5.2.3', '<')) {
			$bits['value']		= htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
		}
		else {
			$bits['value']		= htmlspecialchars($value, ENT_COMPAT, 'UTF-8', false);
		}
		$bits['size']		= $size;
		$bits['maxlength']	= $maxlength;

		//cant be used with hidden element types
		if ($element->hidden != '1') {
			if ($params->get('readonly')) {
				$bits['readonly'] = "readonly";
				$bits['class'] .= " readonly";
			}
			if ($params->get('disable')) {
				$bits['class'] .= " disabled";
				$bits['disabled'] = 'disabled';
			}
		}
		$str = "<input ";
		foreach ($bits as $key=>$val) {
			$str.= "$key = \"$val\" ";
		}
		$str .= " />\n";
		return $str;
	}

	/**
	 * format guess link type
	 *
	 * @param string $value
	 * @param array data
	 * @param int repeat counter
	 */

	function _guessLinkType(&$value, $data, $repeatCounter = 0)
	{
		$params =& $this->getParams();
		$guessed = false;
		if ($params->get('guess_linktype') == '1') {
			jimport('joomla.mail.helper');
			if (JMailHelper::isEmailAddress($value)) {
				$value = JHTML::_('email.cloak', $value);
				$guessed = true;
			}
			// Changes JF Questiaux
			else if (JString::stristr($value, 'http')) {
				if ($params->get('link_target_field', 1) == '1') {
					$target = $params->get('link_target_options', 'default');
					$value = "<a href=\"$value\" target=\"$target\">$value</a>";
				}
				else {
					$value = "<a href=\"$value\">$value</a>";
				}
				$guessed = true;
			} else {
				if (JString::stristr($value, 'www.')) {
					if ($params->get('link_target_field', 1) == '1') {
						$target = $params->get('link_target_options', 'default');
						$value = "<a href=\"http://$value\" target=\"$target\">$value</a>";
						// end changes
					}
					else {
						$value = "<a href=\"http://$value\">$value</a>";
					}
					$guessed = true;
				}
			}
		}
		if (!$guessed) {
			$this->addCustomLink($value, $data, $repeatCounter);
		}
	}

	/**
	 * return the javascript to create an instance of the class defined in formJavascriptClass
	 * @return string javascript to create instance. Instance name must be 'el'
	 */

	function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$opts =& $this->getElementJSOptions($repeatCounter);
		$opts = json_encode($opts);
		return "new FbField('$id', $opts)";
	}

	/**
	 * defines the type of database table field that is created to store the element's data
	 */

	function getFieldDescription()
	{
		$p = $this->getParams();
		if ($this->encryptMe()) {
			return 'BLOB';
		}
		$group =& $this->getGroup();
		if ($group->isJoin() == 0 && $group->canRepeat()) {
			return "TEXT";
		}
		switch ($p->get('text_format')) {
			case 'text':
			default:
				//$objtype = "VARCHAR(255)";
				$objtype = "VARCHAR(" . $p->get('maxlength', 255) . ")";
				break;
			case 'integer':
				$objtype = "INT(" . $p->get('integer_length', 10) . ")";
				break;
			case 'decimal':
				$total = (int)$p->get('integer_length', 10) + (int)$p->get('decimal_length', 2);
				$objtype = "DECIMAL(" . $total . "," . $p->get('decimal_length', 2) . ")";
				break;
		}
		return $objtype;
	}

	/**
	 * @return array key=>value options
	 */

	function getJoomfishOptions()
	{
		$params = $this->getParams();
		$return  = array();
		$size 		= (int)$this->getElement()->width;
		if ($size !== 0) {
			$return['length'] = $size;
		}
		$maxlength  = (int)$params->get('maxlength');
		if ($maxlength === 0) {
			$maxlength = $size;
		}
		if ($params->get('textarea-showmax') && $maxlength !== 0) {
			$return['maxlength'] = $maxlength;
		}
		return $return;
	}

	/**
	 * can the element's data be encrypted
	 */

	public function canEncrypt()
	{
		return true;
	}

	/**
	 *  can be overwritten in add on classes
	 * @param mixed thie elements posted form data
	 * @param array posted form data
	 */

	function storeDatabaseFormat($val, $data)
	{
		if (is_array($val)) {
			foreach ($val as $k => $v) {
				$val[$k] = $this->_indStoreDatabaseFormat($v);
			}
			$val = implode(GROUPSPLITTER, $val);
		}
		else {
			$val = $this->_indStoreDatabaseFormat($val);
		}
		return $val;
	}

	function _indStoreDatabaseFormat($val) {
		return $this->unNumberFormat($val);
	}
	
	protected function getAvgQuery(&$tableModel, $label = "'calc'")
	{
		$params = $this->getParams();
		$format = $params->get('text_format', 'text');
		$decimal_places = $format == 'decimal' ? $params->get('decimal_length', '0') : '0';
		$table 			=& $tableModel->getTable();
		$joinSQL 		= $tableModel->_buildQueryJoin();
		$whereSQL 	= $tableModel->_buildQueryWhere();
		$name = $this->getFullName(false, false, false);
		$groupModel =& $this->getGroup();
		if ($groupModel->isJoin()) {
			//element is in a joined column - lets presume the user wants to sum all cols, rather than reducing down to the main cols totals
			return "SELECT ROUND(AVG($name), $decimal_places) AS value, $label AS label FROM ".FabrikString::safeColName($table->db_table_name)." $joinSQL $whereSQL";
		} else {
			// need to do first query to get distinct records as if we are doing left joins the sum is too large
			return "SELECT ROUND(AVG(value), $decimal_places) AS value, label
FROM (SELECT DISTINCT $table->db_primary_key, $name AS value, $label AS label FROM ".FabrikString::safeColName($table->db_table_name)." $joinSQL $whereSQL) AS t";
		}

	}
	
}
?>