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

require_once(JPATH_SITE . '/components/com_fabrik/models/element.php');

class plgFabrik_ElementFbcomment extends plgFabrik_Element {

	protected $hasLabel = false;

	protected $fieldDesc = 'INT(%s)';

	protected $fieldLength = '1';

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
		//$id = $params->get('fbcomment_uniqueid');
		$href= $params->get('fbcomment_href');
		$width = $params->get('fbcomment_width', 300);
		$num = $params->get('fbcomment_number_of_comments', 10);
		$colour = $params->get('fb_comment_scheme') == '' ? '' : ' colorscheme="dark" ';
		//$str .= "<fb:comments xid=\"$id\" numposts=\"$num\" width=\"$width\" />";
		$str .= '<div id="fb-root"><fb:comments href="'.$href.'" nmigrated="1" um_posts="'.$num.'" width="'.$width.'"'.$colour.'></fb:comments>';

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
		return "new FbComment('$id', $opts)";
	}

}
?>