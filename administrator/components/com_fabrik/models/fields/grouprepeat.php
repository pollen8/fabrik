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


JFormHelper::loadFieldClass('radio');

/**
 * Renders a radio group but only if the fabrik group is assigned to a form
 * see: https://github.com/Fabrik/fabrik/issues/95
 *
 * @package 	Joomla
 * @subpackage	fabrik
 * @since		1.7
 */
class JFormFieldGrouprepeat extends JFormFieldRadio
{
	/**
	 * Element name
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Grouprepeat';

	protected function getInput()
	{
		if ((int) $this->form->getValue('form') === 0)
		{
			return '<input class="readonly" size="60" value="' . JText::_('COM_FABRIK_FIELD_ASSIGN_GROUP_TO_FORM_FIRST') . '" type="readonly" />';
		}
		else
		{
			return parent::getInput();
		}
	}
}