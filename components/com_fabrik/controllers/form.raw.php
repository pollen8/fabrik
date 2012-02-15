<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * Fabrik From Controller
 *
 * @static
 * @package		Joomla
 * @subpackage	Fabrik
 * @since 1.5
 */

class FabrikControllerForm extends JController
{

	var $isMambot = false;

 /**
   * Display the view
   */
  function display()
  {
    //menu links use fabriklayout parameters rather than layout
    $flayout = JRequest::getVar('fabriklayout');
    if ($flayout != '') {
      JRequest::setVar('layout', $flayout);
    }
    $document = JFactory::getDocument();

    $viewName	= JRequest::getVar('view', 'form', 'default', 'cmd');
    $modelName = $viewName;

    if ($viewName == 'details') {
      $viewName = 'form';
    }

    $viewType	= $document->getType();

    // Set the default view name from the Request
    $view = $this->getView($viewName, $viewType);

    // Push a model into the view
    $model	= &$this->getModel($modelName, 'FabrikFEModel');
    //if errors made when submitting from a J plugin they are stored in the session
    //lets get them back and insert them into the form model
  	if (empty($model->_arErrors)) {
			$context = 'com_fabrik.form.'.JRequest::getInt('formid');
			$model->_arErrors = $session->get($context.'.errors', array());
			$session->clear($context.'.errors');
		}
    if (!JError::isError($model) && is_object($model)) {
      $view->setModel($model, true);
    }
		$view->isMambot = $this->isMambot;
    // Display the view
    $view->assign('error', $this->getError());
    $user = JFactory::getUser();
    //only allow cached pages for users not logged in.
    return $view->display();
    if ($viewType != 'feed' && !$this->isMambot && $user->get('id') == 0) {
      $cache = JFactory::getCache('com_fabrik', 'view');
      return $cache->get($view, 'display');
    } else {
      return $view->display();
    }
  }

	/**
	 * process the form
	 */

	function process()
	{
		$document = JFactory::getDocument();
		$viewName	= JRequest::getVar('view', 'form', 'default', 'cmd');
		$viewType	= $document->getType();
		$view = $this->getView($viewName, $viewType);
		$model = $this->getModel('form', 'FabrikFEModel');
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}

		$model->setId(JRequest::getInt('formid', 0));

		$this->isMambot = JRequest::getVar('isMambot', 0);
		$model->getForm();
		$model->_rowId = JRequest::getVar('rowid', '');
		// Check for request forgeries
		if ($model->spoofCheck()) {
			JRequest::checkToken() or die('Invalid Token');
		}

		if (JRequest::getBool('fabrik_ignorevalidation', false) != true) { //put in when saving page of form
			if (!$model->validate()) {
				//if its in a module with ajax or in a package
				if (JRequest::getInt('_packageId') !== 0) {
					$data = array('modified' => $model->_modifiedValidationData);
					//validating entire group when navigating form pages
					$data['errors'] = $model->_arErrors;
					echo json_encode($data);
					return;
				}
				if ($this->isMambot) {
					//store errors in session
					$context = 'com_fabrik.form.'.$model->get('id').'.';
					$session->set($context.'errors', $model->_arErrors);
					// $$$ hugh - testing way of preserving form values after validation fails with form plugin
					// might as well use the 'savepage' mechanism, as it's already there!
					$session->set($context.'session.on', true);
					$this->savepage();
					$this->makeRedirect('', $model);
				} else {
					echo $view->display();
				}
				return;
			}
		}

		//reset errors as validate() now returns ok validations as empty arrays
		$model->_arErrors = array();
		$defaultAction = $model->process();
		//check if any plugin has created a new validation error
		if (!empty($model->_arErrors)) {
			$pluginManager = FabrikWorker::getPluginManager();
			$pluginManager->runPlugins('onError', $model);
			echo $view->display();
			return;
		}

		//one of the plugins returned false stopping the default redirect
		// action from taking place
		if (!$defaultAction) {
			return;
		}

		$msg = $model->getParams()->get('suppress_msgs', '0') == '0' ? $model->getParams()->get('submit-success-msg', JText::_('COM_FABRIK_RECORD_ADDED_UPDATED')) : '';

		if (JRequest::getInt('elid') !== 0) {
			//inline edit show the edited element
			echo $model->inLineEditResult();
			return;
		}

		if (JRequest::getInt('_packageId') !== 0) {
			echo json_encode(array('msg' => $msg));
			return;
		}
		JRequest::setVar('view', 'list');
		echo $this->display();
	}

	/**
	 * generic function to redirect
	 * @param string redirection message to show
	 */

	function makeRedirect($msg = null, &$model)
	{
		$app = JFactory::getApplication();
		if (is_null($msg)) {
			$msg = JText::_('COM_FABRIK_RECORD_ADDED_UPDATED');
		}
		if ($app->isAdmin()) {
			if (array_key_exists('apply', $model->_formData)) {
				$url = "index.php?option=com_fabrik&c=form&task=form&formid=".JRequest::getInt('formid')."&listid=".JRequest::getInt('listid')."&rowid=".JRequest::getInt('rowid');
			} else {
				$url = "index.php?option=com_fabrik&c=table&task=viewTable&cid[]=".$model->_table->id;
			}
			$this->setRedirect($url, $msg);
		} else {
			if (array_key_exists('apply', $model->_formData)) {
				$url = "index.php?option=com_fabrik&c=form&view=form&formid=".JRequest::getInt('formid')."&rowid=".JRequest::getInt('rowid')."&listid=".JRequest::getInt('listid');
			} else {
				if ($this->isMambot) {
					//return to the same page
					$url = JArrayHelper::getValue($_SERVER, 'REQUEST_URI', 'index.php');
				} else {
					//return to the page that called the form
					$url = JRequest::getVar('fabrik_referrer', "index.php", 'post');
				}
				// @TODO this global doesnt exist in j1.6
				$Itemid	= $app->getMenu('site')->getActive()->id;
				if ($url == '') {
					$url = "index.php?option=com_fabrik&Itemid=$Itemid";
				}
			}
			$config		= JFactory::getConfig();
			if ($config->get('sef')) {
				$url = JRoute::_($url);
			}
			$this->setRedirect($url, $msg);
		}
	}

}
?>