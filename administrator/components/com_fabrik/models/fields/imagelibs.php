<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       1.6
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php';

/**
 * Renders availble Image Libraries
 *
 * @package  Fabrik
 * @since    3.0
 */

class JFormFieldImagelibs extends JFormFieldList
{
	/**
	 * Element name
	 * @access	protected
	 * @var		string
	 */
	protected $name = 'Imagelibs';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */

	protected function getOptions()
	{
		require_once COM_FABRIK_FRONTEND . '/helpers/image.php';
		$imageLibs = FabimageHelper::getLibs();
		if (empty($imageLibs))
		{
			return JHTML::_('select.option', JText::_('NO MAGE LIBRARY FOUND'));
		}
		return $imageLibs;
	}
}
