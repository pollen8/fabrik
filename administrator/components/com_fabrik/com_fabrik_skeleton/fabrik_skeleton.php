<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

$db = JFactory::getDbo();
//get the package id from #__fabrik_packages for this opton
$query = $db->getQuery(true);
$option = JRequest::getCmd('option');
$shortName = substr($option, 4);
$query->select('id')->from('#__fabrik_packages')->where('(component_name = ' . $db->quote($option) . ' OR component_name = ' . $db->quote($shortName) . ') AND external_ref <> ""')->order('version DESC');
$db->setQuery($query, 0, 1);
$id = $db->loadResult();
if ($id == '')
{
	JError::raiseError(500, 'Could not load package');
}
JRequest::setVar('id', $id);

// Include dependancies
jimport('joomla.application.component.controller');
jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');

//set the user state to load the package db tables
$app = JFactory::getApplication();
$app->setUserState('com_fabrik.package', $option);

//echo $app->getUserState('com_fabrik.package');exit;
JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fabrik/tables');
JModel::addIncludePath(JPATH_SITE . '/components/com_fabrik/models');
JRequest::setVar('task', 'package.view');
$config = array();
$config['base_path'] = JPATH_SITE . '/components/com_fabrik/';

$controller = JController::getInstance('Fabrik', $config);
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();

?>