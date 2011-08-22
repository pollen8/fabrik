<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');


jimport('joomla.application.component.helper');
jimport('joomla.filesystem.file');

if (!defined('COM_FABRIK_FRONTEND')) {
	JError::raiseError(400, JText::_('COM_FABRIK_SYSTEM_PLUGIN_NOT_ACTIVE'));
}
require_once(JPATH_COMPONENT.DS.'controller.php');

//test for YQL & XML document type
// use the format request value to check for document type
$docs = array("yql", "xml");
foreach ($docs as $d) {
	if (JRequest::getCmd("type") == $d) {
		// get the class
		require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_fabrik'.DS.'classes'.DS.$d.'document.php');
		// replace the document
		$document = JFactory::getDocument();
		$docClass = 'JDocument'.strtoupper($d);
		$document = new $docClass();
	}
}

JModel::addIncludePath(JPATH_COMPONENT.DS.'models');
//$$$ rob if you want to you can override any fabrik model by copying it from
// models/ to models/adaptors the copied file will overwrite (NOT extend) the original
JModel::addIncludePath(JPATH_COMPONENT.DS.'models'.DS.'adaptors');

$controllerName = JRequest::getCmd('view');
//check for a plugin controller

//call a plugin controller via the url :
// &c=visualization.calendar

$isplugin = false;
$cName = JRequest::getCmd('controller');
if (JString::strpos($cName, '.') != false)
{
	list($type, $name) = explode('.', $cName);
	if ($type == 'visualization') {
		require_once(JPATH_COMPONENT.DS.'controllers'.DS.'visualization.php');
	}
	$path = JPATH_SITE.DS.'plugins'.DS.'fabrik_'.$type.DS.$name.DS.'controllers'.DS.$name.'.php';
	if (JFile::exists($path)) {
		require_once $path;
		$isplugin = true;
		$controller = $type.$name;
	} else {
		$controller = '';
	}

} else {
	// its not a plugin
	// map controller to view - load if exists

	//$$$ROB was a simple $controller = view, which was giving an error when trying to save a popup
	//form to the calendar viz
	//May simply be the best idea to remove main contoller and have different controllers for each view

	//hack for package
	if (JRequest::getCmd('view') == 'package' || JRequest::getCmd('view') == 'list') {
		$controller = JRequest::getCmd('view');
	} else {
		$controller = $controllerName;
	}

	$path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
	if (JFile::exists($path)) {
		require_once $path;
	} else {
		$controller = '';
	}
}
// Create the controller if the task is in the form view.task then get
// the specific controller for that class - otherwse use $controller to load
// required controller class
if (strpos(JRequest::getCmd('task'), '.') !== false) {
	$controller = array_shift(explode('.', JRequest::getCmd('task')));
	$classname	= 'FabrikController'.ucfirst($controller);
	$path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
	if (JFile::exists($path)) {
		require_once $path;
		JRequest::setVar('view', $controller); //needed to process J content plugin (form)
		$task = array_pop(explode('.', JRequest::getCmd('task')));
		$controller = new $classname();
	} else {
		$controller = JController::getInstance('Fabrik');
	}

}else{
	$classname	= 'FabrikController'.ucfirst($controller);

	$controller = new $classname();

	$task = JRequest::getCmd('task');
}

if ($isplugin) {
	//add in plugin view
	$controller->addViewPath(JPATH_SITE.DS.'plugins'.DS.'fabrik_'.$type.DS.$name.DS.'views');
	//add the model path
	$modelpaths = JModel::addIncludePath(JPATH_SITE.DS.'plugins'.DS.'fabrik_'.$type.DS.$name.DS.'models');
}
$app = JFactory::getApplication();
$package = JRequest::getVar('package', 'fabrik');
$app->setUserState('com_fabrik.package', $package);

//$package = $app->getUserStateFromRequest('com_fabrik.package', 'package', 'fabrik');
//echo "package = $package <br>";
/**/
// Perform the Request task#
//echo "<pre>";print_r($_POST);
//echo "$classname . $task";exit;
$controller->execute($task);

// Redirect if set by the controller
$controller->redirect();

?>