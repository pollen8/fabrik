<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');

// Load front end language file as well
$lang = JFactory::getLanguage();
$lang->load('com_fabrik', JPATH_SITE . '/components/com_fabrik');

if (!defined('COM_FABRIK_FRONTEND'))
{
	JError::raiseError(400, JText::_('COM_FABRIK_SYSTEM_PLUGIN_NOT_ACTIVE'));
}
jimport('joomla.application.component.model');
jimport('joomla.application.component.helper');
JModelLegacy::addIncludePath(COM_FABRIK_FRONTEND . '/models', 'FabrikFEModel');

$app = JFactory::getApplication();
$j3 = FabrikWorker::j3();
require_once COM_FABRIK_FRONTEND . '/controller.php';
require_once COM_FABRIK_FRONTEND . '/controllers/list.php';

// $$$rob looks like including the view does something to the layout variable
$input = $app->input;
$origLayout = $input->get('layout');
require_once COM_FABRIK_FRONTEND . '/views/list/view.html.php';
$input->set('layout', $origLayout);

require_once COM_FABRIK_FRONTEND . '/views/package/view.html.php';
JModelLegacy::addIncludePath(COM_FABRIK_FRONTEND . '/models');
JTable::addIncludePath(COM_FABRIK_BASE . '/administrator/components/com_fabrik/tables');
$document = JFactory::getDocument();
require_once COM_FABRIK_FRONTEND . '/controllers/package.php';
require_once COM_FABRIK_FRONTEND . '/views/form/view.html.php';
$listId = intval($params->get('list_id', 0));
if ($listId === 0)
{
	JError::raiseError(500, 'no list specified');
}
$listels = json_decode($params->get('list_elements'));
if (isset($listels->show_in_list))
{
	$input->set('fabrik_show_in_list', $listels->show_in_list);
}

$useajax = $params->get('useajax');
$random = intval($params->get('radomizerecords', 0));
$limit = intval($params->get('limit', 0));
$defaultLayout = $j3 ? 'bootstrap' : 'default';
$layout	= $params->get('fabriklayout', $defaultLayout);
if ($layout == '-1')
{
	$layout = $defaultLayout;
}
$input->set('layout', $layout);

/* this all works fine for a list
 * going to try to load a package so u can access the form and list
 */
$moduleclass_sfx = $params->get('moduleclass_sfx', '');
$listId = intval($params->get('list_id', 1));

$viewName = 'list';
$viewType = $document->getType();
$controller = new FabrikControllerList;

// Set the default view name from the Request
$view = clone ($controller->getView($viewName, $viewType));

// Push a model into the view
$model = $controller->getModel($viewName, 'FabrikFEModel');
$model->setId($listId);
$model->setRenderContext($module->id);

if ($limit !== 0)
{
	$app->setUserState('com_fabrik.list' . $model->getRenderContext() . '.limitlength', $limit);
	$input->set('limit', $limit);
}

if ($useajax !== '')
{
	$model->set('ajax', $useajax);
}

if ($params->get('ajax_links') !== '')
{
	$listParams = $model->getParams();
	$listParams->set('list_ajax_links', $params->get('ajax_links'));
}

// Set up prefilters - will overwrite ones defined in the list!

$prefilters = JArrayHelper::fromObject(json_decode($params->get('prefilters')));
$conditions = (array) $prefilters['filter-conditions'];
if (!empty($conditions))
{
	$listParams->set('filter-fields', $prefilters['filter-fields']);
	$listParams->set('filter-conditions', $prefilters['filter-conditions']);
	$listParams->set('filter-value', $prefilters['filter-value']);
	$listParams->set('filter-access', $prefilters['filter-access']);
	$listParams->set('filter-eval', JArrayHelper::getValue($prefilters, 'filter-eval'));
}

$model->randomRecords = $random;
if (!JError::isError($model))
{
	$view->setModel($model, true);
}
$view->isMambot = true;

// Display the view
$view->error = $controller->getError();

// Build unique cache id on url, post and user id
$user = JFactory::getUser();
$uri = JFactory::getURI();
$uri = $uri->toString(array('path', 'query'));
$cacheid = serialize(array($uri, $_POST, $user->get('id'), get_class($view), 'display', $listId));
$cache = JFactory::getCache('com_fabrik', 'view');

// F3 cache with raw view gives error
if (in_array($input->get('format'), array('raw', 'csv')))
{
	$view->display();
}
else
{
	$cache->get($view, 'display', $cacheid);
}
$input->set('layout', $origLayout);
$input->set('fabrik_show_in_list', null);
