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

jimport('joomla.application.component.controllerform');

/**
 * Package controller class.
 *
 * @package  Fabrik
 * @since    3.0
 */

class FabrikControllerPackage extends JControllerForm
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_PACKAGE';

	/**
	 * Export package
	 * 
	 * @return  null
	 */

	public function export()
	{
		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		$model = $this->getModel();
		$model->export($cid);
		$ntext = $this->text_prefix . '_N_ITEMS_EXPORTED';
		$this->setMessage(JText::plural($ntext, count($cid)));
		$this->setRedirect('index.php?option=com_fabrik&view=packages');
	}

	/**
	 * View the package editor
	 * 
	 * @return  null
	 */

	public function view()
	{
		$document = JFactory::getDocument();
		$viewType = $document->getType();
		$this->setPath('view', COM_FABRIK_FRONTEND . '/views');
		$viewLayout = JRequest::getCmd('layout', 'default');
		$view = $this->getView('form', $viewType, '');
		$view->isMambot = $this->isMambot;

		// Set the layout
		$view->setLayout($viewLayout);

		// If the view is a package create and assign the table and form views
		$listView = $this->getView('list', $viewType);
		$listModel = $this->getModel('list', 'FabrikFEModel');
		$listView->setModel($listModel, true);
		$view->tableView = $listView;

		$view->formView = $this->getView('Form', $viewType);
		$formModel = $this->getModel('Form', 'FabrikFEModel');
		$formModel->setDbo(FabrikWorker::getDbo());
		$view->formView->setModel($formModel, true);

		// Push a model into the view
		$model = $this->getModel($viewName, 'FabrikFEModel');
		$model->setDbo(FabrikWorker::getDbo());
		if (!JError::isError($model))
		{
			$view->setModel($model, true);
		}

		// @TODO check for cached version
		$view->display();
	}

	/**
	 * list forms
	 * 
	 * @return  null
	 */

	public function listform()
	{
		$document = JFactory::getDocument();
		$this->Form = $this->get('PackageListForm');
		$viewType = $document->getType();
		$view = $this->getView('package', $viewType, '');

		// Push a model into the view
		$model = $this->getModel();
		$db = FabrikWorker::getDbo();
		$model->setDbo($db);
		if (!JError::isError($model))
		{
			$view->setModel($model, true);
		}
		$view->listform();
	}

}
