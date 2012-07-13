<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.facebooklikebox
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once JPATH_SITE . '/components/com_fabrik/models/element.php';

/**
 * Plugin element to render facebook likebox widget
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.facebooklikebox
 */

class PlgFabrik_ElementFblikebox extends PlgFabrik_Element
{

	protected $hasLabel = false;

	protected $fieldDesc = 'INT(%s)';

	protected $fieldSize = '1';

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
		$pageid = $params->get('fblikebox_pageid', 0);
		$stream = $params->get('fblikebox_stream', 1) == 1 ? 'true' : 'false';
		$width = $params->get('fblikebox_width', 300);
		$height = $params->get('fblikebox_height', 300);
		$header = $params->get('fblikebox_header', 1) == 1 ? 'true' : 'false';
		$connections = $params->get('fblikebox_connections', 10);

		// $str .= "<fb:like-box id=\"$pageid\" width=\"$width\" height=\"$height\" connections=\"$connections\" stream=\"$stream\" header=\"$header\" />";
		$str .= '<fb:like-box id="185550966885" width="292" height="440" connections="4" stream="true" header="true" />';
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
		return "new FbLikebox('$id', $opts)";
	}

}
?>