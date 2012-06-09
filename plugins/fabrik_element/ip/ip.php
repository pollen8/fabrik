<?php
/**
 * Plugin element to store IP
 * @package fabrikar
 * @author Hugh Messenger
 * @copyright (C) Hugh Messenger
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class plgFabrik_elementIp extends plgFabrik_Element
{

	/**
	 * draws the form element
	 * @param int repeat group counter
	 * @return string returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		$element	= $this->getElement();
		$name 		= $this->getHTMLName($repeatCounter);
		$id 			= $this->getHTMLId($repeatCounter);
		$params 	=& $this->getParams();

		$rowid = JRequest::getVar('rowid', false);
		//@TODO when editing a form with joined repeat group the rowid will be set but
		//the record is in fact new
		if ($params->get('ip_update_on_edit') || !$rowid || ($this->_inRepeatGroup && $this->_inJoin &&  $this->_repeatGroupTotal == $repeatCounter)) {
			$ip = $_SERVER['REMOTE_ADDR'];
		} else {
			if (empty($data) || empty($data[$name])) {
				// if $data is empty, we must (?) be a new row, so just grab the IP
				$ip = $_SERVER['REMOTE_ADDR'];
			}
			else {
				$ip = $this->getValue($data, $repeatCounter);
			}
		}

		$str = '';
		if ($this->canView()) {
			if (!$this->_editable) {
				$str = $ip;
			}
			else {
				$str = "<input class=\"fabrikinput inputbox\" readonly=\"readonly\" name=\"$name\" id=\"$id\" value=\"$ip\" />\n";
			}
		} else {
			/* make a hidden field instead*/
			$str = "<input type=\"hidden\" class=\"fabrikinput\" name=\"$name\" id=\"$id\" value=\"$ip\" />";
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
	function _getHiddenField($name, $value, $id)
	{
		return "<input class=\"fabrikinput inputbox\" type=\"hidden\" name=\"$name\" value=\"$value\" id=\"$id\" />\n";
	}

	/**
	 * if we are creating a new record, and the element was set to readonly
	 * then insert the users data into the record to be stored
	 *
	 * @param unknown_type $data
	 */

	function onStoreRow(&$data)
	{
		$element = $this->getElement();
		if (JArrayHelper::getValue($data, 'rowid', 0) == 0 && !in_array($element->name, $data))
		{
			$data[$element->name] = $_SERVER['REMOTE_ADDR'];
		}
		else
		{
			$params = $this->getParams();
			if ($params->get('ip_update_on_edit', 0))
			{
				$data[$element->name] = $_SERVER['REMOTE_ADDR'];
				$data[$element->name . '_raw'] = $_SERVER['REMOTE_ADDR'];
			}
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see plgFabrik_Element::renderListData()
	 */

	public function renderListData($data, &$thisRow)
	{
		return parent::renderListData($data, $thisRow);
	}

	/**
	 * this really does get just the default value (as defined in the element's settings)
	 * @return unknown_type
	 */

	function getDefaultValue($data = array())
	{
		if (!isset($this->_default)) {
			$this->_default = $_SERVER['REMOTE_ADDR'];
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
		if (empty($data) || !array_key_exists($key, $data) || (array_key_exists($key, $data) && empty($data[$key]))) {
			// $$$rob - if no search form data submitted for the search element then the default
			// selection was being applied instead
			if (array_key_exists('use_default', $opts) && $opts['use_default'] == false) {
				$value = '';
			} else {
				$value   = $this->getDefaultValue($data);
			}
			return $value;
		}
		$res = parent::getValue($data, $repeatCounter, $opts);
		return $res;
	}

}
?>