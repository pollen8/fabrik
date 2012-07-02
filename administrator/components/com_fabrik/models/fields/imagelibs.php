<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
* @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php';

/**
 * Renders a list of elements found in a fabrik table
 *
 * @package 	Joomla
 * @subpackage	Articles
 * @since		1.5
 */
class JFormFieldImagelibs extends JFormFieldList
{
	/**
	 * Element name
	 * @access	protected
	 * @var		string
	 */
	protected $name = 'Imagelibs';

	function getOptions()
	{
		require_once(COM_FABRIK_FRONTEND . '/helpers/image.php');
		$imageLibs = FabimageHelper::getLibs();
		if (empty($imageLibs))
		{
			return JHTML::_('select.option', JText::_('NO MAGE LIBRARY FOUND'));
		}
		return $imageLibs;
	}
}