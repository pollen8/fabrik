<?php
/**
 * Plugin element to render facebook recommendations widget
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.facebookrecommendations
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

require_once JPATH_SITE . '/components/com_fabrik/models/element.php';

/**
 * Plugin element to render facebook recommendations widget
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.facebookrecommendations
 * @since       3.0
 */
class PlgFabrik_ElementFbRecommendations extends PlgFabrik_Element
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
	 * @param   array $data          to pre-populate element with
	 * @param   int   $repeatCounter repeat group counter
	 *
	 * @return  string    elements html
	 */

	public function render($data, $repeatCounter = 0)
	{
		$params            = $this->getParams();
		$data->graphApi    = FabrikHelperHTML::facebookGraphAPI($params->get('opengraph_applicationid'));
		$data->domain      = $params->get('fbrecommendations_domain');
		$data->width       = $params->get('fbrecommendations_width', 300);
		$data->height      = $params->get('fbrecommendations_height', 300);
		$data->header      = $params->get('fbrecommendations_header', 1) == 1 ? 'true' : 'false';
		$data->border      = $params->get('fbrecommendations_border', '');
		$data->font        = $params->get('fbrecommendations_font', 'arial');
		$data->colorscheme = $params->get('fbrecommendations_colorscheme', 'light');

		$layout = $this->getLayout('form');

		return $layout->render($data);
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int $repeatCounter Repeat group counter
	 *
	 * @return  array
	 */

	public function elementJavascript($repeatCounter)
	{
		$id   = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);

		return array('FbRecommendations', $id, $opts);
	}
}
