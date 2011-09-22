<?php
/**
 * Plugin element to render series of checkboxes
 * @package fabrikar
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class plgFabrik_ElementCheckbox extends plgFabrik_ElementList
{

	var $hasLabel = false;

	/** should the table render functions use html to display the data */
	var $renderWithHTML = true;

	function renderListData_csv( $data, $oAllRowsData )
	{
		$this->renderWithHTML = false;
		$d = $this->renderListData($data, $oAllRowsData);
		$this->renderWithHTML = true;
		return $d;
	}

	/**
	 * @depreciated moved to plgFabrik_ElementList
	 * shows the data formatted for the table view
	 * @param string data
	 * @param object all the data in the tables current row
	 * @return string formatted value
	 */

/*	function renderListData($data, $oAllRowsData)
	{
		$params 	=& $this->getParams();
		$split_str = $params->get('options_split_str', GROUPSPLITTER2);
		$listModel = $this->getListModel();
		$element 	= $this->getElement();
		$values 	= $this->getSubOptionValues();
		$labels 	= $this->getSubOptionLabels();
		//repeat group data
		$gdata = explode(GROUPSPLITTER, $data);
		$uls = array();
		$useIcon = ($params->get('icon_folder') == -1 || $params->get('icon_folder') == '') ? false : true;
		foreach ($gdata as $i => $data) {
			$lis = array();
			$vals = explode(GROUPSPLITTER2, $data);
			foreach ($vals as $val) {
				if ($useIcon) {
					$l = $this->_replaceWithIcons($val);
				}
				if (!$this->iconsSet == true) {
					$l = $this->getLabelForValue($val);
					$l = $this->_replaceWithIcons( $l);
				}
				$l = $listModel->_addLink($l, $this, $oAllRowsData, $i);
				if (trim($l) == '') { $l = '&nbsp';}
				$lis[] = $this->renderWithHTML ? "<li>$l</li>" : $l;
			}
			if (!empty($lis)) {
				if ($this->renderWithHTML) {
					$uls[] = "<ul class=\"fabrikRepeatData\">".implode(" ", $lis)."</ul>";
				} else {
					$uls[] = implode($split_str, $lis);
				}
			}
		}
		return implode(" ", $uls);
	}*/

	/**
	 * render raw data
	 *
	 * @param string data
	 * @param object all the data in the tables current row
	 * @return string formatted value
	 */

	function renderRawTableData($data, $thisRow)
	{
		return json_encode($data);
		/*if (is_array($data)) {
			return implode(GROUPSPLITTER2, $data);
		} else {
			return $data;
		}*/
	}

	/**
	 * draws the form element
	 * @param int repeat group counter
	 * @return string returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		$name 		= $this->getHTMLName($repeatCounter);
		$id 			= $this->getHTMLId($repeatCounter);
		$element 	= $this->getElement();
		$params 	=& $this->getParams();
		$str 			= "<div class=\"fabrikSubElementContainer\" id=\"$id\">";
		$arVals 	= $this->getSubOptionValues();
		$arTxt 		= $this->getSubOptionLabels();

		$options_per_row = intval( $params->get('ck_options_per_row', 0)); // 0 for one line

		$selected = (array)$this->getValue($data, $repeatCounter);
		$aRoValues = array();
		if ($options_per_row > 0) {
			$percentageWidth = floor(floatval(100) / $options_per_row) - 2;
			$div = "<div class=\"fabrik_subelement\" style=\"float:left;width:" . $percentageWidth . "%\">\n";
		}

		for ($ii = 0; $ii < count($arVals); $ii ++) {
			if ($options_per_row > 0) {
				$str .= $div;
			}
			$thisname = FabrikString::rtrimword($name, '[]') . "[$ii]";
			$label = "<span>".$arTxt[$ii]."</span>";
			$value = htmlspecialchars($arVals[$ii], ENT_QUOTES); //for values like '1"'
			$chx = "<input type=\"checkbox\" class=\"fabrikinput checkbox\" name=\"$thisname\" value=\"".$value."\" ";
			if (in_array($arVals[$ii], $selected)) {
				$aRoValues[] = $this->getReadOnlyOutput($arVals[$ii],  $arTxt[$ii]);
				$chx .= " checked=\"checked\" />\n";
			} else {
				$chx .= " />\n";
			}
			$str .= ($params->get('element_before_label')  == '1') ? "<label>".$chx.$label."</label>\n" : "<label>".$label.$chx."</label>\n";
			if ($options_per_row > 0) {
				$str .= "</div> <!-- end row div -->\n";
			}
		}

		if (!$this->_editable) {
			$splitter = ($params->get('icon_folder') != -1 && $params->get('icon_folder') != '') ? ' ' : ', ';
			return implode($splitter, $aRoValues);
		}
		if ($options_per_row > 0) {
			$str .= "<br />";
		}
		$str .="</div>";
		if ($params->get('allow_frontend_addtocheckbox', false)) {
			$onlylabel = $params->get('chk-allowadd-onlylabel');
			$str .= $this->getAddOptionFields($onlylabel, $repeatCounter);
		}
		return $str;
	}

	/**
	 * trigger called when a row is stored
	 * check if new options have been added and if so store them in the element for future use
	 * @param array data to store
	 */

	function onStoreRow($data)
	{
		$element = $this->getElement();
		$params = $this->getParams();
		if ($params->get('chk-savenewadditions') && array_key_exists($element->name . '_additions', $data)) {
			$added = stripslashes($data[$element->name . '_additions']);
			if (trim($added) == '') {
				return;
			}
			$json = new Services_JSON();
			$added = $json->decode($added);
			$arVals = $this->getSubOptionValues();
			$arTxt 	= $this->getSubOptionLabels();
			$found = false;
			foreach ($added as $obj) {
				if (!in_array($obj->val, $arVals)) {
					$arVals[] = $obj->val;
					$found = true;
					$arTxt[] = $obj->label;
				}
			}
			if ($found) {
				// @TODO test if J1.6 / f3
				//$element->sub_values = implode("|", $arVals);
				//$element->sub_labels = implode("|", $arTxt);
				$opts = $params->get('sub_options');
				$opts->sub_values = $arVals;
				$opts->sub_labels = $arTxt;
				$element->params = json_encode($params);
				$element->store();
			}
		}
	}

	/**
	 * return the javascript to create an instance of the class defined in formJavascriptClass
	 * @return string javascript to create instance. Instance name must be 'el'
	 */

	function elementJavascript($repeatCounter)
	{
		$params = $this->getParams();
		$id = $this->getHTMLId($repeatCounter);
		$element = $this->getElement();

		$arVals = (array)$this->getSubOptionValues();
		$arTxt 	= (array)$this->getSubOptionLabels();
		$data 		=& $this->_form->_data;
		$arSelected = $this->getValue($data, $repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);

		$opts->value    = $arSelected;
		$opts->defaultVal  = $this->getDefaultValue($data);

		$opts->data 			= (empty($arVals) && empty($arTxt)) ? array() : array_combine($arVals, $arTxt);
		$opts->allowadd = $params->get('allow_frontend_addtocheckbox', false);
		$opts = json_encode($opts);
		JText::script('PLG_ELEMENT_CHECKBOX_ENTER_VALUE_LABEL');
		return "new FbCheckBox('$id', $opts)";
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
			$data[$element->name] = $params->get('sub_default_value');
		}
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
	 * used in isempty validation rule
	 *
	 * @param array $data
	 * @return bol
	 */

	function dataConsideredEmpty($data, $repeatCounter)
	{
		$data = (array)$data;
		foreach ($data as $d) {
			if ($d != '') {
				return false;
			}
		}
		return true;
	}

	/**
	 * Examples of where this would be overwritten include timedate element with time field enabled
	 * @param int repeat group counter
	 * @return array html ids to watch for validation
	 */

	function getValidationWatchElements($repeatCounter)
	{
		$id 			= $this->getHTMLId($repeatCounter);
		$ar = array(
			'id' 			=> $id,
			'triggerEvent' => 'click'
			);
			return array($ar);
	}

	/**
	 * build the filter query for the given element.
	 * @param $key element name in format `tablename`.`elementname`
	 * @param $condition =/like etc
	 * @param $value search string - already quoted if specified in filter array options
	 * @param $originalValue - original filter value without quotes or %'s applied
	 * @param string filter type advanced/normal/prefilter/search/querystring/searchall
	 * @return string sql query part e,g, "key = value"
	 */

	function getFilterQuery($key, $condition, $value, $originalValue, $type = 'normal')
	{
		$originalValue = trim($value, "'");
		$this->encryptFieldName($key);
		switch ($condition) {
			case '=':
				$str = " ($key $condition $value OR $key LIKE \"$originalValue',%\"".
				" OR $key LIKE \"%:'$originalValue',%\"".
				" OR $key LIKE \"%:'$originalValue'\"".
				" )";
				break;
			default:
				$str = " $key $condition $value ";
				break;
		}
		return $str;
	}

	/**
	 * if no filter condition supplied (either via querystring or in posted filter data
	 * return the most appropriate filter option for the element.
	 * @return string default filter condition ('=', 'REGEXP' etc)
	 */

	function getDefaultFilterCondition()
	{
		return '=';
	}

	/**
	 * this builds an array containing the filters value and condition
	 * @param string initial $value
	 * @param string intial $condition
	 * @param string eval - how the value should be handled
	 * @return array (value condition)
	 */

	function getFilterValue($value, $condition, $eval )
	{
		$value = $this->prepareFilterVal($value);
		$return = parent::getFilterValue($value, $condition, $eval);
		return $return;
	}
	
	/**
	*  can be overwritten in add on classes
	* @param mixed thie elements posted form data
	* @param array posted form data
	*/
	
	function storeDatabaseFormat($val, $data)
	{
		if (is_array($val)) {
			// ensure that array is incremental numeric key -otherwise json_encode turns it into an object
			$val = array_values($val);
		}
		if (is_array($val) || is_object($val)) {
			return json_encode($val);
		} else {
			return $val;
		}
	}

}
?>