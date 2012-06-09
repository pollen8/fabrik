<?php
// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

class ElementHelper{

	function getId($element, $control_name, $name)
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

	function getFullName($element, $control_name, $name)
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

	public static function getRepeatCounter($element)
	{
		if (method_exists($element, 'getRepeatCounter'))
		{
			$c = $this->getRepeatCounter();
		}
		else
		{
			$c = false;
		}
		return $c;
	}

	public static function getRepeat($element)
	{
		if (method_exists($element, 'getRepeat'))
		{
			$c = $this->getRepeat();
		}
		else
		{
			$c = 0;
		}
		return $c;
	}
}
?>