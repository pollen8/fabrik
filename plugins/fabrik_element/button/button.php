<?php
/**
* Plugin element to render button
* @package fabrikar
* @author Rob Clayburn
* @copyright (C) Rob Clayburn
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class PlgFabrik_ElementButton extends PlgFabrik_Element
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
		$name = $this->getHTMLName($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$element = $this->getElement();
		$str = '<input type="button" class="fabrikinput button" id="' . $id . '" name="' . $name . '" value="' . $element->label . '" />';
		return $str;
	}

	/**
	 * (non-PHPdoc)
	 * @see PlgFabrik_Element::getLabel()
	 */

	function getLabel($repeatCounter, $tmpl = '')
	{
		return '';
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 * 
	 * @param   int  $repeatCounter  repeat group counter
	 * 
	 * @return  string
	 */

	public function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts = json_encode($opts);
		return "new FbButton('$id', $opts)";
		return $str;
	}

	/**
	 * Examples of where this would be overwritten include timedate element with time field enabled
* @param   int		repeat group counter
	 * @return  array	html ids to watch for validation
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