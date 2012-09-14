<?php
/**
 * Renders a list of elements found in the current group
 * for use in setting the element's order
 *
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');
require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php';

/**
 * Renders a list of elements found in the current group
 * for use in setting the element's order
 *
 * @package     Joomla
 * @subpackage  Form
 * @since       1.6
 */

class JFormFieldSpecificordering extends JFormFieldList
{
	/**
	 * Element name
	 * @access	protected
	 * @var		string
	 */
	var $_name = 'Specificordering';

	/**
	 * Get list options
	 *
	 * @return  array
	 */

	protected function getOptions()
	{
		//ONLY WORKS INSIDE ELEMENT :(
		$db = FabrikWorker::getDbo();
		$group_id = $this->form->getValue('group_id');
		$query = "SELECT ordering AS value, name AS text" . "\n FROM #__{package}_elements " . "\n WHERE group_id = " . (int) $group_id
			. "\n AND published >= 0" . "\n ORDER BY ordering";
		// $$$ rob - rather than trying to override the JHTML class lets
		// just swap {package} for the current package.
		$query = FabrikWorker::getDbo(true)->replacePrefix($query);
		return JHTML::_('list.genericordering', $query);
	}

	/**
	 * Get the HTML input
	 *
	 * @return  string  HTML output
	 */

	function getInput()
	{
		$id = $this->form->getValue('id');
		if ($id)
		{
			// Get the field options.
			$options = (array) $this->getOptions();
			$ordering = JHTML::_('select.genericlist', $options, $this->name, 'class="inputbox" size="1"', 'value', 'text', $this->value);
		}
		else
		{
			$text = JText::_('COM_FABRIK_NEW_ITEMS_LAST');
			$ordering = '<input type="text" size="40" readonly="readonly" class="readonly" name="' . $this->name . '" value="' . $this->value . $text
				. '" />';
		}
		return $ordering;
	}
}
