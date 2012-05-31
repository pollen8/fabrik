<?php
/**
 * Plugin element to render fields
 * @package fabrikar
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models'.DS.'element.php');

class plgFabrik_ElementTwitter_profile extends plgFabrik_Element
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
		$data = $this->format($data);
		return parent::renderListData($data, $oAllRowsData);
	}

	/**
	 * take the recorded twitter screen name and parse it through the template
	 * @param string $screenName
	 * @return string|unknown
	 */
	protected function format($screenName)
	{
		if (trim($screenName) == '') {
			return '';
		}

		require_once(COM_FABRIK_FRONTEND.DS.'libs'.DS.'twitter'.DS.'class.twitter.php');
		$twitter = new twitter();
		$params = $this->getParams();
		static $error;
		$tmpl = $params->get('twitter_profile_template');
		$tmpl = str_replace('{screen_name}', $screenName, $tmpl);

		if (!$twitter->twitterAvailable()) {
			if(!isset($error)) {
				$error = true;
				JError::raiseNotice(500, 'Looks like twitters down');
			}
			$tmpl = preg_replace( "/{[^}\s]+}/i", '', $tmpl);
			return $tmpl;
		}
		$user = $twitter->showUser($screenName);

		foreach($user as $k => $v) {
			if (is_object($v)) {
				foreach($v as $k2 => $v2) {
					$tmpl = str_replace('{'.$k.'.'.$k2.'}', $v2, $tmpl);
				}
			} else {
				$tmpl = str_replace('{'.$k.'}', $v, $tmpl);
			}
		}
		$tmpl = preg_replace( "/{[^}\s]+}/i", '', $tmpl);
		return $tmpl;
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
		$maxlength  = $params->get('maxlength');
		if ($maxlength == "0" or $maxlength == "") {
			$maxlength = $size;
		}
		$bits = array();
		// $$$ rob - not sure why we are setting $data to the form's data
		//but in table view when getting read only filter value from url filter this
		// _form_data was not set to no readonly value was returned
		// added little test to see if the data was actually an array before using it
		if (is_array($this->_form->_data)) {
			$data 	=& $this->_form->_data;
		}
		$value 	= $this->getValue($data, $repeatCounter);
		$type = "text";
		if (isset($this->_elementError) && $this->_elementError != '') {
			$type .= " elementErrorHighlight";
		}
		if ($element->hidden == '1') {
			$type = "hidden";
		}
		if (!$this->_editable) {
			$value = $this->format($value);
			return($element->hidden == '1') ? "<!-- " . $value . " -->" : $value;
		}

		$bits['class']		= "fabrikinput inputbox $type";
		$bits['type']		= $type;
		$bits['name']		= $name;
		$bits['id']			= $id;
		//stop "'s from breaking the content out of the field.
		// $$$ rob below now seemed to set text in field from "test's" to "test&#039;s" when failed validation
		//so add false flag to ensure its encoded once only
		// $$$ hugh - the 'double encode' arg was only added in 5.2.3, so this is blowing some sites up
		if (version_compare( phpversion(), '5.2.3', '<')) {
			$bits['value']		= htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
		}
		else {
			$bits['value']		= htmlspecialchars($value, ENT_COMPAT, 'UTF-8', false);
		}
		$bits['size']		= $size;
		$bits['maxlength']	= $maxlength;

		$str = "<input ";
		foreach ($bits as $key=>$val) {
			$str.= "$key = \"$val\" ";
		}
		$str .= " />\n";
		return $str;
	}

	/**
	 * return the javascript to create an instance of the class defined in formJavascriptClass
	 * @return string javascript to create instance. Instance name must be 'el'
	 */

	function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts = json_encode($opts);
		return "new FbTwitter_profile('$id', $opts)";
	}

}
?>