<?php
/**
 * Creates a J2Store add to cart button
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.j2store
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Creates a J2Store add to cart button
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.j2store
 * @since       3.0
 */
class PlgFabrik_ElementJ2Store extends PlgFabrik_Element
{
	/**
	 * Draws the html form element
	 *
	 * @param   array $data          to pre-populate element with
	 * @param   int   $repeatCounter repeat group counter
	 *
	 * @return  string    elements html
	 */
	public function render($data, $repeatCounter = 0)
	{
		$layoutData       = new stdClass;
		$name             = $this->getHTMLName($repeatCounter);
		$id               = $this->getHTMLId($repeatCounter);
		$layoutData->id   = $id;
		$layoutData->name = $name;
		$layout           = $this->getLayout('form');

		return $layout->render($layoutData);
	}

}
