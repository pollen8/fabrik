<?php
/**
 * Plugin element to render facebook likebox widget
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.facebooklikebox
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

require_once JPATH_SITE . '/components/com_fabrik/models/element.php';

/**
 * Plugin element to render facebook likebox widget
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.facebooklikebox
 * @since       3.0
 */

class PlgFabrik_ElementFbLikeBox extends PlgFabrik_Element
{
	/**
	 * Does the element have a label
	 *
	 * @var bool
	 */
	protected $hasLabel = false;

	/**
	 * Db table field type
	 *
	 * @var  string
	 */
	protected $fieldDesc = 'INT(%s)';

	/**
	 * Db table field size
	 *
	 * @var  string
	 */
	protected $fieldLength = '1';

	/**
	 * Draws the html form element
	 *
	 * @param   array  $data           to pre-populate element with
	 * @param   int    $repeatCounter  repeat group counter
	 *
	 * @return  string	elements html
	 */

	public function render($data, $repeatCounter = 0)
	{
		$params = $this->getParams();
		$layout = $this->getLayout('form');
		$data = new stdClass;

		$data->graphApi = FabrikHelperHTML::facebookGraphAPI($params->get('opengraph_applicationid'));
		$data->pageid = 185550966885;//$params->get('fblikebox_pageid', 0);
		$data->stream = $params->get('fblikebox_stream', 1) == 1 ? 'true' : 'false';
		$data->width = $params->get('fblikebox_width', 300);
		$data->height = $params->get('fblikebox_height', 300);
		$data->header = $params->get('fblikebox_header', 1) == 1 ? 'true' : 'false';
		$data->connections = $params->get('fblikebox_connections', 10);

		//return '<fb:like-box id="185550966885" width="292" height="440" connections="4" stream="true" header="true" />';
		return $layout->render($data);
		//$str .= '<fb:like-box id="185550966885" width="292" height="440" connections="4" stream="true" header="true" />';
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int  $repeatCounter  Repeat group counter
	 *
	 * @return  array
	 */

	public function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);

		return array('FbLikebox', $id, $opts);
	}
}
