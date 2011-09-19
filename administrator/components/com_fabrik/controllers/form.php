<?php
/*
 * @package Joomla.Administrator
 * @subpackage Fabrik
 * @since		1.6
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Form controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_fabrik
 * @since		1.6
 */
class FabrikControllerForm extends JControllerForm
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_FORM';

	/**
	 * show the form in the admin
	 */

	function view()
	{
		$document =& JFactory::getDocument();
		$model = JModel::getInstance('Form', 'FabrikFEModel');
		$viewType	= $document->getType();
		$this->setPath('view', COM_FABRIK_FRONTEND.DS.'views');
		$viewLayout	= JRequest::getCmd('layout', 'default');
		$view = & $this->getView('form', $viewType, '');
		$view->setModel($model, true);

		// Set the layout
		$view->setLayout($viewLayout);

		//todo check for cached version
		JToolBarHelper::title(JText::_('COM_FABRIK_MANAGER_FORMS'), 'forms.png');
		$view->display();
		FabrikHelper::addSubmenu(JRequest::getWord('view', 'lists'));
	}

	/**
	 * handle saving posted form data from the admin pages
	 */

	function process()
	{
		//JModel::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models');
		$model = JModel::getInstance('Form', 'FabrikFEModel');

		$document = JFactory::getDocument();
		$viewName = JRequest::getVar('view', 'form', 'default', 'cmd');
		$viewType = $document->getType();
		$view = &$this->getView( $viewName, $viewType);

		if (!JError::isError($model)) {
			$view->setModel( $model, true);
		}

		$model->setId(JRequest::getInt('formid', 0));

		$this->_isMambot = JRequest::getVar('_isMambot', 0);
		$model->getForm();
		$model->_rowId = JRequest::getVar('rowid', '');

		// Check for request forgeries
		$fbConfig = JComponentHelper::getParams('com_fabrik');
		if ($model->getParams()->get('spoof_check', $fbConfig->get('spoofcheck_on_formsubmission', true)) == true) {
			JRequest::checkToken() or die('Invalid Token');
		}
		if (JRequest::getVar('fabrik_ignorevalidation', 0 ) != 1) { //put in when saving page of form
			if (!$model->validate()) {
				//if its in a module with ajax or in a package
				if (JRequest::getInt('_packageId') !== 0) {
					$data = array('modified' => $model->_modifiedValidationData);
					//validating entire group when navigating form pages
					$data['errors'] = $model->_arErrors;
					echo json_encode($data);
					return;
				}
				if ($this->_isMambot) {
					//store errors in session
					$_SESSION['fabrik']['mambot_errors'][$model->_id] = $model->_arErrors;
					JRequest::setVar('fabrik_referrer', JArrayHelper::getValue($_SERVER, 'HTTP_REFERER', ''), 'post');
					// $$$ hugh - testing way of preserving form values after validation fails with form plugin
					// might as well use the 'savepage' mechanism, as it's already there!
					$this->savepage();
					$this->makeRedirect('', $model);
				} else {
					$view->display();
				}
				return;
			}
		}

		//reset errors as validate() now returns ok validations as empty arrays
		$model->_arErrors = array();

		$defaultAction = $model->process();

		//check if any plugin has created a new validation error
		if (!empty( $model->_arErrors)) {
			FabrikWorker::getPluginManager()->runPlugins('onError', $model);
			$view->display();
			return;
		}

		//one of the plugins returned false stopping the default redirect
		// action from taking place
		if (!$defaultAction) {
			return;
		}
		$listModel = $model->getListModel();
		$tid = $listModel->getTable()->id;

		$msg = $model->getParams()->get('submit-success-msg', JText::_('COM_FABRIK_RECORD_ADDED_UPDATED'));

		if (JRequest::getInt('_packageId') !== 0) {
			$rowid = JRequest::getInt('rowid');
			echo json_encode(array('msg' => $msg, 'rowid' => $rowid));
			return;
		}
		if (JRequest::getVar('format') == 'raw') {
			$url = COM_FABRIK_LIVESITE .'/index.php?option=com_fabrik&view=list&format=raw&listid=' . $tid;
			$this->setRedirect($url, $msg);
		} else {
			$this->makeRedirect($msg, $model);
		}
	}

	/**
	 * generic function to redirect
	 */

	protected function makeRedirect($msg = null, &$model )
	{
		if (is_null($msg)) {
			$msg = JText::_('COM_FABRIK_RECORD_ADDED_UPDATED');
		}
		if (array_key_exists('apply', $model->_formData)) {
			$page = "index.php?option=com_fabrik&task=form.view&formid=".JRequest::getInt('formid')."&listid=".JRequest::getInt('listid')."&rowid=".JRequest::getInt('rowid');
		} else {
			$page = "index.php?option=com_fabrik&task=list.view&cid[]=".$model->getlistModel()->getTable()->id;
		}
		$this->setRedirect($page, $msg);
	}
}