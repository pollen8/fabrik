<?php
/**
 * Renders a list of installed image libraries
 *
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Image;
use Fabrik\Helpers\Text;

/**
 * Renders a list of installed image libraries
 *
 * @package     Joomla
 * @subpackage  Form
 * @since       3.0
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
		$imageLibs = Image::getLibs();

		if (empty($imageLibs))
		{
			return JHTML::_('select.option', Text::_('COM_FABRIK_IMAGELIBS_NOT_FOUND'));
		}

		return $imageLibs;
	}
}
