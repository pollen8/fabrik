<?php
/**
 * Plugin element to a series of radio buttons
 * @package fabrikar
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class plgFabrik_ElementRadiobutton extends plgFabrik_ElementList
{

	var $hasLabel = false;

	public function setId($id)
	{
		parent::setId($id);
		$params = $this->getParams();
		//set elementlist params from radio params
		$params->set('element_before_label', (bool)$params->get('radio_element_before_label', true));
		$params->set('allow_frontend_addto', (bool)$params->get('allow_frontend_addtoradio', false));
		$params->set('allowadd-onlylabel', (bool)$params->get('rad-allowadd-onlylabel', true));
		$params->set('savenewadditions', (bool)$params->get('rad-savenewadditions', false));
	}
	
	/**
	 * get the radio buttons possible values
	 * @return array of radio button values
	 */

	protected function getSubOptionValues()
	{
		return parent::getSubOptionValues();
	}

	/**
	 * can be overwritten by plugin class
	 * determines the label used for the browser title
	 * in the form/detail views
	 * @param array data
	 * @param int when repeating joinded groups we need to know what part of the array to access
	 * @param array options
	 * @return string default value
	 */

	function getTitlePart($data, $repeatCounter = 0, $opts = array())
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

	/**
	 * used to format the data when shown in the form's email
	 * @param array radio button ids
	 * @return string formatted value
	 */

	protected function _getEmailValue($value)
	{
		if (empty($value)) {
			return '';
		}
		$labels = $this->getSubOptionLabels();
		$values = $this->getSubOptionValues();
		$key = array_search($value[0], $values);
		$val = ($key === false) ? $value[0] : $labels[$key];
		return $val;
	}

	/**
	 * return the javascript to create an instance of the class defined in formJavascriptClass
	 * @return string javascript to create instance. Instance name must be 'el'
	 */

	function elementJavascript($repeatCounter)
	{
		$params = $this->getParams();
		$id = $this->getHTMLId($repeatCounter);
		$data = $this->_form->_data;
		$arVals = $this->getSubOptionValues();
		$arTxt = $this->getSubOptionLabels();
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts->value  = $this->getValue($data, $repeatCounter);
		$opts->defaultVal = $this->getDefaultValue($data);
		$opts->data = empty($arVals) ? array() : array_combine($arVals, $arTxt);
		$opts->allowadd = $params->get('allow_frontend_addtoradio', false) ? true : false;
		$opts = json_encode($opts);
		JText::script('PLG_ELEMENT_RADIO_ENTER_VALUE_LABEL');
		return "new FbRadio('$id', $opts)";
	}

	/**
	 * Get the table filter for the element
	 * @return string filter html
	 */

	function getFilter($counter = 0, $normal = true)
	{
		$listModel = $this->getlistModel();
		$groupModel	= $this->getGroup();
		$table = $listModel->getTable();
		$element = $this->getElement();
		$params = $this->getParams();
		$elName	= $this->getFullName(false, true, false);
		$htmlid	= $this->getHTMLId() . 'value';
		$v = 'fabrik___filter[list_'.$table->id.'][value]';
		$v .= ($normal) ? '['.$counter.']' : '[]';
		$values = $this->getSubOptionValues();
		//corect default got
		$default = $this->getDefaultFilterVal($normal, $counter);

		if (!$normal || in_array($element->filter_type, array('range', 'dropdown'))) {
			$rows = $this->filterValueList($normal);
			$this->unmergeFilterSplits($rows);
			$this->reapplyFilterLabels($rows);
			if (!in_array('', $values)) {
				array_unshift($rows, JHTML::_('select.option',  '', $this->filterSelectLabel()));
			}
		}
		$size = $params->get('filter_length', 20);
		switch ($element->filter_type)
		{
			case "range":
				if (!is_array($default)) {
					$default = array('', '');
				}

				$attribs = 'class="inputbox" size="1" ';
				$return = JHTML::_('select.genericlist', $rows , $v.'[]', 'class="inputbox fabrik_filter" size="1" ', 'value', 'text', $default[0], $element->name . "_filter_range_0");
				$return .= JHTML::_('select.genericlist', $rows , $v.'[]', 'class="inputbox fabrik_filter" size="1" ', 'value', 'text', $default[1], $element->name . "_filter_range_0");
				break;

			case "dropdown":
			default:
			case '':
				$return = JHTML::_('select.genericlist', $rows, $v, 'class="inputbox fabrik_filter" size="1" ', 'value', 'text', $default, $htmlid);
				break;

			case "field":
				$return = "<input type=\"text\" class=\"inputbox fabrik_filter\" name=\"$v\" value=\"$default\" size=\"$size\" id=\"$htmlid\" />";
				break;

			case 'auto-complete':
				$return = "<input type=\"hidden\" name=\"$v\" class=\"inputbox fabrik_filter\" value=\"$default\" id=\"$htmlid\"  />";
				$return .= "<input type=\"text\" name=\"$v-auto-complete\" class=\"inputbox fabrik_filter autocomplete-trigger\" size=\"$size\" value=\"$default\" id=\"$htmlid-auto-complete\"  />";
				FabrikHelperHTML::autoComplete($htmlid, $this->getElement()->id, 'radiobutton');
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
	 * Get the sql for filtering the table data and the array of filter settings
	 * @param string filter value
	 * @return string filter value
	 */

	function prepareFilterVal($val)
	{
		$arVals = $this->getSubOptionValues();
		$arTxt 	= $this->getSubOptionLabels();
		for ($i=0; $i<count($arTxt); $i++) {
			if (strtolower($arTxt[$i]) == strtolower($val)) {
				$val =  $arVals[$i];
				return $val;
			}
		}
		return $val;
	}

	/**
	 * OPTIONAL
	 * If your element risks not to post anything in the form (e.g. check boxes with none checked)
	 * the this function will insert a default value into the database
	 * @param array form data
	 * @return array form data
	 */

	function getEmptyDataValue(&$data)
	{
		$params 					=& $this->getParams();
		$element = $this->getElement();
		if (!array_key_exists($element->name, $data)) {
			$sel = $this->getSubInitialSelection();
			$sel = $sel[0];
			$arVals = $this->getSubOptionValues();
			$data[$element->name] = array($arVals[$sel]);
		}
	}

	/**
	 *
	 * Examples of where this would be overwritten include drop downs whos "please select" value might be "-1"
	 * @param array data posted from form to check
	 * @return bol if data is considered empty then returns true
	 */

	function dataConsideredEmpty($data, $repeatCounter)
	{
		if (is_array($data)) {
			foreach ($data as $d) {
				if ($d !== '') {
					return false;
				}
			}
			return true;
		}
		return $data === '';
	}

	/**
	 * Examples of where this would be overwritten include timedate element with time field enabled
	 * @param int repeat group counter
	 * @return array html ids to watch for validation
	 */

	function getValidationWatchElements($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$ar = array(
			'id'=> $id,
			'triggerEvent' => 'click'
			);
			return array($ar);
	}

	/**
	 * this builds an array containing the filters value and condition
	 * @param string initial $value
	 * @param string intial $condition
	 * @param string eval - how the value should be handled
	 * @return array (value condition)
	 */

	function getFilterValue($value, $condition, $eval)
	{
		$value = $this->prepareFilterVal($value);
		$return = parent::getFilterValue($value, $condition, $eval);
		return $return;
	}

	public function canToggleValue()
	{
		return count($this->getSubOptionValues()) < 3 ? true : false;
	}
}
?>