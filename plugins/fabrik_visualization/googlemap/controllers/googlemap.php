<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * Contact Component Controller
 *
 * @static
 * @package		Joomla
 * @subpackage	Fabrik
 * @since 1.5
 */

class FabrikControllerVisualizationgooglemap extends FabrikControllerVisualization
{
	function ajax_getMarkers($tmpl = 'default')
	{
		$viewName = 'googlemap';
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$model = $this->getModel($viewName);
		//$id = JRequest::getInt('id', $usersConfig->get('visualizationid', JRequest::getInt('visualizationid', 0)), 'get');
		$id = JRequest::getInt('visualizationid', 0);
		$model->setId($id);
		$model->setListIds();
		$model->onAjax_getMarkers();
	}
}
?>