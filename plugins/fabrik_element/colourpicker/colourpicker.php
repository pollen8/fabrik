<?php
/**
 * Plugin element to render colour picker
 * @package fabrikar
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class plgFabrik_ElementColourpicker extends plgFabrik_Element
{

	protected $fieldDesc = 'CHAR(%s)';

	protected $fieldSize = '10';

	/**
	 * shows the data formatted for the table view
	 * @param string data
	 * @param object all the data in the tables current row
	 * @return string formatted value
	 */

	function renderListData($data, $oAllRowsData)
	{
		$data = FabrikWorker::JSONtoData($data, true);
		$str = '';
		foreach ($data as $d) {
			$str .= '<div style="width:15px;height:15px;background-color:rgb('.$d.')"></div>';
		}
		return $str;
	}

	/**
	 * formats the posted data for insertion into the database
	 * @param mixed thie elements posted form data
	 * @param array posted form data
	 */

	function storeDatabaseFormat($val, $data)
	{
		$val = parent::storeDatabaseFormat($val, $data);
		return $val;
	}

	/**
	 * return the javascript to create an instance of the class defined in formJavascriptClass
	 * @param int group repeat counter
	 * @return string javascript to create instance. Instance name must be 'el'
	 */

	function elementJavascript($repeatCounter)
	{
		if (!$this->_editable) {
			return;
		}
		FabrikHelperHTML::addPath(JPATH_SITE.DS.'plugins/fabrik_element/colourpicker/images/', 'image', 'form', false);
		$params = $this->getParams();
		$element = $this->getElement();
		$id = $this->getHTMLId($repeatCounter);
		$data = $this->_form->_data;
		$value = $this->getValue($data, $repeatCounter);
		$vars = explode(",", $value);
		$vars = array_pad( $vars, 3, 0);
		$opts = $this->getElementJSOptions($repeatCounter);
		$c = new stdClass();
		// 14/06/2011 changed over to color param object from ind colour settings
		$c->red = (int)$vars[0];
		$c->green = (int)$vars[1];
		$c->blue = (int)$vars[2];
		$opts->colour = $c;
		$swatch = $params->get('colourpicker-swatch', 'default.js');
		$swatchFile = JPATH_SITE.DS.'plugins'.DS.'fabrik_element'.DS.'colourpicker'.DS.'swatches'.DS.$swatch;
		$opts->swatch = json_decode(JFile::read($swatchFile));
		
		$opts->closeImage = FabrikHelperHTML::image("close.gif", 'form', @$this->tmpl, array(), true);
		$opts->handleImage = FabrikHelperHTML::image("handle.gif", 'form', @$this->tmpl, array(), true);
		$opts->trackImage = FabrikHelperHTML::image("track.gif", 'form', @$this->tmpl, array(), true);
		
		$opts = json_encode($opts);
		return "new ColourPicker('$id', $opts)";
	}

	/**
	 * draws the form element
	 * @param array row data
	 * @param int repeat group counter
	 * @return string returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		$name = $this->getHTMLName($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$value = $this->getValue($data, $repeatCounter);
		$str = array();
		$str[]	= '<div class="fabrikSubElementContainer">';
		$str[] = '<input type="hidden" name="'.$name.'" id="'.$id.'" /><div class="colourpicker_bgoutput" style="float:left;width:20px;height:20px;border:1px solid #333333;background-color:rgb('.$value.')"></div>';
		if ($this->_editable) {
			$str[] = '<div class="colourPickerBackground colourpicker-widget" style="color:#000;z-index:99999;left:200px;background-color:#EEEEEE;border:1px solid #333333;width:390px;padding:0 0 5px 0;"></div>';
		}
		$str[] = '</div>';
		return implode("\n", $str);
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
		return "VARCHAR(30)";
	}

}
?>
