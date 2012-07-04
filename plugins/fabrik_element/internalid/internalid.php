<?php
/**
* Plugin element to render internal id
* @package fabrikar
* @author Rob Clayburn
* @copyright (C) Rob Clayburn
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class PlgFabrik_ElementInternalid extends PlgFabrik_Element
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
		$params = $this->getParams();
		$element = $this->getElement();
		$value = $this->getValue($data, $repeatCounter);
		$type = "hidden";
		if ($this->elementError != '')
		{
			$type .= ' elementErrorHighlight';
		}
		if (!$this->editable)
		{
			//as per http://fabrikar.com/forums/showthread.php?t=12867
			//return "<!--" . stripslashes($value) . "-->";
			return ($element->hidden == '1') ? "<!-- " . stripslashes($value) . " -->" : stripslashes($value);
		}
		/* no need to eval here as its done before hand i think ! */
		if ($element->eval == "1" and !isset($data[$name]))
		{
			$str = $this->getHiddenField($name, $value, $id, 'inputbox fabrikinput ' . $type);
		}
		else
		{
			$value = stripslashes($value);
			$str = $this->getHiddenField($name, $value, $id, 'inputbox ' . $type);
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

	public function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts = json_encode($opts);
		return "new FbInternalId('$id', $opts)";
	}

	function isHidden()
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
		$item->auto_increment	= 1;
		return $item;
	}
}
?>