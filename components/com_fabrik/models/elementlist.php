<?php

/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');

class plgFabrik_ElementList extends plgFabrik_Element{

	var $hasSubElements = true;

	var $defaults = null;

	protected $fieldDesc = 'TEXT';
	
	protected $inputType = 'radio';

	/**
	 * can be overwritten by plugin class
	 * determines the label used for the browser title
	 * in the form/detail views
	 * @param array data
	 * @param int when repeating joinded groups we need to know what part of the array to access
	 * @param array options
	 * @return string default value
	 */

	public function getTitlePart($data, $repeatCounter = 0, $opts = array())
	{
		$val = $this->getValue($data, $repeatCounter, $opts);
		$labels = $this->getSubOptionLabels();
		$values = $this->getSubOptionValues();
		$str = '';
		if (is_array($val)) {
			foreach ($val as $tmpVal) {
				$key = array_search($tmpVal, $values);
				$str.= ($key === false) ? $tmpVal : $labels[$key];
				$str.= " ";
			}
		} else {
			$str = $val;
		}
		return $str;
	}

	public function getSubInitialSelection()
	{
		$params = $this->getParams();
		$opts = $params->get('sub_options');
		$r = isset($opts->sub_initial_selection) ? (array)$opts->sub_initial_selection : array();
		return $r;
	}

  /**
   * this really does get just the default value (as defined in the element's settings)
   * @return unknown_type
   */

  function getDefaultValue($data = array())
  {
    if (!isset($this->_default)) {
      $this->_default = $this->getSubInitialSelection();
    }
    return $this->_default;
  }

	/**
	 * Get the table filter for the element
	 * @param int counter
	 * @param bol do we render as a normal filter or as an advanced search filter
	 * if normal include the hidden fields as well (default true, use false for advanced filter rendering)
	 * @return string filter html
	 */

	function getFilter($counter = 0, $normal = true)
	{
		$element 	= $this->getElement();
		$values 	= $this->getSubOptionValues();
		$default 	= $this->getDefaultFilterVal($normal, $counter);
		$elName 	= $this->getFullName(false, true, false);
		$htmlid		= $this->getHTMLId() . 'value';
		$table		= $this->getlistModel()->getTable();
		$params		= $this->getParams();
		$v = 'fabrik___filter[list_'.$table->id.'][value]';
		$v .= $normal ? '['.$counter.']' : '[]';

		if (in_array($element->filter_type, array('range', 'dropdown', ''))) {
			$rows = $this->filterValueList($normal);
			JArrayHelper::sortObjects($rows, $params->get('filter_groupby', 'text'));
			if (!in_array('', $values)) {
				array_unshift($rows, JHTML::_('select.option',  '', $this->filterSelectLabel()));
			}
		}

		$attribs = 'class="inputbox fabrik_filter" size="1" ';
		$size = $params->get('filter_length', 20);
		switch ($element->filter_type)
		{
			case "range":
				$default1 = is_array($default)? $default[0] : '';
				$return 	 = JHTML::_('select.genericlist', $rows, $v.'[]', $attribs, 'value', 'text', $default1, $element->name . "_filter_range_0");
				$default1 = is_array($default) ? $default[1] : '';
				$return 	 .= JHTML::_('select.genericlist', $rows, $v.'[]', $attribs, 'value', 'text', $default1 , $element->name . "_filter_range_1");
				break;
			case "dropdown":
			default:
				$return = JHTML::_('select.genericlist', $rows, $v, $attribs, 'value', 'text', $default, $htmlid);
				break;

			case "field":
				if (get_magic_quotes_gpc()) {
					$default = stripslashes($default);
				}
				$default = htmlspecialchars($default);
				$return = "<input type=\"text\" name=\"$v\" class=\"inputbox fabrik_filter\" size=\"$size\" value=\"$default\" id=\"$htmlid\" />";
				break;

			case 'auto-complete':
				if (get_magic_quotes_gpc()) {
					$default = stripslashes($default);
				}
				$default = htmlspecialchars($default);
				$return = "<input type=\"hidden\" name=\"$v\" class=\"inputbox fabrik_filter\" value=\"$default\" id=\"$htmlid\"  />";
				$return .= "<input type=\"text\" name=\"$v-auto-complete\" class=\"inputbox fabrik_filter autocomplete-trigger\" size=\"$size\" value=\"$default\" id=\"$htmlid-auto-complete\" />";
				FabrikHelperHTML::autoComplete($htmlid, $this->getElement()->id, $element->plugin);
				break;
		}
		if ($normal) {
			$return .= $this->getFilterHiddenFields($counter, $elName);
		} else {
			$return .= $this->getAdvancedFilterHiddenFields();
		}
		return $return;
	}

