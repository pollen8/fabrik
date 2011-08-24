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
jimport('joomla.filesystem.file');

//load front end language file as well
$lang = JFactory::getLanguage();
$lang->load('com_fabrik', JPATH_SITE.DS.'components'.DS.'com_fabrik');

if (!defined('COM_FABRIK_FRONTEND')) {
	JError::raiseError(400, JText::_('COM_FABRIK_SYSTEM_PLUGIN_NOT_ACTIVE'));
}
jimport('joomla.application.component.model');
jimport('joomla.application.component.helper');
JModel::addIncludePath(COM_FABRIK_FRONTEND.DS.'models', 'FabrikFEModel');

$app = JFactory::getApplication();


require_once(COM_FABRIK_FRONTEND.DS.'controller.php');
require_once(COM_FABRIK_FRONTEND.DS.'controllers'.DS.'visualization.php');

//$$$rob looks like including the view does something to the layout variable
$origLayout = JRequest::getVar('layout');
require_once(COM_FABRIK_FRONTEND.DS.'views'.DS.'list'.DS.'view.html.php');
JRequest::setVar('layout', $origLayout);

require_once(COM_FABRIK_FRONTEND.DS.'views'.DS.'package'.DS.'view.html.php');


JModel::addIncludePath(COM_FABRIK_FRONTEND.DS.'models');
JTable::addIncludePath(COM_FABRIK_BASE.DS.'administrator'.DS.'components'.DS.'com_fabrik'.DS.'tables');
$document = JFactory::getDocument();

require_once(COM_FABRIK_FRONTEND.DS.'controllers'.DS.'package.php');
require_once(COM_FABRIK_FRONTEND.DS.'views'.DS.'form'.DS.'view.html.php');

$id	= intval($params->get('id', 1));
/*this all works fine for a list
 * going to try to load a package so u can access the form and list
 */
$moduleclass_sfx = $params->get('moduleclass_sfx', '');

$viewName = 'visualization';
$db = FabrikWorker::getDbo();
$query = $db->getQuery(true);
$query->select('plugin')->from('#__{package}_visualizations')->where('id = '.(int)$id);
$db->setQuery($query);
$name = $db->loadResult();
$path = COM_FABRIK_FRONTEND.DS.'plugins'.DS.'visualization'.DS.$name.DS.'controllers'.DS.$name.'.php';
if (file_exists($path)) {
	require_once $path;
}else{
	JError::raiseNotice(400, 'could not load viz:'.$name);
	return;
}
$controllerName = 'FabrikControllerVisualization'.$name;
$controller = new $controllerName();
$controller->addViewPath(COM_FABRIK_FRONTEND.DS.'plugins'.DS.'visualization'.DS.$name.DS.'views');
$controller->addViewPath(COM_FABRIK_FRONTEND.DS.'views');

//add the model path
$modelpaths = JModel::addIncludePath(COM_FABRIK_FRONTEND.DS.'plugins'.DS.'visualization'.DS.$name.DS.'models');
$modelpaths = JModel::addIncludePath(COM_FABRIK_FRONTEND.DS.'models');

$controller->display();
/*
$viewType	= $document->getType();

// Set the default view name from the Request
$view = clone($controller->getView($viewName, $viewType));

// Push a model into the view
$model	= $controller->getModel($viewName, 'FabrikFEModel');
$model->setId($id);
if (!JError::isError($model)) {
	$view->setModel($model, true);
}
$view->isMambot = true;
// Display the view
$view->assign('error', $controller->getError());


$post = JRequest::get('post');
//build unique cache id on url, post and user id
$user = JFactory::getUser();
$cacheid = serialize(array(JRequest::getURI(), $post, $user->get('id'), get_class($view), 'display', 0));
$cache = JFactory::getCache('com_fabrik', 'view');
// f3 cache with raw view gives error
if (in_array(JRequest::getCmd('format'), array('raw', 'csv'))) {
	$view->display();
} else {
	$cache->get($view, 'display', $cacheid);
}

//echo $view->display();

JRequest::setVar('layout', $origLayout);*/
?>