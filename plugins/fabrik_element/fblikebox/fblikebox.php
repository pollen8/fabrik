<?php
/**
 * Plugin element to render facebook open graph activity feed widget
 * @package fabrikar
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models'.DS.'element.php');

class plgFabrik_ElementFblikebox extends plgFabrik_Element {

	var $hasLabel = false;

	protected $fieldDesc = 'INT(%s)';

	protected $fieldSize = '1';

	/**
	 * draws the form element
	 * @param array data to pre-populate element with
	 * @param int repeat group counter
	 * @return string returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		$params = $this->getParams();
		$str = FabrikHelperHTML::facebookGraphAPI( $params->get('opengraph_applicationid'));
		$pageid = $params->get('fblikebox_pageid', 0);
		$stream = $params->get('fblikebox_stream', 1) == 1 ? 'true' : 'false';
		$width = $params->get('fblikebox_width', 300);
		$height = $params->get('fblikebox_height', 300);
		$header = $params->get('fblikebox_header', 1) == 1 ? 'true' : 'false';
		$connections = $params->get('fblikebox_connections', 10);
		//$str .= "<fb:like-box id=\"$pageid\" width=\"$width\" height=\"$height\" connections=\"$connections\" stream=\"$stream\" header=\"$header\" />";
		$str .= "<fb:like-box id=\"185550966885\" width=\"292\" height=\"440\" connections=\"4\" stream=\"true\" header=\"true\" />";
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
		return "new FbLikebox('$id', $opts)";
	}

}
?>