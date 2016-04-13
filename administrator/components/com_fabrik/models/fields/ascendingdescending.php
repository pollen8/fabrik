<?php
/**
 * Renders a list of ascending / descending options
 *
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Text;

/**
 * Renders a list of ascending / descending options
 *
 * @package     Joomla
 * @subpackage  Form
 * @since       1.6
 */

class JFormFieldAscendingdescending extends JFormFieldList
{
	/**
	 * Element name
	 * @var		string
	 */
	protected $name = 'Ascendingdescending';

	/**
	 * Method to get the field options.
	 *
	 * @return  array	The field option objects.
	 */

	protected function getOptions()
	{
		$opts[] = JHTML::_('select.option', 'ASC', Text::_('COM_FABRIK_ASCENDING'));
		$opts[] = JHTML::_('select.option', 'DESC', Text::_('COM_FABRIK_DESCENDING'));

		return $opts;
	}
}
