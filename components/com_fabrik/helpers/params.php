<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

/* MOS Intruder Alerts */
defined('_JEXEC') or die();
/*
 * DEPRECIATED?
could well be that we don't use this now???????????
 */
jimport('joomla.html.parameter');

class fabrikParams extends JParameter
{

	/** @var bol duplicatable param (if true add []" to end of element name)*/
	var $_duplicate = false;

	/** used by form plugins - to set id in name of radio buttons **/
	var $_counter_override = null;
	/**
	 * constructor
	 */

	function __construct($data, $path = '')
	{
		$this->_identifier = str_replace("\\", "-", str_replace(".xml", "", str_replace(JPATH_SITE, '', $path)));
		$this->_identifier = str_replace('/', '-', $this->_identifier);
		parent::__construct($data, $path);
	}

	/**
	 * Get the names of all the parameters in the object
	 * @access private
	 * @return array parameter names
	 */

	function _getParamNames()
	{
		$p = array();
		$default = (object)$this->_xml['_default'];
		if (empty($default)) {
			return $p;
		}
		foreach ($default->children() as $node)  {
			$p[] = $node->attributes('name');
		}
		return $p;
	}

	/**
	 * overwrite core get function so we can force setting to array if needed
	 * @param string key
	 * @param string default
	 * @param string group
	 * @param string output format (string or array)
	 * @param int counter - not used i think
	 * @return mixed - string or array
	 */

	function get($key, $default='', $group = '_default', $outputFormat = 'string', $counter = null )
	{
		$return = parent::get($key, $default);
		if ($outputFormat == 'array') {
			$return = $return == '' ? array() : (array)$return;
		}
		return $return;
	}

	/**
	 * (non-PHPdoc)
	 * @see libraries/joomla/html/JParameter#getParams($name, $group)
	 */

	function getParams($name = 'params', $group = '_default', $ouputformat = 'string', $counter = null)
	{
		if (!isset($this->_xml[$group])) {
			return false;
		}

		$results = array();
		foreach ($this->_xml[$group]->children() as $param)  {
			$results[] = $this->getParam($param, $name, $group, $ouputformat, $counter);
		}
		return $results;
	}

	/**
	 * get a groups parameters names
	 * @param unknown_type $name
	 * @param unknown_type $group
	 * @return string|multitype:
	 */
	function getParamsNames($name = 'params', $group = '_default')
	{
		if (!isset($this->_xml[$group])) {
			return false;
		}
		$results = array();
		foreach ($this->_xml[$group]->children() as $node)  {
			$results[] = $node->attributes('name');
		}
		return $results;
	}

	/**
	 * Render a parameter type
	 *
	 * @param	object A param tag node
	 * @param	string The control name
	 * @param string parameter group
	 * @param string output format
	 * @param mixed repeat group counter??? /how about repeating plugins is this the same??
	 * @return	array Any array of the label, the form element and the tooltip
	 * @since	1.5
	 */

	function getParam(&$node, $control_name = 'params', $group = '_default', $outPutFormat ='string', $counter = null)
	{
		//get the type of the parameter
		$type = $node->attributes('type');

		//remove any occurance of a mos_ prefix
		$type = str_replace('mos_', '', $type);

		$element = $this->loadElement($type);

		// error happened
		if ($element === false)
		{
			$result = array();
			$result[0] = $node->attributes('name');
			$result[1] = JText::_('COM_FABRIK_ELEMENT_NOT_DEFINED_FOR_TYPE').' = '.$type;
			$result[5] = $result[0];
			return $result;
		}

		//get value


		if ($outPutFormat == 'array' && !is_null($counter)) {
			$nodeName = str_replace("[]", "",$node->attributes('name'));
		} else {
			$nodeName = $node->attributes('name');
		}
		//end test

		$value = $this->get($nodeName, $node->attributes('default'), $group, $outPutFormat, $counter);

		if ($outPutFormat == 'array' && !is_null($counter)) {
			$value = JArrayHelper::getValue($value, $counter, '');
		}
		//value must be a string
		$element->_array_counter = $counter;

		$result = $element->render($node, $value, $control_name);

		$reqParamName = $result[5];

		if ($this->_duplicate) { //_duplicate property set in view pages
			if ($type == 'radio') {

				//otherwise only a single entry is recorded no matter how many duplicates we make
				if ($counter == 0 && isset($this->_counter_override)) {
					$counter = $this->_counter_override;
				}
				$replacewith = "[$reqParamName][$counter][]";
			} else {
				$replacewith = "[$reqParamName][]";
			}
			$result[1] = str_replace("[$reqParamName]", $replacewith, $result[1]);
		}

		return $result;
	}

