<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_fabrik'.DS.'helpers'.DS.'element.php');

/**
 * Renders a fabrik element drop down
 *
 * @author 		rob clayburn
 * @package 	fabrikar
 * @subpackage		Parameter
 * @since		1.6
 */

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldElement extends JFormFieldList
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Element';

	function getOptions()
	{
		$cnns = array(JHTML::_('select.option', '-1', JText::_('COM_FABRIK_PLEASE_SELECT')));
		return;
	}

	function getInput()
	{
		static $fabrikelements;
		if (!isset($fabrikelements)) {
			$fabrikelements = array();
		}

		$c = $this->form->repeatCounter;
		$table = $this->element['table'];

		$include_calculations = (int)$this->element['include_calculations'];
		$published = (int)$this->element['published'];
		$showintable = (int)$this->element['showintable'];
		if ($include_calculations != 1) {
			$include_calculations = 0;
		}

		if (!array_key_exists($this->id, $fabrikelements)) {
			$opts = new stdClass();
			if ($this->form->repeat) {
				//in repeat fieldset/group
				$conn =  $this->element['connection'].'-'.$this->form->repeatCounter;
				$opts->table = 'jform_'.$table.'-'.$this->form->repeatCounter;
			} else {
				$conn = ($c === false || $this->element['connection_in_repeat'] == 'false') ?  $this->element['connection'] :  $this->element['connection'].'-'.$c;
				$opts->table = ($c === false || $this->element['connection_in_repeat'] == 'false') ? 'jform_'.$table : 'jform_'.$table.'-'.$c;
			}

			$opts->published = $published;
			$opts->showintable = $showintable;
			$opts->excludejoined = (int)$this->element['excludejoined'];
			$opts->livesite = COM_FABRIK_LIVESITE;
			$opts->conn = 'jform_'.$conn;
			$opts->value = $this->value;
			$opts->include_calculations = $include_calculations;
			$opts = json_encode($opts);

			$script = array();
			$script[] = "var p = new elementElement('$this->id', $opts);";
			$script[] = "Fabrik.model.fields.element['$this->id'] = p;";
			$script = implode("\n", $script);
			$fabrikelements[$this->id] = true;
			FabrikHelperHTML::script('administrator/components/com_fabrik/models/fields/element.js', $script);
		}
		$return = parent::getInput();
		$return .= '<img style="margin-left:10px;display:none" id="'.$this->id.'_loader" src="components/com_fabrik/images/ajax-loader.gif" alt="' . JText::_('COM_FABRIK_LOADING'). '" />';
		return $return;
	}
}
?>