	/**
	 * used to format the data when shown in the form's email
	 * @param mixed element's data
	 * @param array form records data
	 * @param int repeat group counter
	 * @return string formatted value
	 */

	protected function _getEmailValue($value)
	{
		$params 	=& $this->getParams();
		$split_str = $params->get('options_split_str', '');
		$element 	= $this->getElement();
		$values 	= $this->getSubOptionValues();
		$labels 	= $this->getSubOptionLabels();
		$aLabels 	= array();

		if (is_string($value)) {
			$value = array($value);
		}

		if (is_array($value)) {
			foreach ($value as $tmpVal) {
				$key = array_search($tmpVal, $values);
				if ($key !== false) {
					$aLabels[] = $labels[$key];
				}
			}
		}
		if ($split_str == '') {
			$val = "<ul><li>".implode("</li><li>", $aLabels ) . "</li></ul>";
		} else {
			$val = implode($split_str, $aLabels);
		}
		if ($val === '') {
			$val = $params->get('sub_default_label');
		}
		return $val;
	}

	public function filterValueList($normal, $tableName = '', $label = '', $id = '', $incjoin = true)
	{
		$rows = parent::filterValueList($normal, $tableName, $label, $id, $incjoin);
		$this->unmergeFilterSplits($rows);
		$this->reapplyFilterLabels($rows);
		return $rows;
	}

	function autocomplete_options()
	{
		$rows = $this->filterValueList(true);
		$v = addslashes(JRequest::getVar('value'));
		for ($i = count($rows)-1; $i >= 0; $i--) {
			if (!preg_match("/$v(.*)/i", $rows[$i]->text)) {
				unset($rows[$i]);
			}
		}
		$rows = array_values($rows);
		echo json_encode($rows);
	}

/**
	 * shows the data formatted for the table view
	 * @param string data
	 * @param object all the data in the tables current row
	 * @return string formatted value
	 */

	function renderListData($data, $oAllRowsData)
	{
		$element = $this->getElement();
		$params = $this->getParams();
		$listModel = $this->getListModel();
		$multiple = $params->get('multiple', 0) || $this->isJoin();
		$sLabels 	= array();
		//repeat group data
		$gdata = FabrikWorker::JSONtoData($data, true);
		$uls = array();
		$useIcon = $params->get('icon_folder', 0);
		foreach ($gdata as $i => $data) {
			$lis = array();
			$vals = FabrikWorker::JSONtoData($data, true);
			foreach ($vals as $val) {
				$l = $useIcon ? $this->_replaceWithIcons($val) : $val;
				if (!$this->iconsSet == true) {
					$l = $this->getLabelForValue($val);
					$l = $this->_replaceWithIcons($l);
				}
				$l = $this->rollover($l, $oAllRowsData, 'list');
				$l = $listModel->_addLink($l, $this, $oAllRowsData, $i);
				if (trim($l) !== '') {
					$lis[] =  $multiple ? "<li>$l</li>" : $l;
				}
			}
			if (!empty($lis)) {
				$uls[] = $multiple ? '<ul class="fabrikRepeatData">'.implode(' ', $lis).'</ul>' : implode(' ', $lis);
			}
		}
		//$$$rob if only one repeat group data then dont bother encasing it in a <ul>
		return count($gdata) !== 1 ? '<ul class="fabrikRepeatData">'.implode(' ', $uls).'</ul>' : implode(' ', $uls);
	}
	
	/**
	* draws the form element
	* @param array data
	* @param int repeat group counter
	* @return string returns element html
	*/
	
