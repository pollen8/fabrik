<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2010 Pollen 8 Design Ltd. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_fabrik'.DS.'helpers'.DS.'element.php');

/**
 * Renders a repeating drop down list of packages
 *
 * @author 		Rob Clayburn
 * @package 	Joomla
 * @subpackage		Fabrik
 * @since		1.5
 */

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');

class JFormFieldSuboptions extends JFormField

{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Subptions';


	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */

	protected function getInput()
	{
		JText::script('COM_FABRIK_SUBOPTS_VALUES_ERROR');

		$default = new stdClass();
		$default->sub_values = array();
		$default->sub_labels = array();
		$default->sub_initial_selection = array();
		$opts = $this->value == '' ? json_encode($default) : json_encode($this->value);
		$script = "new Suboptions('$this->name', $opts);";
		FabrikHelperHTML::script('administrator/components/com_fabrik/models/fields/suboptions.js', $script);
		$html = "<div style=\"float:left;width:100%\">

<table style=\"width: 100%\">
	<tr>
		<th style=\"width: 5%\"></th>
		<th style=\"width: 30%\">".JText::_('COM_FABRIK_VALUE')."</th>
		<th style=\"width: 30%\">".JText::_('COM_FABRIK_LABEL')."</th>
		<th style=\"width: 30%\">".JText::_('COM_FABRIK_DEFAULT')."</th>
	</tr>
</table>
<ul id=\"sub_subElementBody\" class=\"subelements\">
	<li></li>
</ul>
<a class=\"addButton\" href=\"#\" id=\"addSuboption\">". JText::_('COM_FABRIK_ADD'). "</a></div>";
		return $html;
	}

}