<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       1.6
 */

// No direct access
defined('_JEXEC') or die;

$db = JFactory::getDbo();

// Get the package id from #__fabrik_packages for this opton
$query = $db->getQuery(true);
$app = JFactory::getApplication();
$input = $app->input;
$option = $input->get('option');
$shortName = JString::substr($option, 4);
$query->select('id')->from('#__fabrik_packages')
->where('(component_name = ' . $db->quote($option) . ' OR component_name = ' . $db->quote($shortName) . ') AND external_ref <> ""')
->order('version DESC');
$db->setQuery($query, 0, 1);
$id = $db->loadResult();
if ($id == '')
{
	JError::raiseError(500, 'Could not load package');
}
$input->set('id', $id);

// Include dependancies
jimport('joomla.application.component.controller');
jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');

// Set the user state to load the package db tables
$app->setUserState('com_fabrik.package', $option);

JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fabrik/tables');
JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_fabrik/models');
$input->set('task', 'package.view');
$config = array();
$config['base_path'] = JPATH_SITE . '/components/com_fabrik/';

$controller = JControllerLegacy::getInstance('Fabrik', $config);
$controller->execute($input->getCmd('task'));
$controller->redirect();
