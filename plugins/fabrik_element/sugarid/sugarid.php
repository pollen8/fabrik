<?php
/**
* Plugin element to Sugar CRM id
* @package fabrikar
* @author Rob Clayburn
* @copyright (C) Rob Clayburn
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models'.DS.'element.php');

class plgFabrik_ElementSugarid extends plgFabrik_Element
{

	protected $fieldDesc = 'CHAR(%s)';

	protected $fieldSize = '36';

	/**
	 * draws the form element
	 * @param int repeat group counter
	 * @return string returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		$name 		= $this->getHTMLName($repeatCounter);
		$id				= $this->getHTMLId($repeatCounter);
		$params 	=& $this->getParams();
		$element 	= $this->getElement();
		$value 		= $this->getValue($data, $repeatCounter);
		$type = "hidden";
		if (isset($this->_elementError) && $this->_elementError != '') {
			$type .= " elementErrorHighlight";
		}
		if (!$this->_editable) {
			return "<!--" . stripslashes($value) . "-->";
		}
		$hidden = 'hidden';
		$guid = $this->create_guid();
		/* no need to eval here as its done before hand i think ! */
		if ($element->eval == "1" and !isset($data[$name])) {
			$str = "<input class=\"inputbox $type\" type=\"$hidden\" name=\"$name\" id=\"$id\" $sizeInfo value=\"$value\" />\n";
		} else {
			$value = stripslashes($value);
			$str = "<input class=\"inputbox fabrikinput $type\" type=\"$hidden\" name=\"$name\" id=\"$id\" value=\"$value\" />\n";
		}
		return $str;
	}

	/**
	 * formats the posted data for insertion into the database
	 * @param mixed thie elements posted form data
	 * @param array posted form data
	 */

	function storeDatabaseFormat($val, $data )
	{
		if (trim($val) == '') {
			$val = $this->create_guid();
		}
		return $val;
	}


/**
 * A temporary method of generating GUIDs of the correct format for our DB.
 * @return String contianing a GUID in the format: aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee
 *
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 */
function create_guid()
{
	$microTime = microtime();
	list($a_dec, $a_sec) = explode(" ", $microTime);

	$dec_hex = dechex($a_dec* 1000000);
	$sec_hex = dechex($a_sec);

	$this->ensure_length($dec_hex, 5);
	$this->ensure_length($sec_hex, 6);

	$guid = "";
	$guid .= $dec_hex;
	$guid .= $this->create_guid_section(3);
	$guid .= '-';
	$guid .= $this->create_guid_section(4);
	$guid .= '-';
	$guid .= $this->create_guid_section(4);
	$guid .= '-';
	$guid .= $this->create_guid_section(4);
	$guid .= '-';
	$guid .= $sec_hex;
	$guid .= $this->create_guid_section(6);

	return $guid;

}

function create_guid_section($characters)
{
	$return = "";
	for($i=0; $i<$characters; $i++)
	{
		$return .= dechex(mt_rand(0,15));
	}
	return $return;
}

function ensure_length(&$string, $length)
{
	$strlen = strlen($string);
	if($strlen < $length)
	{
		$string = str_pad($string,$length,"0");
	}
	else if($strlen > $length)
	{
		$string = substr($string, 0, $length);
	}
}
}
?>