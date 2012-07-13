<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.facebookcomment
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once JPATH_SITE . '/components/com_fabrik/models/element.php';

/**
 * Plugin element to render facebook open graph comment widget
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.facebookcomment
 */

class PlgFabrik_ElementFbcomment extends PlgFabrik_Element
{

	protected $hasLabel = false;

	protected $fieldDesc = 'INT(%s)';

	protected $fieldLength = '1';

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
		$params = $this->getParams();
		$str = FabrikHelperHTML::facebookGraphAPI($params->get('opengraph_applicationid'));
		//$id = $params->get('fbcomment_uniqueid');
		$href = $params->get('fbcomment_href');
		$width = $params->get('fbcomment_width', 300);
		$num = $params->get('fbcomment_number_of_comments', 10);
		$colour = $params->get('fb_comment_scheme') == '' ? '' : ' colorscheme="dark" ';
		//$str .= "<fb:comments xid=\"$id\" numposts=\"$num\" width=\"$width\" />";
		$str .= '<div id="fb-root"><fb:comments href="' . $href . '" nmigrated="1" um_posts="' . $num . '" width="' . $width . '"' . $colour
			. '></fb:comments>';

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
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts = json_encode($opts);
		return "new FbComment('$id', $opts)";
	}

}
?>