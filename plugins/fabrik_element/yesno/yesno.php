<?php
/**
 * Plugin element to yes/no radio options - render as tick/cross in list view
 * @package fabrikar
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models'.DS.'element.php');
require_once(JPATH_SITE.DS.'plugins'.DS.'fabrik_element'.DS.'radiobutton'.DS.'radiobutton.php');

class plgFabrik_ElementYesno extends plgFabrik_ElementRadiobutton {

	protected $fieldDesc = 'TINYINT(%s)';

	protected $fieldSize = '1';

	/**
	 * formats the posted data for insertion into the database
	 * @param mixed thie elements posted form data
	 * @param array posted form data
	 */

	function storeDatabaseFormat($val, $data)
	{
		$val = (array)$val;
		$return = array();
		foreach ($val as $v) {
			//$str .= $v[0] . GROUPSPLITTER;
			$return[] = $v[0];
		}
		//$str = FabrikString::rtrimword( $str, GROUPSPLITTER);
		//	return $str;
		return json_encode($val);
	}

	/**
	 * this really does get just the default value (as defined in the element's settings)
	 * @param array data to use as parsemessage for placeholder
	 * @return unknown_type
	 */

	function getDefaultValue($data)
	{
		if (!isset($this->_default)) {
			$params =& $this->getParams();
			$this->_default = $params->get('yesno_default', 0);
		}
		return $this->_default;
	}

	function renderListData($data, $oAllRowsData)
	{
		FabrikHelperHTML::addPath(JPATH_SITE.'/plugins/fabrik_element/yesno/images/', 'image', 'list', false);
		$yessrc = FabrikHelperHTML::image("1.png", 'list', @$this->tmpl, '', true);
		$nosrc = FabrikHelperHTML::image("0.png", 'list', @$this->tmpl, '', true);
		//check if the data is in csv format, if so then the element is a multi drop down
		if ($data == '1') {
			return "<img src=\"$yessrc\" alt=\"" . JText::_('JYES') . "\" />" ;
		} else {
			return "<img src=\"$nosrc\" alt=\"" . JText::_('JNO') . "\" />";
		}
	}

	/**
	 * shows the data formatted for the table view with format = pdf
	 * note pdf lib doesnt support transparent pngs hence this func
	 * @param string data
	 * @param object all the data in the tables current row
	 * @return string formatted value
	 */

	function renderListData_pdf($data, $oAllRowsData)
	{
		FabrikHelperHTML::addPath(JPATH_SITE.'/plugins/fabrik_element/yesno/images/', 'image', 'list', false);
		$yessrc = FabrikHelperHTML::image("1_8bit.png", 'list', $this->tmpl, '', true);
		$nosrc = FabrikHelperHTML::image("0_8bit.png", 'list', $this->tmpl, '', true);
		if ($data == '1') {
			return "<img src=\"$yessrc\" alt=\"" . JText::_('JYES') . "\" />" ;
		} else {
			return "<img src=\"$nosrc\" alt=\"" . JText::_('JNO') . "\" />";
		}
	}

	/**
	 * shows the data formatted for CSV export
	 * @param string data
	 * @param object all the data in the tables current row
	 * @return string formatted value
	 */

	function renderListData_csv($data, $oAllRowsData)
	{
		if ($data == '1') {
			return  JText::_('JYES');
		} else {
			return  JText::_('JNO');
		}
	}

	/**
	 * get the radio buttons possible values
	 * @return array of radio button values
	 */

	protected function getSubOptionValues()
	{
		return array(0, 1);
	}

	/**
	 * get the radio buttons possible labels
	 * @return array of radio button labels
	 */

	protected function getSubOptionLabels()
	{
		return array(JText::_('JNO'), JText::_('JYES'));
	}

	/**
	 * format the read only output for the page
	 * @param string $value
	 * @param string label
	 * @return string value
	 */

	protected function getReadOnlyOutput($value, $label)
	{
		FabrikHelperHTML::addPath(JPATH_SITE.'/plugins/fabrik_element/yesno/images/', 'image', 'form', false);
		$img = $value == '1' ?"1.png" : "0.png";
		return FabrikHelperHTML::image($img, 'form', @$this->tmpl, $label);
	}

	/**
	 * draws the form element
	 * @param int repeat group counter
	 * @return string returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		return parent::render($data, $repeatCounter);
	}

	/**
	 * return the javascript to create an instance of the class defined in formJavascriptClass
	 * @return string javascript to create instance. Instance name must be 'el'
	 */

	function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$params =& $this->getParams();
		$opts =& $this->getElementJSOptions($repeatCounter);
		$opts = json_encode($opts);
		return "new FbYesno('$id', $opts)";
	}

	/**
	 * Get the table filter for the element
	 * @param bol do we render as a normal filter or as an advanced searc filter
	 * if normal include the hidden fields as well (default true, use false for advanced filter rendering)
	 * @return string filter html
	 */

	function getFilter($counter = 0, $normal = true)
	{
		$table 				=& $this->getlistModel()->getTable();
		$elName 			= $this->getFullName(false, true, false);
		$htmlid				= $this->getHTMLId() . 'value';
		$elName 			= FabrikString::safeColName($elName);
		$v = 'fabrik___filter[list_'.$table->id.'][value]';
		$v .= ($normal) ? '['.$counter.']' : '[]';

		$default 			=  $this->getDefaultFilterVal($normal, $counter);

		$rows = $this->filterValueList($normal);
		$return 	 = JHTML::_('select.genericlist', $rows, $v, 'class="inputbox fabrik_filter" size="1" ', 'value', 'text', $default, $htmlid);
		if ($normal) {
			$return .= $this->getFilterHiddenFields($counter, $elName);
		} else {
			$return .= $this->getAdvancedFilterHiddenFields();
		}
		return $return;
	}

	/**
	 * (non-PHPdoc)
	 * @see components/com_fabrik/models/plgFabrik_Element#filterValueList_Exact($normal, $tableName, $label, $id, $incjoin)
	 */

	protected function filterValueList_Exact($normal, $tableName = '', $label = '', $id = '', $incjoin = true )
	{
		$opt = array(JHTML::_('select.option', '', $this->filterSelectLabel()));
		$rows = parent::filterValueList_Exact($normal, $tableName, $label, $id, $incjoin);
		foreach ($rows as &$row) {
			if ($row->value == 1) { $row->text = JText::_('JYES'); }
			if ($row->value == 0) { $row->text = JText::_('JNO'); }
		}
		$rows = array_merge($opt, $rows);
		return $rows;
	}

	/**
	 *
	 * @param unknown_type $normal
	 * @param unknown_type $tableName
	 * @param unknown_type $label
	 * @param unknown_type $id
	 * @param unknown_type $incjoin
	 */

	protected function filterValueList_All($normal, $tableName = '', $label = '', $id = '', $incjoin = true )
	{
		$rows = array(
		JHTML::_('select.option', '', $this->filterSelectLabel()),
		JHTML::_('select.option', '0', JText::_('JNO')),
		JHTML::_('select.option', '1', JText::_('JYES') )
		);
		return $rows;
	}

	/**
	 * (non-PHPdoc)
	 * @see components/com_fabrik/models/plgFabrik_Element#getFilterCondition()
	 */
	protected function getFilterCondition()
	{
		return '=';
	}

}
?>