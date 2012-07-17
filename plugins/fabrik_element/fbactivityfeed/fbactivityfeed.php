<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.facebookactivityfeed
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once JPATH_SITE . '/components/com_fabrik/models/element.php';

/**
 * Plugin element to render facebook open graph activity feed widget
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.facebookactivityfeed
 */

class plgFabrik_ElementFbactivityfeed extends plgFabrik_Element
{

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
		$str = FabrikHelperHTML::facebookGraphAPI($params->get('opengraph_applicationid'));
		$domain = $params->get('fbactivityfeed_domain');
		$width = $params->get('fbactivityfeed_width', 300);
		$height = $params->get('fbactivityfeed_height', 300);
		$header = $params->get('fbactivityfeed_header', 1) ? 'true' : 'false';
		$border = $params->get('fbactivityfeed_border', '');
		$font = $params->get('fbactivityfeed_font', 'arial');
		$colorscheme = $params->get('fbactivityfeed_colorscheme', 'light');
		$str .= "<fb:activity site=\"$domain\" width=\"$width\" height=\"$height\" header=\"$header\" colorscheme=\"$colorscheme\" font=\"$font\" border_color=\"$border\" />";
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
		return "new FbActivityfeed('$id', $opts)";
	}

}
