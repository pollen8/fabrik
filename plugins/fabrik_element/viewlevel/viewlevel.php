<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.viewlevel
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Plugin element to render user view levels
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.viewlevel
 * @since       3.0.6
 */

class PlgFabrik_ElementViewlevel extends PlgFabrik_Element
{

	/** @var  string  db table field type */
	protected $fieldDesc = 'INT(%s)';

	/** @var  string  db table field size */
	protected $fieldSize = '3';

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
		$arSelected = array('');
		if (isset($data[$name]))
		{
			$arSelected = !is_array($data[$name]) ? explode(',', $data[$name]) : $arSelected = $data[$name];
		}
		if (!$this->canUse())
		{
			$data = new stdClass;
			return $this->renderListData($arSelected[0], $data);
		}
		$options = array();
		return JHtml::_('access.level', $name, $arSelected[0], 'class="inputbox" size="6"', $options, $id);
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
		return "new FbViewlevel('$id', $opts)";
	}

}
