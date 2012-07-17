<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * Fabrik Details Controller
 *
 * @static
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       1.5
 */

class FabrikControllerDetails extends JController
{

	/** @var   bool $isMambot  is the view rendered from the J content plugin */
	public $isMambot = false;

	/* @var int  id used from content plugin when caching turned on to ensure correct element rendered)*/
	public $cacheId = 0;

	/**
	 * Display the view
	 *
	 * @return  null
	 */

	public function display()
	{
		$session = JFactory::getSession();
		$document = JFactory::getDocument();
		$viewName = JRequest::getVar('view', 'form', 'default', 'cmd');
		$modelName = $viewName;
		if ($viewName == 'emailform')
		{
			$modelName = 'form';
		}

		$viewName = 'form';
		$modelName = 'form';

		$viewType = $document->getType();
		if ($viewType == 'pdf')
		{
			// In PDF view only shown the main component content.
			JRequest::setVar('tmpl', 'component');
		}

		// Set the default view name from the Request
		$view = &$this->getView($viewName, $viewType);

		// Push a model into the view
		$model = !isset($this->_model) ? $this->getModel($modelName, 'FabrikFEModel') : $this->_model;

		// If errors made when submitting from a J plugin they are stored in the session lets get them back and insert them into the form model
		if (!empty($model->errors))
		{
			$context = 'com_fabrik.form.' . JRequest::getInt('formid');
			$model->errors = $session->get($context . '.errors', array());
			$session->clear($context . '.errors');
		}
		if (!JError::isError($model) && is_object($model))
		{
			$view->setModel($model, true);
		}
		$view->isMambot = $this->isMambot;

		// Display the view
		$view->assign('error', $this->getError());

		if (in_array(JRequest::getCmd('format'), array('raw', 'csv', 'pdf')))
		{
			$view->display();
		}
		else
		{
			$user = JFactory::getUser();
			$post = JRequest::get('post');
			$cacheid = serialize(array(JRequest::getURI(), $post, $user->get('id'), get_class($view), 'display', $this->cacheId));
			$cache = JFactory::getCache('com_fabrik', 'view');
			echo $cache->get($view, 'display', $cacheid);
		}
	}

	/**
	 * process the form
	 *
	 * @return  null
	 */

	public function process()
	{
		@set_time_limit(300);
		$document = JFactory::getDocument();
		$viewName = JRequest::getVar('view', 'form', 'default', 'cmd');
		$viewType = $document->getType();
		$view = $this->getView($viewName, $viewType);
		$model = $this->getModel('form', 'FabrikFEModel');
		if (!JError::isError($model))
		{
			$view->setModel($model, true);
		}

		$model->setId(JRequest::getInt('formid', 0));

		$this->isMambot = JRequest::getVar('isMambot', 0);
		$model->getForm();
		$model->rowId = JRequest::getVar('rowid', '');

		// Check for request forgeries
		$fbConfig = JComponentHelper::getParams('com_fabrik');
		if ($model->getParams()->get('spoof_check', $fbConfig->get('spoofcheck_on_formsubmission', true)) == true)
		{
			JRequest::checkToken() or die('Invalid Token');
		}

		if (JRequest::getBool('fabrik_ignorevalidation', false) != true)
		{
			// Put in when saving page of form
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
					// Store errors in session
					$context = 'com_fabrik.form.' . $model->get('id') . '.';
					$session->set($context . 'errors', $model->errors);
					/**
					 * $$$ hugh - testing way of preserving form values after validation fails with form plugin
					 * might as well use the 'savepage' mechanism, as it's already there!
					 */
					$session->set($context . 'session.on', true);
					$this->savepage();
					$this->makeRedirect($model, '');
				}
				else
				{
					/**
					 * $$$ rob - http://fabrikar.com/forums/showthread.php?t=17962
					 * couldn't determine the exact set up that triggered this, but we need to reset the rowid to -1
					 * if reshowing the form, otherwise it may not be editable, but rather show as a detailed view
					 */
					if (JRequest::getCmd('usekey') !== '')
					{
						JRequest::setVar('rowid', -1);
					}
					$view->display();
				}
				return;
			}
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
		/**
		 * $$$ rob 31/01/2011
		 * Now redirect always occurs even with redirect thx message, $this->setRedirect
		 * will look up any redirect url specified in the session by a plugin and use that or
		 * fall back to the url defined in $this->makeRedirect()
		 */

