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

	public $isMambot = false;

	/* @var int  id used from content plugin when caching turned on to ensure correct element rendered)*/
	protected $cacheId = 0;
	
	/**
	 * show the form in the admin
	 */

	function view()
	{
		$document = JFactory::getDocument();
		$model = JModel::getInstance('Form', 'FabrikFEModel');
		$viewType	= $document->getType();
		$this->setPath('view', COM_FABRIK_FRONTEND . '/views');
		$viewLayout	= JRequest::getCmd('layout', 'default');
		$view = $this->getView('form', $viewType, '');
		$view->setModel($model, true);
		$view->isMambot = $this->isMambot;
		// Set the layout
		$view->setLayout($viewLayout);

		//todo check for cached version
		JToolBarHelper::title(JText::_('COM_FABRIK_MANAGER_FORMS'), 'forms.png');
		
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
			ob_start();
			$cache->get($view, 'display', $cacheid);
			$contents = ob_get_contents();
			ob_end_clean();
			$token = JUtility::getToken();
			$search = '#<input type="hidden" name="[0-9a-f]{32}" value="1" />#';
			$replacement = '<input type="hidden" name="' . $token . '" value="1" />';
			echo preg_replace($search, $replacement, $contents);
		}
		
		FabrikHelper::addSubmenu(JRequest::getWord('view', 'lists'));
	}

	/**
	 * handle saving posted form data from the admin pages
	 */

	function process()
	{
		$model = JModel::getInstance('Form', 'FabrikFEModel');
		$document = JFactory::getDocument();
		$viewName = JRequest::getVar('view', 'form', 'default', 'cmd');
		$viewType = $document->getType();
		$view = $this->getView($viewName, $viewType);
		if (!JError::isError($model))
		{
			$view->setModel($model, true);
		}
		$model->setId(JRequest::getInt('formid', 0));

		$this->isMambot = JRequest::getVar('_isMambot', 0);
		$model->getForm();
		$model->_rowId = JRequest::getVar('rowid', '');

		// Check for request forgeries
		if ($model->spoofCheck())
		{
			JRequest::checkToken() or die('Invalid Token');
		}
		if (!$model->validate())
		{
			//if its in a module with ajax or in a package
			if (JRequest::getInt('_packageId') !== 0)
			{
				echo $model->getJsonErrors();
				return;
			}
			$this->savepage();

			if ($this->isMambot)
			{
				JRequest::setVar('fabrik_referrer', JArrayHelper::getValue($_SERVER, 'HTTP_REFERER', ''), 'post');
			}
			else
			{
				$this->setRedirect('index.php?option=com_fabrik&task=form.view&formid=' . $model->getId() . '&rowid=' . $model->_rowId, '');
			}
			return;
		}

		//reset errors as validate() now returns ok validations as empty arrays
		$model->clearErrors();

		$defaultAction = $model->process();

		//check if any plugin has created a new validation error
		if (!empty($model->_arErrors))
		{
			FabrikWorker::getPluginManager()->runPlugins('onError', $model);
			$view->display();
			return;
		}

		//one of the plugins returned false stopping the default redirect
		// action from taking place
		if (!$defaultAction)
		{
			return;
		}
		$listModel = $model->getListModel();
		$tid = $listModel->getTable()->id;

		$msg = $model->getParams()->get('suppress_msgs', '0') == '0' ? $model->getParams()->get('submit-success-msg', JText::_('COM_FABRIK_RECORD_ADDED_UPDATED')) : '';

		if (JRequest::getInt('_packageId') !== 0)
		{
			$rowid = JRequest::getInt('rowid');
			echo json_encode(array('msg' => $msg, 'rowid' => $rowid));
			return;
		}
		if (JRequest::getVar('format') == 'raw')
		{
			$url = COM_FABRIK_LIVESITE . '/index.php?option=com_fabrik&view=list&format=raw&listid=' . $tid;
			$this->setRedirect($url, $msg);
		}
		else
		{
			$this->makeRedirect($msg, $model);
		}
	}

	/**
	* save a form's page to the session table
	*/

	protected function savepage()
	{
		$model = $this->getModel('Formsession', 'FabrikFEModel');
		$formModel = $this->getModel('Form', 'FabrikFEModel');
		$formModel->setId(JRequest::getInt('formid'));
		$model->savePage($formModel);
	}

	/**
	 * generic function to redirect
	 */

	protected function makeRedirect($msg = null, &$model)
	{
		if (is_null($msg))
		{
			$msg = JText::_('COM_FABRIK_RECORD_ADDED_UPDATED');
		}
		if (array_key_exists('apply', $model->_formData))
		{
			$page = 'index.php?option=com_fabrik&task=form.view&formid=' . JRequest::getInt('formid') . '&listid=' . JRequest::getInt('listid') . '&rowid=' . JRequest::getInt('rowid');
		}
		else
		{
			$page = 'index.php?option=com_fabrik&task=list.view&listid=' . $model->getlistModel()->getTable()->id;
		}
		// $$$ rob was redirecting back to admin list view and not list data view (list.view)
		// $$$ rob put back in as list views are now called using /administrator/index.php?option=com_fabrik&task=list.view&listid=1
		// $$$ hugh - commented this out, as it was overriding the 'apply' URL form above, and (if I'm reading Rob's comments correctly)
		// fixing the $page setting above, to use task=list.view&listid=X should work?
		//$page = JRequest::getVar('fabrik_referrer', $page);
		$this->setRedirect($page, $msg);
	}
}