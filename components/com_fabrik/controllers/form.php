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

	/* @var int  id used from content plugin when caching turned on to ensure correct element rendered)*/
	var $cacheId = 0;

	/**
	 * @since 3.0b
	 * inline edit control
	 */

	public function inlineedit()
	{
		$document = JFactory::getDocument();
		$model = JModel::getInstance('Form', 'FabrikFEModel');
		$viewType	= $document->getType();
		//$this->setPath('view', COM_FABRIK_FRONTEND.DS.'views');
		$viewLayout	= JRequest::getCmd('layout', 'default');
		$view = $this->getView('form', $viewType, '');
		$view->setModel($model, true);
		// Set the layout
		$view->setLayout($viewLayout);
		//todo check for cached version
		$view->inlineEdit();
	}

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

		$viewType	= $document->getType();

		// Set the default view name from the Request
		$view = $this->getView($viewName, $viewType);

		// Push a model into the view (may have been set in content plugin already
		$model = !isset($this->_model) ? $this->getModel($modelName, 'FabrikFEModel') : $this->_model;

		//test for failed validation then page refresh
		$model->getErrors();
		if (!JError::isError($model) && is_object($model)) {
			$view->setModel($model, true);
		}
		$view->isMambot = $this->isMambot;
		// Display the view
		$view->assign('error', $this->getError());

		// Workaround for token caching

		if (in_array(JRequest::getCmd('format'), array('raw', 'csv', 'pdf'))) {
			$view->display();
		} else {
			$user = JFactory::getUser();
			$post = JRequest::get('post');
			$cacheid = serialize(array(JRequest::getURI(), $post, $user->get('id'), get_class($view), 'display', $this->cacheId));
			$cache = JFactory::getCache('com_fabrik', 'view');
			ob_start();
			$cache->get($view, 'display', $cacheid);
			$contents = ob_get_contents();
			ob_end_clean();
			$token = JUtility::getToken();
			$search = '#<input type="hidden" name="[0-9a-f]{32}" value="1" />#';
			$replacement = '<input type="hidden" name="'.$token.'" value="1" />';
			echo preg_replace($search, $replacement, $contents);
		}
	}

	/**
	 * process the form
	 */

	function process()
	{
		if (JRequest::getCmd('format', '') == 'raw') {
			error_reporting( error_reporting() ^ (E_WARNING | E_NOTICE) );
		}
		$model = $this->getModel('form', 'FabrikFEModel');
		$viewName = JRequest::getVar('view', 'form', 'default', 'cmd');
		$view = $this->getView($viewName, JFactory::getDocument()->getType());

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

		if (!$model->validate()) {
			//if its in a module with ajax or in a package
			if (JRequest::getCmd('fabrik_ajax')) {

				echo $model->getJsonErrors();
				return;
			}
			$this->savepage();

			if ($this->isMambot) {
				$this->setRedirect($this->getRedirectURL($model, false));
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

		//reset errors as validate() now returns ok validations as empty arrays
		$model->clearErrors();

		$model->process();

		if (JRequest::getInt('elid') !== 0) {
			//inline edit show the edited element - ignores validations for now
			echo $model->inLineEditResult();
			return;
		}

		//check if any plugin has created a new validation error
		if (!empty($model->_arErrors)) {
			FabrikWorker::getPluginManager()->runPlugins('onError', $model);
			$view->display();
			return;
		}

		$listModel = $model->getListModel();
		$listModel->set('_table', null);

		$url = $this->getRedirectURL($model);
		$msg = $this->getRedirectMessage($model);

		// @todo -should get handed off to the json view to do this
		if (JRequest::getInt('fabrik_ajax') == 1) {
			// $$$ hugh - adding some options for what to do with redirect when in content plugin
			// Should probably do this elsewhere, but for now ...
			$redirect_opts = array(
				'msg' => $msg,
				'url' => $url,
				'baseRedirect'=>$this->baseRedirect,
				'rowid' => JRequest::getVar('rowid')
			);
			if (!$this->baseRedirect && $this->isMambot) {
				$session = JFactory::getSession();
				$context = 'com_fabrik.form.'.$model->get('id').'.redirect.';
				$redirect_opts['redirect_how'] = $session->get($context.'redirect_content_how', 'popup');
				$redirect_opts['width'] = (int)$session->get($context.'redirect_content_popup_width', '300');
				$redirect_opts['height'] = (int)$session->get($context.'redirect_content_popup_height', '300');
				$redirect_opts['x_offset'] = (int)$session->get($context.'redirect_content_popup_x_offset', '0');
				$redirect_opts['y_offset'] = (int)$session->get($context.'redirect_content_popup_y_offset', '0');
				$redirect_opts['title'] = $session->get($context.'redirect_content_popup_title', '');
				$redirect_opts['reset_form'] = $session->get($context.'redirect_content_reset_form', '1') == '1';
			}
			else if ($this->isMambot) {
				// $$$ hugh - special case to allow custom code to specify that
				// the form should not be cleared after a failed AJAX submit
				$session = JFactory::getSession();
				$context = 'com_fabrik.form.'.$model->get('id').'.redirect.';
				$redirect_opts['reset_form'] = $session->get($context.'redirect_content_reset_form', '1') == '1';
			}
			//let form.js handle the redirect logic (will also send out a
			echo json_encode($redirect_opts);
			return;
		}

		if (JRequest::getVar('format') == 'raw') {
			JRequest::setVar('view', 'list');
			$this->display();
			return;
		} else {
			$this->setRedirect($url, $msg);
		}
	}

	/**
	 * @since 3.0
	 * Enter description here ...
	 * @param object $model
	 */

	protected function getRedirectMessage($model)
	{
		$session = JFactory::getSession();
		$registry	= $session->get('registry');
		$formdata = $session->get('com_fabrik.form.data');
		//$$$ rob 30/03/2011 if using as a search form don't show record added message
		if ($registry && $registry->getValue('com_fabrik.searchform.fromForm') != $model->get('id')) {
			$msg = $model->getParams()->get('suppress_msgs', '0') == '0' ? $model->getParams()->get('submit-success-msg', JText::_('COM_FABRIK_RECORD_ADDED_UPDATED')) : '';
		} else {
			$msg = '';
		}
		$context = 'com_fabrik.form.'.$formdata['formid'].'.redirect.';
		$smsg = $session->get($context.'msg', array($msg));
		if (!is_array($smsg)) {
			$smsg = array($smsg);
		}
		if (empty($smsg)) {
			$smsg[] = $msg;
		}
		// $$$ rob Was using array_shift to set $msg, not to really remove it from $smsg
		// without the array_shift the custom message is never attached to the redirect page.
		// use case 'redirct plugin with jump page pointing to a J page and thanks message selected.
		$custommsg = JArrayHelper::getValue($smsg, array_shift(array_keys($smsg)));
		if ($custommsg != '') {
			$msg = $custommsg;
		}
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
		$session->set($context.'msg', $smsg);
		$showmsg = array_shift($session->get($context.'showsystemmsg', array(true)));
		$msg = $showmsg == 1 ? $msg : null;
		return $msg;
	}

	/**
	 * @since 3.0
	 * Enter description here ...
	 * @param object $model
	 */

	protected function getRedirectURL($model, $incSession = true)
	{
		$app = JFactory::getApplication();
		if ($app->isAdmin()) {
			if (array_key_exists('apply', $model->_formData)) {
				$url = "index.php?option=com_fabrik&c=form&task=form&formid=".JRequest::getInt('formid')."&listid=".JRequest::getInt('listid')."&rowid=".JRequest::getInt('rowid');
			} else {
				$url = "index.php?option=com_fabrik&c=table&task=viewTable&cid[]=".$model->_table->id;
			}
		} else {
			if (array_key_exists('apply', $model->_formData)) {
				$url = "index.php?option=com_fabrik&view=form&formid=".JRequest::getInt('formid')."&rowid=".JRequest::getInt('rowid')."&listid=".JRequest::getInt('listid');
			} else {
				if ($this->isMambot) {
					//return to the same page
					$url = JArrayHelper::getvalue($_SERVER, 'HTTP_REFERER', 'index.php');
				} else {
					//return to the page that called the form
					$url = urldecode(JRequest::getVar('fabrik_referrer', 'index.php', 'post'));
				}
				$Itemid	= (int)@$app->getMenu('site')->getActive()->id;
				if ($url == '') {
					if ($Itemid !== 0) {
						$url = "index.php?option=com_fabrik&Itemid=$Itemid";
					} else {
						//no menu link so redirect back to list view
						$url = "index.php?option=com_fabrik&view=list&listid=".JRequest::getInt('listid');
					}
				}
			}
			$config	= JFactory::getConfig();
			if ($config->get('sef')) {
				$url = JRoute::_($url);
			}
		}
		//3.0 need to distinguish between the default redirect and redirect plugin
		$this->baseRedirect = true;
		if (!$incSession) {
			return $url;
		}
		$session = JFactory::getSession();
		$formdata = $session->get('com_fabrik.form.data');
		$context = 'com_fabrik.form.'.$formdata['formid'].'.redirect.';
		//if the redirect plug-in has set a url use that in preference to the default url
		//$surl = $session->get($context.'url', array($url));
		$surl = $session->get($context.'url', array());
		if (!empty($surl)) {
			$this->baseRedirect = false;
		}
		if (!is_array($surl)) {
			$surl = array($surl);
		}
		if (empty($surl)) {
			$surl[] = $url;
		}
		// $$$ hugh - hmmm, array_shift re-orders array keys, which will screw up plugin ordering?
		$url = array_shift($surl);
		$session->set($context.'url', $surl);
		return $url;
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
		$model = $this->getModel('Formsession', 'FabrikFEModel');
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
		$app = JFactory::getApplication();
		$model = $this->getModel('list', 'FabrikFEModel');
		$ids = array(JRequest::getVar('rowid', 0));

		$listid = JRequest::getInt('listid');
		$limitstart = JRequest::getVar('limitstart'. $listid);
		$length = JRequest::getVar('limit' . $listid);

		$oldtotal = $model->getTotalRecords();
		$model->setId($listid);
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
			$context = 'com_fabrik.list.'.$model->getRenderContext().'.';
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