		$listModel = $model->getListModel();
		$listModel->set('_table', null);

		$msg = $model->getParams()->get('suppress_msgs', '0') == '0'
			? $model->getParams()->get('submit-success-msg', JText::_('COM_FABRIK_RECORD_ADDED_UPDATED')) : '';

		if (JRequest::getInt('_packageId') !== 0)
		{
			echo json_encode(array('msg' => $msg));
			return;
		}
		if (JRequest::getVar('format') == 'raw')
		{
			JRequest::setVar('view', 'list');
			$this->display();
			return;
		}
		else
		{
			$this->makeRedirect($model, $msg);
		}
	}

	/**
	 * Set the redirect url
	 *
	 * @param   string  $url   default url
	 * @param   string  $msg   optional message to apply on redirect
	 * @param   string  $type  optional message type
	 *
	 * @return  null
	 */

	public function setRedirect($url, $msg = null, $type = 'message')
	{
		$session = JFactory::getSession();
		$formdata = $session->get('com_fabrik.form.data');
		$context = 'com_fabrik.form.' . $formdata['fabrik'] . '.redirect.';

		// If the redirect plug-in has set a url use that in preference to the default url
		$surl = $session->get($context . 'url', array($url));
		if (!is_array($surl))
		{
			$surl = array($surl);
		}
		if (empty($surl))
		{
			$surl[] = $url;
		}
		$smsg = $session->get($context . 'msg', array($msg));
		if (!is_array($smsg))
		{
			$smsg = array($smsg);
		}
		if (empty($smsg))
		{
			$smsg[] = $msg;
		}
		$url = array_shift($surl);
		$msg = array_shift($smsg);

		$app = JFactory::getApplication();
		$q = $app->getMessageQueue();
		$found = false;
		foreach ($q as $m)
		{
			// Custom message already queued - unset default msg
			if ($m['type'] == 'message' && trim($m['message']) !== '')
			{
				$found = true;
				break;
			}
		}
		if ($found)
		{
			$msg = null;
		}
		$session->set($context . 'url', $surl);
		$session->set($context . 'msg', $smsg);
		$showmsg = array_shift($session->get($context . 'showsystemmsg', array(true)));
		$msg = $showmsg ? $msg : null;
		parent::setRedirect($url, $msg, $type);
	}

	/**
	 * generic function to redirect
	 *
	 * @param   object  &$model  form model
	 * @param   string  $msg     redirection message to show
	 *
	 * @return  null
	 */

	protected function makeRedirect(&$model, $msg = null)
	{
		$app = JFactory::getApplication();
		if (is_null($msg))
		{
			$msg = JText::_('COM_FABRIK_RECORD_ADDED_UPDATED');
		}
		if ($app->isAdmin())
		{
			if (array_key_exists('apply', $model->formData))
			{
				$url = 'index.php?option=com_fabrik&c=form&task=form&formid=' . JRequest::getInt('formid') . '&listid=' . JRequest::getInt('listid')
					. '&rowid=' . JRequest::getInt('rowid');
			}
			else
			{
				$url = "index.php?option=com_fabrik&c=table&task=viewTable&cid[]=" . $model->getTable()->id;
			}
			$this->setRedirect($url, $msg);
		}
		else
		{
			if (array_key_exists('apply', $model->formData))
			{
				$url = "index.php?option=com_fabrik&c=form&view=form&formid=" . JRequest::getInt('formid') . "&rowid=" . JRequest::getInt('rowid')
					. "&listid=" . JRequest::getInt('listid');
			}
			else
			{
				if ($this->isMambot)
				{
					// Return to the same page
					$url = JArrayHelper::getvalue($_SERVER, 'HTTP_REFERER', 'index.php');
				}
				else
				{
					// Return to the page that called the form
					$url = urldecode(JRequest::getVar('fabrik_referrer', 'index.php', 'post'));
				}
				$Itemid = $app->getMenu('site')->getActive()->id;
				if ($url == '')
				{
					$url = "index.php?option=com_fabrik&Itemid=$Itemid";
				}
			}
			$config = JFactory::getConfig();
			if ($config->get('sef'))
			{
				$url = JRoute::_($url);
			}
			$this->setRedirect($url, $msg);
		}
	}

	/**
	 * validate via ajax
	 *
	 * @return  null
	 */

	public function ajax_validate()
	{
		$model = $this->getModel('form', 'FabrikFEModel');
		$model->setId(JRequest::getInt('formid', 0));
		$model->getForm();
		$model->rowId = JRequest::getVar('rowid', '');
		$model->validate();
		$data = array('modified' => $model->modifiedValidationData);

		// Validating entire group when navigating form pages
		$data['errors'] = $model->errors;
		echo json_encode($data);
	}

	/**
	 * save a form's page to the session table
	 *
	 * @return  null
	 */

	public function savepage()
	{
		$model = $this->getModel('Formsession', 'FabrikFEModel');
		$formModel = $this->getModel('Form', 'FabrikFEModel');
		$formModel->setId(JRequest::getInt('formid'));
		$model->savePage($formModel);
	}

	/**
	 * clear down any temp db records or cookies
	 * containing partially filled in form data
	 *
	 * @return  null
	 */

	public function removeSession()
	{
		$sessionModel = $this->getModel('formsession', 'FabrikFEModel');
		$sessionModel->setFormId(JRequest::getInt('formid', 0));
		$sessionModel->setRowId(JRequest::getInt('rowid', 0));
		$sessionModel->remove();
		$this->display();
	}

	/**
	 * called via ajax to page through form records
	 *
	 * @return  null
	 */

	public function paginate()
	{
		$model = $this->getModel('Form', 'FabrikFEModel');
		$model->setId(JRequest::getInt('formid'));
		$model->paginateRowId(JRequest::getVar('dir'));
		$this->display();
	}

	/**
	 * delete a record from a form
	 *
	 * @return  null
	 */

	public function delete()
	{
		// Check for request forgeries
		JRequest::checkToken() or die('Invalid Token');
		$app = JFactory::getApplication();
		$model = $this->getModel('list', 'FabrikFEModel');
		$ids = array(JRequest::getVar('rowid', 0));

		$listid = JRequest::getInt('listid');
		$limitstart = JRequest::getVar('limitstart' . $listid);
		$length = JRequest::getVar('limit' . $listid);

		$oldtotal = $model->getTotalRecords();
		$model->setId($listid);
		$model->deleteRows($ids);

		$total = $oldtotal - count($ids);

		$ref = JRequest::getVar('fabrik_referrer', "index.php?option=com_fabrik&view=table&listid=$listid", 'post');
		if ($total >= $limitstart)
		{
			$newlimitstart = $limitstart - $length;
			if ($newlimitstart < 0)
			{
				$newlimitstart = 0;
			}
			$ref = str_replace("limitstart$listid=$limitstart", "limitstart$listid=$newlimitstart", $ref);
			$app = JFactory::getApplication();
			$context = 'com_fabrik.list.' . $model->getRenderContext() . '.';
			$app->setUserState($context . 'limitstart', $newlimitstart);
		}
		if (JRequest::getVar('format') == 'raw')
		{
			JRequest::setVar('view', 'list');

			$this->display();
		}
		else
		{
			// @TODO: test this
			$app->redirect($ref, count($ids) . " " . JText::_('COM_FABRIK_RECORDS_DELETED'));
		}
	}
}
