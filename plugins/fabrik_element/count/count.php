<?php
/**
 * Plugin element to:
 * Counts records in a row - so adds "COUNT(x) .... GROUP BY (y)" to the main db query
 *
 * Note implementing this element will mean that only the first row of data is returned in
 * the joined group
 *
 * @package fabrikar
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once(JPATH_SITE . '/components/com_fabrik/models/element.php');

class plgFabrik_ElementCount extends plgFabrik_Element {

	/**
	 */
	public function getGroupByQuery()
	{
		$params = $this->getParams();
		return $params->get('count_groupbyfield');
	}

	/**
	 * @param array
	 * @param array
	 * @param string table name (depreciated)
	 */

	function getAsField_html(&$aFields, &$aAsFields, $opts = array())
	{
		$dbtable = $this->actualTableName();
		$db = FabrikWorker::getDbo();
		if (JRequest::getVar('c') != 'form') {
			$params = $this->getParams();
			$fullElName = JArrayHelper::getValue($opts, 'alias', $db->nameQuote($dbtable . "___".$this->_element->name));
			$r = "COUNT(".$params->get('count_field', '*').")";
			$aFields[] 	= "$r AS $fullElName";
			$aAsFields[] =  $fullElName;
			$aAsFields[] =  "`$dbtable" . "___" . $this->getElement()->name . "_raw`";
		}
	}

	/**
	 * determines if the element can contain data used in sending receipts, e.g. field returns true
	 */

	function isReceiptElement()
	{
		return false;
	}

	/**
	 * this element s only used for table displays so always return false
	 * (non-PHPdoc)
	 * @see components/com_fabrik/models/plgFabrik_Element#canUse()
	 */

	function canUse()
	{
		return false;
	}

	/**
	 * draws the form element
	 * @param array data to preopulate element with
	 * @param int repeat group counter
	 * @return string returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		return '';
		/*$name 			= $this->getHTMLName($repeatCounter);
		 $id 				= $this->getHTMLId($repeatCounter);
		 $params 		=& $this->getParams();
		 $element 		= $this->getElement();
		 $size 			= $element->width;

		 $bits = array();
		 // $$$ rob - not sure why we are setting $data to the form's data
		 //but in table view when getting read only filter value from url filter this
		 // _form_data was not set to no readonly value was returned
		 // added little test to see if the data was actually an array before using it
		 if (is_array($this->_form->_data)) {
			$data 	=& $this->_form->_data;
			}
			$value 	= $this->getValue($data, $repeatCounter);
			$type = "text";
			if (isset($this->_elementError) && $this->_elementError != '') {
			$type .= " elementErrorHighlight";
			}
			if ($element->hidden == '1') {
			$type = "hidden";
			}
			if (!$this->_editable) {
			return($element->hidden == '1') ? "<!-- " . $value . " -->" : $value;
			}

			$bits['class']		= "fabrikinput inputbox $type";
			$bits['type']		= $type;
			$bits['name']		= $name;
			$bits['id']			= $id;

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
			return $str;*/
	}

	/**
	 * return the javascript to create an instance of the class defined in formJavascriptClass
	 * @return string javascript to create instance. Instance name must be 'el'
	 */

	function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts = json_encode($opts);
		return "new FbCount('$id', $opts)";
	}

}
?>