<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.picklist
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once JPATH_SITE . '/components/com_fabrik/models/element.php';

/**
 * Plugin element to two lists - one to select from the other to select into
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.picklist
 */

class PlgFabrik_ElementPicklist extends PlgFabrik_ElementList
{

	/**
	 * Method to set the element id
	 *
	 * @param   int  $id  element ID number
	 *
	 * @return  void
	 */

	public function setId($id)
	{
		parent::setId($id);
		$params = $this->getParams();

		// Set elementlist params from picklist params
		$params->set('allow_frontend_addto', (bool) $params->get('allowadd', false));
	}

	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           to preopulate element with
	 * @param   int    $repeatCounter  repeat group counter
	 *
	 * @return  string	elements html
	 */

	public function render($data, $repeatCounter = 0)
	{
		$name = $this->getHTMLName($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$element = $this->getElement();
		$params = $this->getParams();
		$arVals = $this->getSubOptionValues();
		$arTxt = $this->getSubOptionLabels();
		$arSelected = (array) $this->getValue($data, $repeatCounter);
		$errorCSS = $this->elementError != '' ? " elementErrorHighlight" : '';
		$attribs = 'class="picklistcontainer' . $errorCSS . "\"";
		$style = ".frompicklist, .topicklist{\n" . "background-color:#efefef;\n" . "padding:5px !important;\n" . "}\n" . "\n"
			. "div.picklistcontainer{\n" . "width:40%;\n" . "margin-right:10px;\n" . "margin-bottom:10px;\n" . "float:left;\n" . "}\n" . "\n"
			. ".frompicklist li, .topicklist li, li.picklist{\n" . "background-color:#FFFFFF;\n" . "margin:3px;\n" . "padding:5px !important;\n"
			. "cursor:move;\n" . "}\n" . "\n" . "li.emptyplicklist{\n" . "background-color:transparent;\n" . "cursor:pointer;\n" . "}";
		FabrikHelperHTML::addStyleDeclaration($style);
		$i = 0;
		$aRoValues = array();
		$fromlist = "from:<ul id=\"$id" . "_fromlist\" class=\"frompicklist\">\n";
		$tolist = "to:<ul id=\"$id" . "_tolist\" class=\"topicklist\">\n";
		foreach ($arVals as $v)
		{
			//$tmptxt = addslashes(htmlspecialchars($arTxt[$i]));
			if (!in_array($v, $arSelected))
			{
				$fromlist .= "<li id=\"{$id}_value_$v\" class=\"picklist\">" . $arTxt[$i] . "</li>\n";
			}
			$i++;
		}
		$i = 0;
		$lookup = array_flip($arVals);
		foreach ($arSelected as $v)
		{
			if ($v == '' || $v == '-')
			{
				continue;
			}
			$k = JArrayHelper::getValue($lookup, $v);
			$tmptxt = addslashes(htmlspecialchars(JArrayHelper::getValue($arTxt, $k)));
			$tolist .= "<li id=\"{$id}_value_$v\" class=\"$v\">" . $tmptxt . "</li>\n";
			$aRoValues[] = $tmptxt;
			$i++;
		}
		if (empty($arSelected))
		{
			$fromlist .= "<li class=\"emptyplicklist\">" . JText::_('PLG_ELEMENT_PICKLIST_DRAG_OPTIONS_HERE') . "</li>\n";
		}
		if (empty($aRoValues))
		{
			$tolist .= "<li class=\"emptyplicklist\">" . JText::_('PLG_ELEMENT_PICKLIST_DRAG_OPTIONS_HERE') . "</li>\n";
		}

		$fromlist .= "</ul>\n";
		$tolist .= "</ul>\n";

		$str = "<div $attribs>$fromlist</div><div class='picklistcontainer'>$tolist</div>";
		$str .= $this->getHiddenField($name, json_encode($arSelected), $id);
		if (!$this->editable)
		{
			return implode(', ', $aRoValues);
		}
		$str .= $this->getAddOptionFields($repeatCounter);
		return $str;
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int  $repeatCounter  repeat group counter
	 *
	 * @return  string
	 */

	public function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$element = $this->getElement();
		$data = $this->getFormModel()->data;
		$arVals = $this->getSubOptionValues();
		$arTxt = $this->getSubOptionLabels();
		$params = $this->getParams();
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts->allowadd = (bool) $params->get('allowadd', false);
		$opts->defaultVal = $this->getValue($data, $repeatCounter);
		;
		//$opts->data = array_combine($arVals, $arTxt);;
		$opts->hovercolour = $params->get('picklist-hovercolour', '#AFFFFD');
		$opts->bghovercolour = $params->get('picklist-bghovercolour', '#FFFFDF');
		$opts = json_encode($opts);
		JText::script('PLG_ELEMENT_PICKLIST_ENTER_VALUE_LABEL');
		return "new FbPicklist('$id', $opts)";
	}

	/**
	 * if the search value isnt what is stored in the database, but rather what the user
	 * sees then switch from the search string to the db value here
	 * overwritten in things like checkbox and radio plugins
	 *
	 * @param   string  $value  filterVal
	 *
	 * @return  string
	 */

	protected function prepareFilterVal($value)
	{
		$arVals = $this->getSubOptionValues();
		$arTxt = $this->getSubOptionLabels();
		for ($i = 0; $i < count($arTxt); $i++)
		{
			if (JString::strtolower($arTxt[$i]) == JString::strtolower($val))
			{
				$val = $arVals[$i];
				return $val;
			}
		}
		return $val;
	}

	/**
	 * Builds an array containing the filters value and condition
	 *
	 * @param   string  $value      initial value
	 * @param   string  $condition  intial $condition
	 * @param   string  $eval       how the value should be handled
	 *
	 * @return  array	(value condition)
	 */

	public function getFilterValue($value, $condition, $eval)
	{
		$value = $this->prepareFilterVal($value);
		$return = parent::getFilterValue($value, $condition, $eval);
		return $return;
	}

	/**
	 * build the filter query for the given element.
	 * Can be overwritten in plugin - e.g. see checkbox element which checks for partial matches
	 *
	 * @param   string  $key            element name in format `tablename`.`elementname`
	 * @param   string  $condition      =/like etc
	 * @param   string  $value          search string - already quoted if specified in filter array options
	 * @param   string  $originalValue  original filter value without quotes or %'s applied
	 * @param   string  $type           filter type advanced/normal/prefilter/search/querystring/searchall
	 *
	 * @return  string	sql query part e,g, "key = value"
	 */

	public function getFilterQuery($key, $condition, $value, $originalValue, $type = 'normal')
	{
		$originalValue = trim($value, "'");
		$this->encryptFieldName($key);
		$str = ' (' . $key . ' ' . $condition . ' ' . $value . ' OR ' . $key . ' LIKE \'%"' . $originalValue . '"%\')';
		return $str;
	}

}
