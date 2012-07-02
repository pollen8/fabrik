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
 * Form controller class.
 *
 * @package  Fabrik
 * @since    3.0
 */

class FabrikControllerForm extends JControllerForm
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_FORM';

	public $isMambot = false;

	/**
	 * Inline Edit
	 * 
	 * @return  null
	 */

	public function inlineedit()
	{
		$document = JFactory::getDocument();
		$model = JModel::getInstance('Form', 'FabrikFEModel');
		$viewType = $document->getType();
		$this->setPath('view', COM_FABRIK_FRONTEND . '/views');
		$viewLayout	= JRequest::getCmd('layout', 'default');
		$view = $this->getView('form', $viewType, '');
		$view->setModel($model, true);

		// Set the layout
		$view->setLayout($viewLayout);

		// @TODO check for cached version
		$view->inlineEdit();
	}

	/**
	 * handle saving posted form data from the admin pages
	 * 
	 * @return null
	 */

	public function process()
	{
		$document = JFactory::getDocument();
		$viewName = JRequest::getVar('view', 'form', 'default', 'cmd');
		$model = JModel::getInstance('Form', 'FabrikFEModel');
		$viewType = $document->getType();

		// For now lets route this to the html view.
		$view = $this->getView($viewName, 'html');
		if (!JError::isError($model))
		{
			$view->setModel($model, true);
		}
		$model->setId(JRequest::getInt('formid', 0));
		$this->isMambot = JRequest::getVar('_isMambot', 0);
		$model->getForm();
		$model->rowId = JRequest::getVar('rowid', '');

		// Check for request forgeries
		if ($model->spoofCheck())
		{
			JRequest::checkToken() or die('Invalid Token');
		}
		if (!$model->validate())
		{
			// If its in a module with ajax or in a package
			if (JRequest::getInt('_packageId') !== 0)
			{
				$data = array('modified' => $model->modifiedValidationData);

				// Validating entire group when navigating form pages
				$data['errors'] = $model->errors;
				echo json_encode($data);
				return;
			}
			if ($this->isMambot)
			{
				JRequest::setVar('fabrik_referrer', JArrayHelper::getValue($_SERVER, 'HTTP_REFERER', ''), 'post');
				/**
				 * $$$ hugh - testing way of preserving form values after validation fails with form plugin
				 * might as well use the 'savepage' mechanism, as it's already there!
				 */
				$this->savepage();
				$this->makeRedirect($model, '');
			}
			else
			{
				$view->display();
			}
			return;
		}

		// Reset errors as validate() now returns ok validations as empty arrays
		$model->errors = array();

		$defaultAction = $model->process();

		// Check if any plugin has created a new validation error
		if (!empty($model->errors))
		{
			FabrikWorker::getPluginManager()->runPlugins('onError', $model);
			$view->display();
			return;
		}

		// One of the plugins returned false stopping the default redirect action from taking place
		if (!$defaultAction)
		{
			return;
		}
		$listModel = $model->getListModel();
		$tid = $listModel->getTable()->id;
		$msg = $model->getParams()->get('submit-success-msg', JText::_('COM_FABRIK_RECORD_ADDED_UPDATED'));
		$msg = $model->getParams()->get('suppress_msgs', '0') == '0' ? $msg : '';
		if (JRequest::getInt('elid') !== 0)
		{
			// Inline edit show the edited element
			echo $model->inLineEditResult();
			return;
		}
		if (JRequest::getInt('_packageId') !== 0)
		{
			$rowid = JRequest::getInt('rowid');
			echo json_encode(array('msg' => $msg, 'rowid' => $rowid));
			return;
		}
		if (JRequest::getVar('format') == 'raw')
		{
			$url = 'index.php?option=com_fabrik&view=list&format=raw&listid=' . $tid;
			$this->setRedirect($url, $msg);
		}
		else
		{
			$this->makeRedirect($model, $msg);
		}
	}

	/**
	 * generic function to redirect
	 * 
	 * @param   object  $model  J model
	 * @param   string  $msg    Optional redirect message
	 * 
	 * @return  null
	 */

	protected function makeRedirect($model, $msg = null)
	{
		if (is_null($msg))
		{
			$msg = JText::_('COM_FABRIK_RECORD_ADDED_UPDATED');
		}
		if (array_key_exists('apply', $model->_formData))
		{
			$page = 'index.php?option=com_fabrik&task=form.view&formid=' . JRequest::getInt('formid') .
			'&listid=' . JRequest::getInt('listid') . '&rowid=' . JRequest::getInt('rowid');
		}
		else
		{
			$page = 'index.php?option=com_fabrik&task=list.view&cid[]=' . $model->getlistModel()->getTable()->id;
		}
		$this->setRedirect($page, $msg);
	}
}
