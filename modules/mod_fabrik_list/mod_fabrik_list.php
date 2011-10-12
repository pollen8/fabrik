<?php
/**
 * @version
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if (!defined('COM_FABRIK_FRONTEND')) {
	JError::raiseError(400, JText::_('COM_FABRIK_SYSTEM_PLUGIN_NOT_ACTIVE'));
}

jimport('joomla.filesystem.file');
jimport('joomla.application.component.model');
jimport('joomla.application.component.helper');
JModel::addIncludePath(COM_FABRIK_FRONTEND.DS.'models', 'FabrikFEModel');
JModel::addIncludePath(COM_FABRIK_FRONTEND.DS.'models');
JTable::addIncludePath(COM_FABRIK_BASE.DS.'administrator'.DS.'components'.DS.'com_fabrik'.DS.'tables');

require_once(COM_FABRIK_FRONTEND.DS.'controller.php');
require_once(COM_FABRIK_FRONTEND.DS.'controllers'.DS.'list.php');
require_once(COM_FABRIK_FRONTEND.DS.'views'.DS.'list'.DS.'view.html.php');
require_once(COM_FABRIK_FRONTEND.DS.'views'.DS.'package'.DS.'view.html.php');
require_once(COM_FABRIK_FRONTEND.DS.'controllers'.DS.'package.php');
require_once(COM_FABRIK_FRONTEND.DS.'views'.DS.'form'.DS.'view.html.php');

//load front end language file as well
$lang = JFactory::getLanguage();
$lang->load('com_fabrik', JPATH_BASE.DS.'components'.DS.'com_fabrik');

$app = JFactory::getApplication();
$document = JFactory::getDocument();

FabrikHelperHTML::framework();
//$$$rob looks like including the view does something to the layout variable
$origLayout = JRequest::getVar('layout');
JRequest::setVar('layout', $origLayout);


$listId = (int)$params->get('list_id', 1);
$useajax = (int)$params->get('useajax', 0);
$random	= (int)$params->get('radomizerecords', 0);
$limit = (int)$params->get('limit', 0);
$layout	= $params->get('fabriklayout', 'default');

JRequest::setVar('layout', $layout);
if ($limit !== 0) {
	$app->setUserState('com_fabrik.list'.$listId.'.limitlength', $limit);
	JRequest::setVar('limit', $limit);
}

$moduleclass_sfx = $params->get('moduleclass_sfx', '');

$listId = (int)$params->get('list_id', 1);

$viewName = 'list';
$viewType	= $document->getType();
$controller = new FabrikControllerList();

// Set the default view name from the Request
$view = clone($controller->getView($viewName, $viewType));

// Push a model into the view
$model = $controller->getModel($viewName, 'FabrikFEModel');
$model->setId($listId);
$model->randomRecords = $random;
if (!JError::isError($model)) {
	$view->setModel($model, true);
}
$view->isMambot = true;
// Display the view
$view->assign('error', $controller->getError());
echo $view->display();

JRequest::setVar('layout', $origLayout);
?>