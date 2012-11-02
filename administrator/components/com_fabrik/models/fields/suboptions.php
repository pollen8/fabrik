<?php
/**
 * Used in radios/checkbox elements for adding <options> to the element
 *
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php';

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');

/**
 * Used in radios/checkbox elements for adding <options> to the element
 *
 * @package     Joomla
 * @subpackage  Form
 * @since       1.6
 */

class JFormFieldSuboptions extends JFormField
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	protected $name = 'Subptions';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 */

	protected function getInput()
	{
		JText::script('COM_FABRIK_SUBOPTS_VALUES_ERROR');

		$default = new stdClass;
		$default->sub_values = array();
		$default->sub_labels = array();
		$default->sub_initial_selection = array();
		$opts = $this->value == '' ? $default : $this->value;
		$delClass = FabrikWorker::j3() ? 'btn btn-danger' : 'removeButton';
		$delButton = '<a class="' . $delClass . '" href="#"><i class="icon-delete"></i> ' . JText::_('COM_FABRIK_DELETE') . '</a>';
		if (is_array($opts))
		{
			$opts['delButton'] = $delButton;
		}
		else
		{
			$opts->delButton = $delButton;
		}
		$opts = json_encode($opts);
		$script = "new Suboptions('$this->name', $opts);";
		$addClass = FabrikWorker::j3() ? 'btn btn-success' : 'addButton';
		FabrikHelperHTML::script('administrator/components/com_fabrik/models/fields/suboptions.js', $script);
		$html = '<div style="float:left;width:100%">

<table style="width: 100%">
	<tr style="text-align:left">
		<th style="width: 5%"></th>
		<th style="width: 30%">' . JText::_('COM_FABRIK_VALUE') . '</th>
		<th style="width: 30%">' . JText::_('COM_FABRIK_LABEL') . '</th>
		<th style="width: 30%">' . JText::_('COM_FABRIK_DEFAULT')
			. '</th>
	</tr>
</table>
<ul id="sub_subElementBody" class="subelements">
	<li></li>
</ul><a class="' . $addClass. '" href="#" id="addSuboption"><i class="icon-plus"></i> ' . JText::_('COM_FABRIK_ADD') . '</a></div>';
		return $html;
	}

}
