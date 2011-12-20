<?php
/**
* @version		$Id: element.php 10707 2008-08-21 09:52:47Z eddieajau $
* @package		Joomla.Framework
* @subpackage	Parameter
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Parameter base class
 *
 * The JElement is the base class for all JElement types
 *
 * @abstract
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */
class JElement extends JObject
{
	/**
	* element name
	*
	* This has to be set in the final
	* renderer classes.
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = null;

	/**
	* reference to the object that instantiated the element
	*
	* @access	protected
	* @var		object
	*/
	var	$_parent = null;

	/**
	 * Constructor
	 *
	 * @access protected
	 */
	function __construct($parent = null) {
		$this->_parent = $parent;
	}

	/**
	* get the element name
	*
	* @access	public
	* @return	string	type of the parameter
	*/
	function getName() {
		return $this->_name;
	}

	function render(&$xmlElement, $value, $control_name = 'params')
	{
		$name	= $xmlElement->attributes('name');
		$label	= $xmlElement->attributes('label');
		$descr	= $xmlElement->attributes('description');
		//make sure we have a valid label
		$label = $label ? $label : $name;
		$result[0] = $this->fetchTooltip($label, $descr, $xmlElement, $control_name, $name);
		$result[1] = $this->fetchElement($name, $value, $xmlElement, $control_name);
		$result[2] = $descr;
		$result[3] = $label;
		$result[4] = $value;
		$result[5] = $name;
		return $result;
	}

	function fetchTooltip($label, $description, &$xmlElement, $control_name='', $name='')
	{
		$output = '<label id="'.$control_name.$name.'-lbl" for="'.$control_name.$name.'"';
		if ($description) {
			$output .= ' class="hasTip" title="'.JText::_($label).'::'.JText::_($description).'">';
		} else {
			$output .= '>';
		}
		$output .= JText::_($label).'</label>';

		return $output;
	}

	function fetchElement($name, $value, &$xmlElement, $control_name) {
		return;
	}

	//////
	// FABRIK SPECIFIC
	/////

	/**
	 * get the elements html name
	 * @param string $control_name
	 * @param string $name
	 * @return string html name
	 */
	function getFullName($control_name, $name )
	{
		if (strstr($name, "[]")) {
			$name = trim($name, "[]");
			$fullname = $control_name.'['.$name."][]";
		} else {
			$fullname = $control_name.'['.$name.']';
		}
		return $fullname;
	}

	/**
	 * get the elements id
	 * @param string $control_name
	 * @param string $name
	 * @return string element id
	 */
	function getId($control_name, $name )
	{
		$name = str_replace('[]', '', $name);

		$c = $this->getRepeatCounter();
		if ($c !== false) {
			$id = $control_name.$name . '-' . $c;
		} else {
			$id = $control_name.$name;
		}
		return $id;
	}

	/**
	 * get repeat group counter used for things like element's id
	 * @return int repeat group counter
	 */
	function getRepeatCounter()
	{
		if (isset($this->_parent->_counter_override ) && $this->_parent->_counter_override != -1 ){
			return $this->_parent->_counter_override;
		} else {
			if ($this->getRepeat()) {
				if(isset($this->_array_counter )){
					//array counter might have been set when rendering viz params
					return $this->_array_counter;
				} else {
					// element in repeat but no repeat counter override or array counter set so default to 0
					return 0;
				}
			}
			return false;
		}
	}

	/**
	 * is the current element in a repeat group
	 * @return bol
	 */
	function getRepeat()
	{
		if (isset($this->_parent->_group)) {
			//funky custom fabrik params (components/com_fabrik/helpers/params)
			//  have had their _group option set
			// in render() method
			$group = $this->_parent->_group;
		} else {
			$group = $this->_parent->get('_group', '_default');
		}
		return $this->_parent->_xml[$group]->attributes('repeat');
	}
}

?>