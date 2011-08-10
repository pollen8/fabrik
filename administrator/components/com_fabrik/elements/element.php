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
 * @since		1.5
 */

class JElementElement extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Element';

	function fetchElement($name, $value, &$node, $control_name )
	{
		static $fabrikelements;
		if (!isset($fabrikelements)) {
			$fabrikelements = array();
		}
		FabrikHelperHTML::script('administrator/components/com_fabrik/elements/element.js', true);
		$document =& JFactory::getDocument();
		$c = ElementHelper::getRepeatCounter($this);
		$conn = ($c === false || $node->attributes('connection_in_repeat') == 'false') ?  $node->attributes('connection') :  $node->attributes('connection') . '-' . $c;


		$table = $node->attributes('table');
		$include_calculations = (int)$node->attributes('include_calculations', 0);
		$published = (int)$node->attributes('published', 0);
		$showintable = (int)$node->attributes('showintable', 0);
		if ($include_calculations != 1) {
			$include_calculations = 0;
		}


		$cnns = array(JHTML::_('select.option', '-1', JText::_('COM_FABRIK_PLEASE_SELECT')));

		$id 			= ElementHelper::getId($this, $control_name, $name);
		$fullName = ElementHelper::getFullName($this, $control_name, $name);
		$repeat 	= ElementHelper::getRepeat($this);

		if (!array_key_exists($id, $fabrikelements)) {
			$script = "head.ready(function() {\n";

			$opts = new stdClass();

			$opts->table = ($c === false) ? 'params' . $table : 'params' . $table . "-" .$c;

			$opts->published = $published;
			$opts->showintable = $showintable;
			$opts->excludejoined = (int)$node->attributes('excludejoined', 0);
			$opts->livesite = COM_FABRIK_LIVESITE;
			$opts->conn = 'params'.$conn;
			$opts->value = $value;
			$opts->include_calculations = $include_calculations;
			$opts = json_encode($opts);

			$script .= 	"var p = new elementElement('$id', $opts);\n";
			$script .= "Fabrik.model.fields.element['$id'] = p;\n";
			$script .="});\n";
			$document->addScriptDeclaration($script);
			$fabrikelements[$id] = true;
		}
		$return = JHTML::_('select.genericlist', $cnns, $fullName, 'class="inputbox element"', 'value', 'text', $value, $id);
		$return .= '<img style="margin-left:10px;display:none" id="'.$id.'_loader" src="components/com_fabrik/images/ajax-loader.gif" alt="' . JText::_('LOADING'). '" />';
		return $return;
	}
}
?>