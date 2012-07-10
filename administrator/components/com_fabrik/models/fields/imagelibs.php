<?php
/**
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php';

/**
 * Renders a list of elements found in a fabrik table
 *
 * @package     Joomla
 * @subpackage  Form
 * @since		1.5
 */

class JFormFieldImagelibs extends JFormFieldList
{
	/**
	 * Element name
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Imagelibs';

	function getOptions()
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