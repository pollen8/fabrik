<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Plugin element to store IP
 * 
 * @package  Fabrik
 * @since    3.0
 */

class PlgFabrik_ElementIp extends PlgFabrik_Element
{

	/**
	 * Draws the html form element
	 * 
	 * @param   array  $data           to preopulate element with
	 * @param   int    $repeatCounter  repeat group counter
	 * 
	 * @return  string	elements html
	 */

	public function render($data, $repeatCounter = 0)
	{
		$element = $this->getElement();
		$name = $this->getHTMLName($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$params = &$this->getParams();

		$rowid = JRequest::getVar('rowid', false);
		/**
		 * @TODO when editing a form with joined repeat group the rowid will be set but
		 * the record is in fact new
		 */
		//
		if ($params->get('ip_update_on_edit') || !$rowid || ($this->inRepeatGroup && $this->_inJoin && $this->_repeatGroupTotal == $repeatCounter))
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		else
		{
			if (empty($data) || empty($data[$name]))
			{
				// If $data is empty, we must (?) be a new row, so just grab the IP
				$ip = $_SERVER['REMOTE_ADDR'];
			}
			else
			{
				$ip = $this->getValue($data, $repeatCounter);
			}
		}

		$str = '';
		if ($this->canView())
		{
			if (!$this->editable)
			{
				$str = $ip;
			}
			else
			{
				$str = "<input class=\"fabrikinput inputbox\" readonly=\"readonly\" name=\"$name\" id=\"$id\" value=\"$ip\" />\n";
			}
		}
		else
		{
			/* make a hidden field instead*/
			$str = "<input type=\"hidden\" class=\"fabrikinput\" name=\"$name\" id=\"$id\" value=\"$ip\" />";
		}
		return $str;
	}

	/**
	 * Trigger called when a row is stored.
	 * If we are creating a new record, and the element was set to readonly
	 * then insert the users data into the record to be stored
	 * 
	 * @param   array  &$data  to store
	 * 
	 * @return  void
	 */

	public function onStoreRow(&$data)
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
	 * Shows the data formatted for the list view
	 * 
	 * @param   string  $data      elements data
	 * @param   object  &$thisRow  all the data in the lists current row
	 * 
	 * @return  string	formatted value
	 */

	public function renderListData($data, &$thisRow)
	{
		return parent::renderListData($data, $thisRow);
	}

	/**
	 * This really does get just the default value (as defined in the element's settings)
	 * 
	 * @param   array  $data  form data
	 * 
	 * @return mixed 
	 */

	public function getDefaultValue($data = array())
	{
		if (!isset($this->default))
		{
			$this->default = $_SERVER['REMOTE_ADDR'];
		}
		return $this->default;
	}

	/**
	 * Determines the value for the element in the form view
	 * 
	 * @param   array  $data           form data
	 * @param   int    $repeatCounter  when repeating joinded groups we need to know what part of the array to access
	 * @param   array  $opts           options
	 * 
	 * @return  string	value
	 */

	public function getValue($data, $repeatCounter = 0, $opts = array())
	{
		// Cludge for 2 scenarios
		if (array_key_exists('rowid', $data))
		{
			// When validating the data on form submission
			$key = 'rowid';
		}
		else
		{
			// When rendering the element to the form
			$key = '__pk_val';
		}
		if (empty($data) || !array_key_exists($key, $data) || (array_key_exists($key, $data) && empty($data[$key])))
		{
			// $$$rob - if no search form data submitted for the search element then the default
			// selection was being applied instead
			$value = array_key_exists('use_default', $opts) && $opts['use_default'] == false ? '' : $this->getDefaultValue($data);
			return $value;
		}
		$res = parent::getValue($data, $repeatCounter, $opts);
		return $res;
	}

}
