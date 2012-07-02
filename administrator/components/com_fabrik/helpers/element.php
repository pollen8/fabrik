<?php
/**
* @package     Joomla.Administrator
* @subpackage  Fabrik
* @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
* @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* @since       1.6
*/

// No direct access.
defined('_JEXEC') or die;

/**
 * JHtml element helper
 * 
 * @package  Fabrik
 * @since    3.0
 */

class ElementHelper
{

	/**
	 * get html id
	 * 
	 * @param   object  $element       field
	 * @param   string  $control_name  control name
	 * @param   string  $name          name
	 * 
	 * @return string
	 */

	public function getId($element, $control_name, $name)
	{
		if (method_exists($element, 'getId'))
		{
			$id = $element->getId($control_name, $name);
		}
		else
		{
			$id  = "$control_name.$name";
		}
		return $id;
	}

	/**
	* get full name
	*
	* @param   object  $element       field
	* @param   string  $control_name  control name
	* @param   string  $name          name
	*
	* @return string
	*/

	public function getFullName($element, $control_name, $name)
	{
		if (method_exists($element, 'getFullName'))
		{
			$fullName = $element->getFullName($control_name, $name);
		}
		else
		{
			$fullName = $control_name . '[' . $name . ']';
		}
		return $fullName;
	}

	/**
	 * get repeat counter
	 * 
	 * @param   object  $element  Jhtml field
	 * 
	 * @return mixed 
	 */

	public static function getRepeatCounter($element)
	{
		if (method_exists($element, 'getRepeatCounter'))
		{
			$c = $element->getRepeatCounter();
		}
		else
		{
			$c = false;
		}
		return $c;
	}

	/**
	* get repeat
	*
	* @param   object  $element  Jhtml field
	* 
	* @return mixed
	*/

	public static function getRepeat($element)
	{
		if (method_exists($element, 'getRepeat'))
		{
			$c = $element->getRepeat();
		}
		else
		{
			$c = 0;
		}
		return $c;
	}
}
