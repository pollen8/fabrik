<?php
/**
* @package		Joomla.Plugin
* @subpackage	Fabrik.element.button
* @copyright	Copyright (C) 2005 Fabrik. All rights reserved.
* @license		GNU General Public License version 2 or later; see LICENSE.txt
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
* Plugin element to render button
*
* @package		Joomla.Plugin
* @subpackage	Fabrik.element.button
*/

class plgFabrik_ElementButton extends plgFabrik_Element
{

	/**
	 * draws a button
	 * @param	string	data
	 * @param	int		repeat group counter
	 * @return	string	returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		$name = $this->getHTMLName($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$element = $this->getElement();
		$str = '<input type="button" class="fabrikinput button" id="' . $id . '" name="' . $name . '" value="' . $element->label . '" />';
		return $str;
	}

	/**
	 * (non-PHPdoc)
	 * @see plgFabrik_Element::getLabel()
	 */

	function getLabel($repeatCounter, $tmpl = '')
	{
		return '';
	}

	/**
	 * (non-PHPdoc)
	 * @see plgFabrik_Element::elementJavascript()
	 */

	function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts = json_encode($opts);
		return "new FbButton('$id', $opts)";
		return $str;
	}

	/**
	 * Examples of where this would be overwritten include timedate element with time field enabled
	 * @param	int		repeat group counter
	 * @return	array	html ids to watch for validation
	 */

	function getValidationWatchElements($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$ar = array(
			'id' => $id,
			'triggerEvent' => 'click'
		);
		return array($ar);
	}
}
?>