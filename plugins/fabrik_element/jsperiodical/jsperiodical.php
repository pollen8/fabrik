<?php
/**
* Plugin element to js periodical
* @package fabrikar
* @author Rob Clayburn
* @copyright (C) Rob Clayburn
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models'.DS.'element.php');

class plgFabrik_ElementJSPeriodical extends plgFabrik_Element
{
	/**
	 * shows the data formatted for the table view
	 * @param string data
	 * @param object all the data in the tables current row
	 * @return string formatted value
	 */

	function renderListData($data, $oAllRowsData)
	{
		$params = $this->getParams();
		$format = $params->get('text_format_string');
		if ($format  != '') {
			 $str = sprintf($format, $data);
			 $data = eval($str);
		}
		return parent::renderListData($data, $oAllRowsData);
	}

	/**
	 * determines if the element can contain data used in sending receipts, e.g. field returns true
	 */

	function isReceiptElement()
	{
		return true;
	}

		/**
	 * draws the form element
	 * @param array data to preopulate element with
	 * @param int repeat group counter
	 * @return string returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		$name 			= $this->getHTMLName($repeatCounter);
		$id 				= $this->getHTMLId($repeatCounter);
		$params 		=& $this->getParams();
		$element 		= $this->getElement();
		$size 			= $element->width;
		$maxlength  = $params->get('maxlength', 0);
		if ((int)$maxlength === 0) {
			$maxlength = $size;
		}

		$value = $this->getValue($data, $repeatCounter);
		$type = "text";
		if (isset($this->_elementError) && $this->_elementError != '') {
			$type .= " elementErrorHighlight";
		}
		if ($element->hidden == '1') {
			$type = "hidden";
		}
		$sizeInfo =  " size=\"$size\" maxlength=\"$maxlength\"";
		if (!$this->_editable) {
			$format = $params->get('text_format_string');
			if ($format  != '') {
				 $value =  eval(sprintf($format,$value));
			}
			if ($element->hidden == '1') {
				return "<!--" . $value . "-->";
			} else {
				return $value;
			}
		}

		$str = "<input class=\"fabrikinput inputbox $type\" type=\"$type\" name=\"$name\" id=\"$id\" $sizeInfo value=\"$value\" />\n";
		return $str;
	}

	/**
	 * return the javascript to create an instance of the class defined in formJavascriptClass
	 * @return string javascript to create instance. Instance name must be 'el'
	 */

	function elementJavascript($repeatCounter)
	{
		$params = $this->getParams();
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts->code = $params->get('jsperiod_code');
		$opts->period = $params->get('jsperiod_period');
		$opts = json_encode($opts);
		return "new FbJSPeriodical('$id', $opts)";
	}

	/**
	 * defines the type of database table field that is created to store the element's data
	 */

	function getFieldDescription()
	{
		$p = $this->getParams();
		if ($this->encryptMe()) {
			return 'BLOB';
		}
		switch ( $p->get('text_format')) {
			case 'text':
			default:
				$objtype = "VARCHAR(255)";
				break;
			case 'integer':
				$objtype = "INT(" . $p->get('integer_length', 10) . ")";
				break;
			case 'decimal':
				$objtype = "DECIMAL(" . $p->get('integer_length', 10) . "," . $p->get('decimal_length', 2) . ")";
				break;
		}
		return $objtype;
	}

}
?>