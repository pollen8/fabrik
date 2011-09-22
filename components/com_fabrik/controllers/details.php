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
 * Fabrik Details Controller
 *
 * @static
 * @package		Joomla
 * @subpackage	Fabrik
 * @since 1.5
 */

class FabrikControllerDetails extends JController
{

	var $isMambot = false;

	/* @var int  id used from content plugin when caching turned on to ensure correct element rendered)*/
	var $cacheId = 0;

	/**
	 * Display the view
	 */

	function display()
	{
		$session = JFactory::getSession();
		//menu links use fabriklayout parameters rather than layout
		$flayout = JRequest::getVar('fabriklayout');
		if ($flayout != '') {
			JRequest::setVar('layout', $flayout);
		}
		$document = JFactory::getDocument();

		$viewName	= JRequest::getVar('view', 'form', 'default', 'cmd');
		$modelName = $viewName;
		if ($viewName == 'emailform') {
			$modelName = 'form';
		}

		$viewName = 'form';
		$modelName = 'form';

		$viewType	= $document->getType();

		// Set the default view name from the Request
		$view = &$this->getView($viewName, $viewType);

		// Push a model into the view
		$model = !isset($this->_model) ? $this->getModel($modelName, 'FabrikFEModel') : $this->_model;

		//if errors made when submitting from a J plugin they are stored in the session
		//lets get them back and insert them into the form model
		if (!empty($model->_arErrors)) {
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

		if (in_array(JRequest::getCmd('format'), array('raw', 'csv', 'pdf'))) {
			$view->display();
		} else {
			$user = JFactory::getUser();
			$post = JRequest::get('post');
			$cacheid = serialize(array(JRequest::getURI(), $post, $user->get('id'), get_class($view), 'display', $this->cacheId));
			$cache = JFactory::getCache('com_fabrik', 'view');
			echo $cache->get($view, 'display', $cacheid);
		}
	}

	/**
	 * process the form
	 */

	function process()
	{
		@set_time_limit(300);
		$document = JFactory::getDocument();
		$viewName	= JRequest::getVar('view', 'form', 'default', 'cmd');
		$viewType	= $document->getType();
		$view 		= &$this->getView($viewName, $viewType);
		$model		= &$this->getModel('form', 'FabrikFEModel');
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}

		$model->setId(JRequest::getInt('formid', 0));

		$this->isMambot = JRequest::getVar('isMambot', 0);
		$model->getForm();
		$model->_rowId = JRequest::getVar('rowid', '');
		// Check for request forgeries
		$fbConfig = JComponentHelper::getParams('com_fabrik');
		if ($model->getParams()->get('spoof_check', $fbConfig->get('spoofcheck_on_formsubmission', true)) == true) {
			JRequest::checkToken() or die('Invalid Token');
		}

		if (JRequest::getVar('fabrik_ignorevalidation', 0) != 1) { //put in when saving page of form
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
					//JRequest::setVar('fabrik_referrer', JArrayHelper::getValue($_SERVER, 'HTTP_REFERER', '' ), 'post');
					// $$$ hugh - testing way of preserving form values after validation fails with form plugin
					// might as well use the 'savepage' mechanism, as it's already there!
					$session->set($context.'session.on', true);
					$this->savepage();
					$this->makeRedirect('', $model);
				} else {
					// $$$ rob - http://fabrikar.com/forums/showthread.php?t=17962
					// couldn't determine the exact set up that triggered this, but we need to reset the rowid to -1
					// if reshowing the form, otherwise it may not be editable, but rather show as a detailed view
					if (JRequest::getCmd('usekey') !== '') {
						JRequest::setVar('rowid', -1);
					}
					$view->display();
				}
				return;
			}
		}

		//reset errors as validate() now returns ok validations as empty arrays
		$model->_arErrors = array();

		$defaultAction = $model->process();
		//check if any plugin has created a new validation error
		if (!empty($model->_arErrors)) {
			FabrikWorker::getPluginManager()->runPlugins('onError', $model);
			$view->display();
			return;
		}
		// $$$ rob 31/01/2011
		// Now redirect always occurs even with redirect thx message, $this->setRedirect
		// will look up any redirect url specified in the session by a plugin and use that or
		// fall back to the url defined in $this->makeRedirect()

		//one of the plugins returned false stopping the default redirect
		// action from taking place
		/*if ($defaultAction !== true) {
			//see if a plugin set a custom re
			$url =
			return;
			}*/
		$listModel = $model->getListModel();
		$listModel->set('_table', null);

		$msg = $model->getParams()->get('submit-success-msg', JText::_('COM_FABRIK_RECORD_ADDED_UPDATED'));
		if (JRequest::getInt('_packageId') !== 0) {
			echo json_encode(array('msg' => $msg));
			return;
		}
		if (JRequest::getVar('format') == 'raw') {
			JRequest::setVar('view', 'list');
			$this->display();
			return;
		} else {

			$this->makeRedirect($msg, $model);
		}
	}

	/**
	 * (non-PHPdoc) adds redirect url and message to session
	 * @see JController::setRedirect()
	 */

	function setRedirect($url, $msg = null, $type = 'message')
	{
		$session = JFactory::getSession();
		$formdata = $session->get('com_fabrik.form.data');
		$context = 'com_fabrik.form.'.$formdata['fabrik'].'.redirect.';
		//if the redirect plug-in has set a url use that in preference to the default url
		$surl = $session->get($context.'url', array($url));
		if (!is_array($surl)) {
			$surl = array($surl);
		}
		if (empty($surl)) {
			$surl[] = $url;
		}
		$smsg = $session->get($context.'msg', array($msg));
		if (!is_array($smsg)) {
			$smsg = array($smsg);
		}
		if (empty($smsg)) {
			$smsg[] = $msg;
		}
		$url = array_shift($surl);
		$msg = array_shift($smsg);

		$app = JFactory::getApplication();
		$q = $app->getMessageQueue();
		$found = false;
		foreach ($q as $m) {
			//custom message already queued - unset default msg
			if ($m['type'] == 'message' && trim($m['message']) !== '') {
				$found= true;
				break;
			}
		}
		if ($found) {
			$msg = null;
		}
		$session->set($context.'url', $surl);
		$session->set($context.'msg', $smsg);
		$showmsg = array_shift($session->get($context.'showsystemmsg', array(true)));
		$msg = $showmsg ? $msg : null;
		parent::setRedirect($url, $msg, $type);
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
					//$$$ hugh - this doesn't seem to work if SEF is NOT enabled, just goes to index.php.
					//$url = JArrayHelper::getValue($_SERVER, 'REQUEST_URI', 'index.php');
					$url = JArrayHelper::getvalue($_SERVER, 'HTTP_REFERER', 'index.php');
				} else {
					//return to the page that called the form
					$url = urldecode(JRequest::getVar('fabrik_referrer', 'index.php', 'post'));
				}
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

	/**
	 * validate via ajax
	 *
	 */

	function ajax_validate()
	{
		$model	= &$this->getModel('form', 'FabrikFEModel');
		$model->setId(JRequest::getInt('formid', 0));
		$model->getForm();
		$model->_rowId = JRequest::getVar('rowid', '');
		$model->validate();
		$data = array('modified' => $model->_modifiedValidationData);
		//validating entire group when navigating form pages
		$data['errors'] = $model->_arErrors;
		echo json_encode($data);
	}

	/**
	 * save a form's page to the session table
	 */

	function savepage()
	{
		$model		=& $this->getModel('Formsession', 'FabrikFEModel');
		$formModel = $this->getModel('Form', 'FabrikFEModel');
		$formModel->setId(JRequest::getInt('formid'));
		$model->savePage($formModel);
	}

	/**
	 * clear down any temp db records or cookies
	 * containing partially filled in form data
	 */

	function removeSession()
	{
		$sessionModel = $this->getModel('formsession', 'FabrikFEModel');
		$sessionModel->setFormId(JRequest::getInt('formid', 0));
		$sessionModel->setRowId(JRequest::getInt('rowid', 0));
		$sessionModel->remove();
		$this->display();
	}

	/**
	 * called via ajax to page through form records
	 */
	function paginate()
	{
		$model = $this->getModel('Form', 'FabrikFEModel');
		$model->setId(JRequest::getInt('formid'));
		$model->paginateRowId(JRequest::getVar('dir'));
		$this->display();
	}

	/**
	 * delete a record from a form
	 */

	function delete()
	{
		// Check for request forgeries
		JRequest::checkToken() or die('Invalid Token');
		$app 				= JFactory::getApplication();
		$model	= &$this->getModel('list', 'FabrikFEModel');
		$ids = array(JRequest::getVar('rowid', 0));

		$listid = JRequest::getInt('listid');
		$limitstart = JRequest::getVar('limitstart'. $listid);
		$length = JRequest::getVar('limit' . $listid);

		$oldtotal = $model->getTotalRecords();
		$model->deleteRows($ids);

		$total = $oldtotal - count($ids);

		$ref = JRequest::getVar('fabrik_referrer', "index.php?option=com_fabrik&view=table&listid=$listid", 'post');
		if ($total >= $limitstart) {
			$newlimitstart = $limitstart - $length;
			if ($newlimitstart < 0) {
				$newlimitstart = 0;
			}
			$ref = str_replace("limitstart$listid=$limitstart", "limitstart$listid=$newlimitstart", $ref);
			$app = JFactory::getApplication();
			$context = 'com_fabrik.list.'.$listid.'.';
			$app->setUserState($context.'limitstart', $newlimitstart);
		}
		if (JRequest::getVar('format') == 'raw') {
			JRequest::setVar('view', 'list');

			$this->display();
		} else {
			//@TODO: test this
			$app->redirect($ref, count($ids) . " " . JText::_('COM_FABRIK_RECORDS_DELETED'));
		}
	}
}
?>