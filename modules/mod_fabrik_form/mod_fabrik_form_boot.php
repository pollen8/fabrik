<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

jimport('joomla.filesystem.file');

// Load front end language file as well
$lang = JFactory::getLanguage();
$lang->load('com_fabrik', JPATH_BASE . '/components/com_fabrik');

if (!defined('COM_FABRIK_FRONTEND'))
{
	JError::raiseError(400, JText::_('COM_FABRIK_SYSTEM_PLUGIN_NOT_ACTIVE'));
}
$app = JFactory::getApplication();

FabrikHelperHTML::framework();
require_once COM_FABRIK_FRONTEND . '/controllers/form.php';

// $$$rob looks like including the view does something to the layout variable
$origLayout = JRequest::getVar('layout');
require_once COM_FABRIK_FRONTEND . '/views/form/view.html.php';
require_once COM_FABRIK_FRONTEND . '/views/package/view.html.php';
require_once COM_FABRIK_FRONTEND . '/views/list/view.html.php';

JRequest::setVar('layout', $origLayout);

JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fabrik/tables');
JModel::addIncludePath(COM_FABRIK_FRONTEND . '/models', 'FabrikFEModel');

$formId = (int) $params->get('form_id', 1);
$rowid = (int) $params->get('row_id', 0);
$layout = $params->get('template', 'default');
$usersConfig = JComponentHelper::getParams('com_fabrik');
$usersConfig->set('rowid', $rowid);

$usekey = $params->get('usekey', '');
if (!empty($usekey))
{
	$app->input->set('usekey', $usekey);
}

$moduleclass_sfx = $params->get('moduleclass_sfx', '');

$moduleAjax = $params->get('formmodule_useajax', true);

$origView = JRequest::getVar('view');

JRequest::setVar('view', 'form');
$controller = new FabrikControllerForm;

/* $$$rob for table views in category blog layouts when no layout specified in {} the blog layout
 * was being used to render the table - which was not found which gave a 500 error
*/
JRequest::setVar('layout', $layout);

// Display the view
$controller->isMambot = true;
$origFormid = JRequest::getInt('formid');
$ajax = JRequest::getVar('ajax');
JRequest::setVar('formid', $params->get('formid'));

JRequest::setVar('ajax', $moduleAjax);
echo $controller->display();

// Reset the layout and view etc for when the component needs them
JRequest::setVar('formid', $origFormid);
JRequest::setVar('ajax', $ajax);
JRequest::setVar('layout', $origLayout);
JRequest::setVar('view', $origView);