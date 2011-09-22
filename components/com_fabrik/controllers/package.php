<?php
/**
 * Fabrik Package Controller
 *
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

class FabrikControllerPackage extends JController
{
	/* @var int  id used from content plugin when caching turned on to ensure correct element rendered)*/
	var $cacheId = 0;

	/**
	 * Display the view
	 */
	function display()
	{
		$document = JFactory::getDocument();

		$viewName	= JRequest::getVar('view', 'package', 'default', 'cmd');

		$viewType	= $document->getType();

		// Set the default view name from the Request
		$view = &$this->getView($viewName, $viewType);

		//if the view is a package create and assign the table and form views
		$tableView = &$this->getView('list', $viewType);
		$listModel = $this->getModel('list', 'FabrikFEModel');
		$tableView->setModel($listModel, true);
		$view->_tableView = $tableView;

		$view->_formView = &$this->getView('Form', $viewType);
		$formModel = $this->getModel('Form', 'FabrikFEModel');
		$formModel->setDbo(FabrikWorker::getDbo());
		$view->_formView->setModel($formModel, true);

		// Push a model into the view
		$model = &$this->getModel($viewName, 'FabrikFEModel');
		$model->setDbo(FabrikWorker::getDbo());

		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}
		// Display the view
		$view->assign('error', $this->getError());
		$view->display();
	}
}
?>