	/**
	 * Render
	 *
	 * @access	public
	 * @param	string	The name of the control, or the default text area if a setup file is not found
	 * @param string group
	 * @param bol write out or return
	 * @param int if set and group is repeat only return int row from rendered params
	 * used for form plugin admin pages.
	 * @return	string	HTML
	 *
	 * NOTE when rendering admin settings I *think* the repeat group is set with $this->_counter_override

	 * @since	1.5
	 */
	function render($name = 'params', $group = '_default', $write = true, $repeatSingleVal = null)
	{
		$return = '';
		$this->_group = $group;
		//$$$rob experimental again
		//problem - when rendering plugin params - e.g. calendar vis - params like the table drop down
		// are repeated n times. I think the best way to deal with this is to get the data recorded for
		// the viz and udpate this objects _xml array duplicate the relavent JSimpleXMLElement Objects
		// for the required number of table drop downs
		//echo " $name : $group <br>";

		$repeat = false;
		$repeatControls = true;
		$repeatMin = 0;
		if (is_array($this->_xml)) {
			if (array_key_exists($group, $this->_xml)) {
				$repeat = $this->_xml[$group]->attributes('repeat');
				$repeatMin = (int)$this->_xml[$group]->attributes('repeatmin');
				$repeatControls = $this->_xml[$group]->attributes('repeatcontrols');
			}
		}
		if ($repeat) {
			//get the name of the first element in the group
			$children = $this->_xml[$group]->children();
			if (empty($children)) {
				$firstElName = '';
				$allParamData = '';
				$value = '';
			} else {
				$firstElName = str_replace("[]", "", $children[0]->attributes('name'));

				$allParamData = $this->_registry['_default']['data'];

				$value = $this->get($firstElName, array(), $group, 'array');
			}


			$c = 0;

			//limit the number of groups of repeated params written out
			if (!is_null($repeatSingleVal) && is_int($repeatSingleVal)) {
				$total = $repeatSingleVal + 1;
				$start = $repeatSingleVal;
			} else {
				$total = count($value);
				$start = 0;
			}
			$return .= '<div id="container'.$this->_identifier.'">';
				//add in the 'add' button to duplicate the group
			//only show for first added group
			if ($repeatControls && $repeatSingleVal == 0) {
				$return .= "<a href='#' class='addButton'>" . JText::_('COM_FABRIK_ADD') . "</a>";
			}
			for ($x=$start; $x<$total; $x++) {
				//call render for the number of time the group is repeated
				//echo parent::render($name, $group);

				$return .= '<div class="repeatGroup" id="'.$this->_identifier . 'group-'.$x.'">';
				////new
				//$this->_counter_override = $x;
				$params = $this->getParams($name, $group, 'array', $x);

				$html = array();
				$html[] = '<table width="100%" class="paramlist admintable" cellspacing="1">';

				if ($description = $this->_xml[$group]->attributes('description')) {
					// add the params description to the display
					$desc	= JText::_($description);
					$html[]	= '<tr><td class="paramlist_description" colspan="2">'.$desc.'</td></tr>';
				}
				foreach ($params as $param)
				{
					$html[] = '<tr>';

					if ($param[0]) {
						$html[] = '<td width="40%" class="paramlist_key"><span class="editlinktip">'.$param[0].'</span></td>';
						$html[] = '<td class="paramlist_value">'.$param[1].'</td>';
					} else {
						$html[] = '<td class="paramlist_value" colspan="2">'.$param[1].'</td>';
					}

					$html[] = '</tr>';
				}

				if (count($params ) < 1) {
					$html[] = "<tr><td colspan=\"2\"><i>".JText::_('COM_FABRIK_THERE_ARE_NO_PARAMETERS_FOR_THIS_ITEM')."</i></td></tr>";
				}

				$html[] = '</table>';
				if ($repeatControls) {
					$html[]= "<a href='#' class=\"removeButton delete\">" . JText::_('COM_FABRIK_DELETE') . "</a>";
				}
				$return .= implode("\n", $html);

				///end new
				$c ++;
				$return .= "</div>";
			}
			$return .= "</div>";
		} else {
			$return .= parent::render($name, $group);
		}

		if ($repeat && $repeatControls && ($repeatSingleVal == null || $repeatSingleVal == 0)) {
			FabrikHelperHTML::script('components/com_fabrik/libs/params.js');
			// watch add and remove buttons
			$document = JFactory::getDocument();
			$script = "head.ready(function() {
			 new RepeatParams('container{$this->_identifier}', {repeatMin:$repeatMin});
	});";
			FabrikHelperHTML::addScriptDeclaration($script);
		}
		if ($write) {
			echo $return;
		} else {
			return $return;
		}
	}

	/**
	 * @since fabrik 3.0
	 * get the child nodes
	 * @param $namespace
	 */

	public function getChildren($namespace = '_default')
	{
		return $this->_xml[$namespace]->children();
	}

}
?>