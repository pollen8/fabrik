<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once(JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php');

/**
 * Renders a list of ascending / decending options
 *
 * @package 	Joomla
 * @subpackage	fabrik
 * @since		1.6
 */
class JFormFieldAscendingdescending extends JFormFieldList
{
	/**
	 * Element name
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Ascendingdescending';

		/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	
	public function getOptions()
	{
		$opts[] = JHTML::_('select.option', 'ASC', JText::_('COM_FABRIK_ASCENDING'));
		$opts[] = JHTML::_('select.option', 'DESC', JText::_('COM_FABRIK_DESCENDING'));
		return $opts;
	}
}