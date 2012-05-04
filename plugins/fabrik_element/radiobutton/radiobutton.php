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
	 * Get the sql for filtering the table data and the array of filter settings
	 * @param string filter value
	 * @return string filter value
	 */

	function prepareFilterVal($val)
	{
		$values = $this->getSubOptionValues();
		$labels = $this->getSubOptionLabels();
		for ($i=0; $i<count($labels); $i++) {
			if (strtolower($labels[$i]) == strtolower($val)) {
				$val = $values[$i];
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
		$params = $this->getParams();
		$element = $this->getElement();
		if (!array_key_exists($element->name, $data)) {
			$sel = $this->getSubInitialSelection();
			$sel = JArrayHelper::getValue($sel, 0, '');
			$arVals = $this->getSubOptionValues();
			$data[$element->name] = array(JArrayHelper::getValue($arVals, $sel, ''));
		}
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
	
	/**
	 * determines the value for the element in the form view
	 * @param array data
	 * @param int when repeating joinded groups we need to know what part of the array to access
	 * @param array options
	 */

	function getValue($data, $repeatCounter = 0, $opts = array())
	{
		$v = parent::getValue($data, $repeatCounter, $opts);
		// $$$ rob see http://fabrikar.com/forums/showthread.php?t=25965
		if (count($v) == 1)
		{
			$v = $v[0];
		}
		return $v;
	}
}
?>