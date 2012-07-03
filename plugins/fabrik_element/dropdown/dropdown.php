<?php
/**
 * Plugin element to render dropdown
 * @package fabrikar
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class plgFabrik_ElementDropdown extends plgFabrik_ElementList
{

	/**
	 * (non-PHPdoc)
	 * @see plgFabrik_Element::setId()
	 */
	
	public function setId($id)
	{
		parent::setId($id);
		$params = $this->getParams();
		//set elementlist params from dropdown params
		$params->set('allow_frontend_addto', (bool) $params->get('allow_frontend_addtodropdown', false));
		$params->set('allowadd-onlylabel', (bool) $params->get('dd-allowadd-onlylabel', true));
		$params->set('savenewadditions', (bool) $params->get('dd-savenewadditions', false));
	}

	/**
	 * draws the form element
* @param   int		repeat group counter
	 * @return  string	returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		$name = $this->getHTMLName($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$element = $this->getElement();
		$params = $this->getParams();
		$allowAdd = $params->get('allow_frontend_addtodropdown', false);

		$values = $this->getSubOptionValues();
		$labels = $this->getSubOptionLabels();
		$multiple = $params->get('multiple', 0);
		$multisize = $params->get('dropdown_multisize', 3);
		$selected = (array) $this->getValue($data, $repeatCounter);
		$errorCSS = $this->elementError != '' ? " elementErrorHighlight" : '';
		$attribs 	= 'class="fabrikinput inputbox' . $errorCSS . '"';

		if ($multiple == "1")
		{
			$attribs .= ' multiple="multiple" size="' . $multisize . '" ';
		}
		$i = 0;
		$aRoValues 	= array();
		$opts = array();
		foreach ($values as $tmpval)
		{
			$tmpLabel = JArrayHelper::getValue($labels, $i);
			$tmpval = htmlspecialchars($tmpval, ENT_QUOTES); //for values like '1"'
			$opts[] = JHTML::_('select.option', $tmpval, $tmpLabel);
			if (in_array($tmpval, $selected))
			{
				$aRoValues[] = $this->getReadOnlyOutput($tmpval, $tmpLabel);
			}
			$i ++;
		}
		//if we have added an option that hasnt been saved to the database. Note you cant have
		// it not saved to the database and asking the user to select a value and label
		if ($params->get('allow_frontend_addtodropdown', false) && !empty($selected))
		{
			foreach ($selected as $sel)
			{
				if (!in_array($sel, $values) && $sel !== '')
				{
					$opts[] = JHTML::_('select.option', $sel, $sel);
					$aRoValues[] = $this->getReadOnlyOutput($sel, $sel);
				}
			}
		}
		$str = JHTML::_('select.genericlist', $opts, $name, $attribs, 'value', 'text', $selected, $id);
		if (!$this->editable) {
			return implode(', ', $aRoValues);
		}
		$str .= $this->getAddOptionFields($repeatCounter);
		return $str;
	}

	/**
	 * (non-PHPdoc)
	 * @see plgFabrik_Element::elementJavascript()
	 */

	function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$element = $this->getElement();
		$data = $this->getFormModel()->_data;
		$arSelected = $this->getValue($data, $repeatCounter);
		$values = $this->getSubOptionValues();
		$labels = $this->getSubOptionLabels();
		$params = $this->getParams();

		$opts = $this->getElementJSOptions($repeatCounter);
		$opts->allowadd = $params->get('allow_frontend_addtodropdown', false) ? true : false;
		$opts->value = $arSelected;
		$opts->defaultVal = $this->getDefaultValue($data);

		$opts->data = (empty($values) && empty($labels)) ? array() : array_combine($values, $labels);
		$opts = json_encode($opts);
		JText::script('PLG_ELEMENT_DROPDOWN_ENTER_VALUE_LABEL');
		return "new FbDropdown('$id', $opts)";
	}

	/**
	 * (non-PHPdoc)
	 * @see plgFabrik_ElementList::getDefaultValue()
	 */

	function getDefaultValue($data = array())
	{
		$params = $this->getParams();
		if (!isset($this->default))
		{
			if ($this->getElement()->default != '')
			{
				$default = $this->getElement()->default;
				// nasty hack to fix #504 (eval'd default value)
				// where _default not set on first getDefaultValue
				// and then its called again but the results have already been eval'd once and are hence in an array
				if (is_array($default))
				{
					$v = $default;
				}
				else
				{
					$w = new FabrikWorker;
					$default = $w->parseMessageForPlaceHolder($default, $data);
					$v = $params->get('eval', '0') == '1' ? eval($default) : $default;
				}
				if (is_string($v))
				{
					$this->default = explode('|', $v);
				}
				else
				{
					$this->default = $v;
				}
			}
			else
			{
				$this->default = $this->getSubInitialSelection();
			}
		}
		return $this->default;
	}

	/**
	 * (non-PHPdoc)
	 * @see plgFabrik_ElementList::dataConsideredEmpty()
	 */

	function dataConsideredEmpty($data, $repeatCounter)
	{
		// $$$ hugh - $data seems to be an array now?
		if (is_array($data))
		{
			if (empty($data[0]))
			{
				return true;
			}
		}
		else
		{
			if ($data == '' || $data == '-1') {
				return true;
			}
		}
		return false;
	}

	/**
	 * repalce a value with its label
* @param   string	value
	 * @return  string	label
	 */
	
	protected function replaceLabelWithValue($selected)
	{
		$selected = (array) $selected;
		foreach ($selected as &$s)
		{
			$s = str_replace("'", "", $s);
		}
		$element = $this->getElement();
		$vals = $this->getSubOptionValues();
		$labels = $this->getSubOptionLabels();
		$return = array();
		$aRoValues	= array();
		$opts = array();
		$i = 0;
		foreach ($labels as $label)
		{
			if (in_array($label, $selected))
			{
				$return[] = $vals[$i];
			}
			$i++;
		}
		return $return;
	}

	/**
	 * (non-PHPdoc)
	 * @see plgFabrik_Element::getFilterQuery()
	 */

	public function getFilterQuery($key, $condition, $label, $originalValue, $type = 'normal')
	{
		$value = $label;
		if ($type == 'searchall')
		{
			// $$$ hugh - (sometimes?) $label is already quoted, which is causing havoc ...
			$db = JFactory::getDbo();
			$values = $this->replaceLabelWithValue(trim($label,"'"));
			if (empty($values))
			{
				$value = '';
			}
			else
			{
				$value = $values[0];
			}
			if ($value == '') {
				$value = $label;
			}
			if (!preg_match('#^\'.*\'$#', $value))
			{
				$value = $db->quote($value);
			}
		}
		$this->encryptFieldName($key);
		$params = $this->getParams();
		if ($params->get('multiple'))
		{
			$originalValue = trim($value, "'");
			$where1 = ('["' . $originalValue . '",%');
			$where2 = ('%,"' . $originalValue . '",%');
			$where3 = ('%,"' . $originalValue . '"]');

			return ' (' . $key . ' ' . $condition . ' ' . $value .' OR ' . $key . ' LIKE \'' . $where1 .
							'\' OR ' . $key . ' LIKE \'' . $where2 .
							'\' OR ' . $key . ' LIKE \'' . $where3 .
							'\' )';
		}
		else
		{
			return parent::getFilterQuery($key, $condition, $value, $originalValue, $type);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see plgFabrik_ElementList::getValidationWatchElements()
	 */

	function getValidationWatchElements($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$ar = array(
			'id' => $id,
			'triggerEvent' => 'change'
		);
		return array($ar);
	}

}
?>