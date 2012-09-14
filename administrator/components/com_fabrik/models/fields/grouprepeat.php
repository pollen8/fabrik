<?php
/**
 * Renders a radio group but only if the fabrik group is assigned to a form
 *
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php';


JFormHelper::loadFieldClass('radio');

/**
 * Renders a radio group but only if the fabrik group is assigned to a form
 * see: https://github.com/Fabrik/fabrik/issues/95
 *
 * @package     Joomla
 * @subpackage  Form
 * @since       1.6
 */

class JFormFieldGrouprepeat extends JFormFieldRadio
{
	/**
	 * Element name
	 *
	 * @var		string
	 */
	var	$_name = 'Grouprepeat';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 */

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