	public function render($data, $repeatCounter = 0)
	{
		$name = $this->getHTMLName($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$params = $this->getParams();
		$values = $this->getSubOptionValues();
		$labels = $this->getSubOptionLabels();
		
		
		$selected = (array)$this->getValue($data, $repeatCounter);
		if (!$this->_editable) {
			$aRoValues = array();
			for ($i = 0; $i < count($values); $i ++) {
				if (in_array($values[$i], $selected)) {
					$aRoValues[] = $this->getReadOnlyOutput($values[$i],  $labels[$i]);
				}
			}
			$splitter = ($params->get('icon_folder') != -1 && $params->get('icon_folder') != '') ? ' ' : ', ';
			return implode($splitter, $aRoValues);
		}
		
		$optionsPerRow = (int)$this->getParams()->get('options_per_row', 0);
		$elBeforeLabel = (bool)$this->getParams()->get('element_before_label', true);
		//element_before_label
		$grid = FabrikHelperHTML::grid($values, $labels, $selected, $name, $this->inputType, $elBeforeLabel, $optionsPerRow);
		
		array_unshift($grid, '<div class="fabrikSubElementContainer" id="'.$id.'">');
		
		$grid[] = '</div>';
		if ($params->get('allow_frontend_addto', false)) {
			$onlylabel = $params->get('allowadd-onlylabel');
			$grid[] = $this->getAddOptionFields($onlylabel, $repeatCounter);
		}
		return implode("\n", $grid);
	}
	
	protected function getElementBeforeLabel()
	{
		return (bool)$this->getParams()->get('radio_element_before_label', true);
	}

	/**
	 * called from within function getValue
	 * needed so we can append _raw to the name for elements such as db joins
	 * @param array $opts
	 * @return string element name inside data array
	 */

	protected function getValueFullName($opts)
	{
		return $this->getFullName(false, true, false);
	}

	/**
	 * determines the value for the element in the form view
	 * @param array data
	 * @param int when repeating joinded groups we need to know what part of the array to access
	 * @param array options
	 */

	function getValue($data, $repeatCounter = 0, $opts = array())
	{
		$data = (array)$data;
		if (!isset($this->defaults)) {
			$this->defaults = array();
		}
		$valueKey = $repeatCounter . serialize($opts);
		if (!array_key_exists($valueKey, $this->defaults)) {
			$default = '';
			$groupModel = $this->_group;
			$group			=& $groupModel->getGroup();
			$joinid			= $group->join_id;
			$formModel 	=& $this->getForm();
			$element		= $this->getElement();
			// $$$rob - if no search form data submitted for the checkbox search element then the default
			// selecton was being applied instead
			$default = JArrayHelper::getValue($opts, 'use_default', true) == false ? '' : $this->getDefaultValue($data);

			$name = $this->getValueFullName($opts);

			if ($groupModel->isJoin()) {
				if ($groupModel->canRepeat()) {
					if (array_key_exists('join', $data) && array_key_exists($joinid, $data['join']) && is_array($data['join'][$joinid]) &&  array_key_exists($name, $data['join'][$joinid]) && array_key_exists($repeatCounter, $data['join'][$joinid][$name])) {
						$default = $data['join'][$joinid][$name][$repeatCounter];
					}
				} else {
					if (array_key_exists('join', $data) && array_key_exists($joinid, $data['join']) && is_array($data['join'][$joinid]) && array_key_exists($name, $data['join'][$joinid])) {
						$default = $data['join'][$joinid][$name];
					}
				}
			} else {
				if ($groupModel->canRepeat()) {
					//can repeat NO join
					if (array_key_exists($name, $data)) {
						if (is_array($data[$name])) {
							//occurs on form submission for fields at least
							$a = $data[$name];
						} else {
							//occurs when getting from the db
							$a = FabrikWorker::JSONtoData($data[$name], true);
						}
						$default = JArrayHelper::getValue($a, $repeatCounter, $default);
					}
				} else {
					if (array_key_exists($name, $data)) {
						if (is_array($data[$name])) {
							//occurs on form submission for fields at least
							$default = $data[$name];
						} else {
							//occurs when getting from the db
							//$$$ rob changed to false below as when saving encrypted data a stored valued of 62
							// was being returned as [62], then [[62]] etc.
							$default = FabrikWorker::JSONtoData($data[$name], false);
						}
					}
				}
			}

			if ($default === '') { //query string for joined data
				$default = JArrayHelper::getValue($data, $name);
			}
			$element->default = $default;
			$formModel = $this->getForm();
			//stops this getting called from form validation code as it messes up repeated/join group validations
			if (array_key_exists('runplugins', $opts) && $opts['runplugins'] == 1) {
				FabrikWorker::getPluginManager()->runPlugins('onGetElementDefault', $formModel, 'form', $this);
			}
			if (is_string($element->default)) {
				//$$$ rob changed to false below as when saving encrypted data a stored valued of 62
				// was being returned as [62], then [[62]] etc.
				$element->default = FabrikWorker::JSONtoData($element->default, false);
			}
			$this->defaults[$valueKey] = $element->default;
		}
		return $this->defaults[$valueKey];
	}

	/**
	 * is the dropdowns cnn the same as the main Joomla db
	 * @return bool
	 */
	protected function inJDb()
	{
		return $this->getlistModel()->inJDb();
	}
	
	/**
	* format the read only output for the page
	* @param string $value
	* @param string label
	* @return string value
	*/
	
	protected function getReadOnlyOutput($value, $label)
	{
		$params = $this->getParams();
		if ($params->get('icon_folder') != -1 && $params->get('icon_folder') != '') {
				$icon = $this->_replaceWithIcons($value);
				if ($this->iconsSet) {
					$label = $icon;
				}
		}
		return $label;
	}

}