<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.calendar
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

require_once COM_FABRIK_FRONTEND . '/helpers/params.php';
require_once COM_FABRIK_FRONTEND . '/helpers/string.php';

/**
 * Fabrik Calendar Plug-in Controller
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.calendar
 */

class FabrikControllerVisualizationcalendar extends FabrikControllerVisualization
{
	/**
	 * Display the view
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController  A JController object to support chaining.
	 *
	 * @since   11.1
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$document = JFactory::getDocument();
		$viewName = 'calendar';

		$viewType = $document->getType();
		// Set the default view name from the Request
		$view = $this->getView($viewName, $viewType);

		//create a form view as well to render the add event form.
		//$view->_formView = &$this->getView('Form', $viewType);

		$formModel = JModel::getInstance('Form', 'FabrikFEModel');
		//$view->_formView->setModel($formModel, true);

		parent::display();
		return $this;
	}

	function deleteEvent()
	{
		$model = $this->getModel('calendar');
		$model->deleteEvent();
		$this->getEvents();
	}

	function getEvents()
	{
		$viewName = 'calendar';
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$model = &$this->getModel($viewName);
		$id = $input->getInt('id', $usersConfig->get('visualizationid', $input->getInt('visualizationid', 0)), 'get');
		$model->setId($id);
		echo $model->getEvents();
	}

	function chooseaddevent()
	{
		$document = JFactory::getDocument();
		$viewName = 'calendar';

		$viewType = $document->getType();

		// Set the default view name from the Request
		$view = $this->getView($viewName, $viewType);

		$formModel = $this->getModel('Form', 'FabrikFEModel');
		$view->setModel($formModel);

		// Push a model into the view
		$model = $this->getModel($viewName);

		$view->setModel($model, true);
		$view->chooseaddevent();
	}

	function addEvForm()
	{
		$app = JFactory::getApplication();
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$input = $app->input;
		$listid = $input->getInt('listid');
		$viewName = 'calendar';
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$model = $this->getModel($viewName);
		$id = $input->getInt('visualizationid', $usersConfig->get('visualizationid', 0));
		$model->setId($id);
		$model->setupEvents();
		if (array_key_exists($listid, $model->_events))
		{
			$datefield = $model->_events[$listid][0]['startdate'];
		}
		else
		{
			$config = JFactory::getConfig();
			$prefix = $config->get('dbprefix');
			$datefield = $prefix . 'fabrik_calendar_events___start_date';
		}
		$rowid = $input->getInt('rowid');
		$listModel = JModel::getInstance('list', 'FabrikFEModel');
		$listModel->setId($listid);
		$table = $listModel->getTable();
		$input->set('view', 'form');
		$input->set('formid', $table->form_id);
		$input->set('tmpl', 'component');
		$input->set('ajax', '1');
		$link = 'index.php?option=com_' . $package . '&view=form&formid=' . $table->form_id . '&rowid=' . $rowid . '&tmpl=component&ajax=1';
		$link .= '&jos_fabrik_calendar_events___visualization_id=' . JRequest::getInt('jos_fabrik_calendar_events___visualization_id');
		$link .= '&fabrik_window_id=' . $input->get('fabrik_window_id');

		$start_date = JRequest::getVar('start_date', '');
		if (!empty($start_date))
		{
			$link .= "&$datefield=" . $start_date;
		}
		// $$$ rob have to add this to stop the calendar filtering itself after adding an new event?
		$link .= '&clearfilters=1';
		$this->setRedirect($link);
	}
}
