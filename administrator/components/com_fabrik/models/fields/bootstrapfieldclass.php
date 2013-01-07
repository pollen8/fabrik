<?php
/**
 * Renders a list of Bootstrap field class sizes
 *
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Renders a list of Bootstrap field class sizes
 *
 * @package     Joomla
 * @subpackage  Form
 * @since		1.5
 */

class JFormFieldBootstrapfieldclass extends JFormFieldList
{

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */

	protected function getOptions()
	{
		$sizes = array();
		$sizes[] = JHTML::_('select.option', 'input-mini');
		$sizes[] = JHTML::_('select.option', 'input-small');
		$sizes[] = JHTML::_('select.option', 'input-medium');
		$sizes[] = JHTML::_('select.option', 'input-large');
		$sizes[] = JHTML::_('select.option', 'input-xlarge');
		$sizes[] = JHTML::_('select.option', 'input-xxlarge');
		return $sizes;
	}
}
