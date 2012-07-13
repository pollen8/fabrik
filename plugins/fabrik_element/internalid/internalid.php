<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.internalid
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Plugin element to render internal id
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.internalid
 */

class plgFabrik_ElementInternalid extends plgFabrik_Element
{

	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           to preopulate element with
	 * @param   int    $repeatCounter  repeat group counter
	 *
	 * @return  string	elements html
	 */

	function render($data, $repeatCounter = 0)
	{
		$name = $this->getHTMLName($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$params = $this->getParams();
		$element = $this->getElement();
		$value = $this->getValue($data, $repeatCounter);
		$type = "hidden";
		if (isset($this->_elementError) && $this->_elementError != '')
		{
			$type .= " elementErrorHighlight";
		}
		if (!$this->_editable)
		{
			//as per http://fabrikar.com/forums/showthread.php?t=12867
			//return "<!--" . stripslashes($value) . "-->";
			return ($element->hidden == '1') ? "<!-- " . stripslashes($value) . " -->" : stripslashes($value);
		}
		$hidden = 'hidden';
		/* no need to eval here as its done before hand i think ! */
		if ($element->eval == "1" and !isset($data[$name]))
		{
			$str = "<input class=\"inputbox $type\" type=\"$hidden\" name=\"$name\" id=\"$id\" value=\"$value\" />\n";
		}
		else
		{
			$value = stripslashes($value);
			$str = "<input class=\"inputbox fabrikinput $type\" type=\"$hidden\" name=\"$name\" id=\"$id\" value=\"$value\" />\n";
		}
		return $str;
	}

	/**
	 * Get database field description
	 *
	 * @return  string  db field type
	 */

	public function getFieldDescription()
	{
		return "INT(6) NOT NULL AUTO_INCREMENT";
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int  $repeatCounter  repeat group counter
	 *
	 * @return  string
	 */

	function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts = json_encode($opts);
		return "new FbInternalId('$id', $opts)";
	}

	/**
	 * Is the element hidden or not - if not set then return false
	 *
	 * @return  bool
	 */

	protected function isHidden()
	{
		return true;
	}

	function onLoad()
	{

	}

	/**
	 * load a new set of default properites and params for the element
	 * @return  object	element (id = 0)
	 */

	public function getDefaultProperties()
	{
		$item = parent::getDefaultProperties();
		$item->primary_key = true;
		$item->width = 3;
		$item->hidden = 1;
		$item->auto_increment = 1;
		return $item;
	}
}
?>