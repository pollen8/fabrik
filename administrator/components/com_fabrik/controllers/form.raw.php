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
 * @package		Joomla.Administrator
 * @subpackage	Fabrik
 * @since		3.0
 */

class FabrikAdminControllerForm extends JControllerForm
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_FORM';

	public $isMambot = false;

	/**
	 * Set up inline edit view
	 *
	 * @return  void
	 */

	public function inlineedit()
	{
		$document = JFactory::getDocument();
		$model = JModelLegacy::getInstance('Form', 'FabrikFEModel');
		$app = JFactory::getApplication();
		$input = $app->input;
		$viewType = $document->getType();
		$this->setPath('view', COM_FABRIK_FRONTEND . '/views');
		$viewLayout = $input->get('layout', 'default');
		$view = $this->getView('form', $viewType, '');
		$view->setModel($model, true);

		// Set the layout
		$view->setLayout($viewLayout);

		// @TODO check for cached version
		$view->inlineEdit();
	}

	/**
	 * Handle saving posted form data from the admin pages
	 *
	 * @return  void
	 */

	public function process()
	{
		$app = JFactory::getApplication();
		$input = $app->input;

		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		$input = $app->input;
		$viewName = $input->get('view', 'form');
		$model = JModelLegacy::getInstance('Form', 'FabrikFEModel');
		$viewType = $document->getType();

		// For now lets route this to the html view.
		$view = $this->getView($viewName, 'html');
		if (!JError::isError($model))
		{
			$view->setModel($model, true);
		}
		$model->setId($input->get('formid', 0));
		$this->isMambot = $input->get('_isMambot', 0);
		$model->getForm();
		$model->rowId = $input->get('rowid', '', 'string');

		// Check for request forgeries
		if ($model->spoofCheck())
		{
			JSession::checkToken() or die('Invalid Token');
		}

		$validated = $model->validate();
		if (!$validated)
		{
			// If its in a module with ajax or in a package or inline edit
			if ($input->get('fabrik_ajax'))
			{
				if ($input->getInt('elid') !== 0)
				{
					// Inline edit
					$eMsgs = array();
					$errs = $model->getErrors();

					// Only raise errors for fields that are present in the inline edit plugin
					$toValidate = array_keys($input->get('toValidate', array(), 'array'));
					foreach ($errs as $errorKey => $e)
					{
						if (in_array($errorKey, $toValidate) && count($e[0]) > 0)
						{
							array_walk_recursive($e, array('FabrikString', 'forHtml'));
							$eMsgs[] = count($e[0]) === 1 ? '<li>' . $e[0][0] . '</li>' : '<ul><li>' . implode('</li><li>', $e[0]) . '</ul>';
						}
					}
					if (!empty($eMsgs))
					{
						$eMsgs = '<ul>' . implode('</li><li>', $eMsgs) . '</ul>';
						header('HTTP/1.1 500 ' . JText::_('COM_FABRIK_FAILED_VALIDATION') . $eMsgs);
						jexit();
					}
					else
					{
						$validated = true;
					}
				}
				else
				{
					echo $model->getJsonErrors();
				}
				if (!$validated)
				{
					return;
				}
			}
			if (!$validated)
			{
				if ($this->isMambot)
				{
					$input->post->set('fabrik_referrer', JArrayHelper::getValue($_SERVER, 'HTTP_REFERER', ''));

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
		}

		// Reset errors as validate() now returns ok validations as empty arrays
		$model->errors = array();

		$model->process();

		// Check if any plugin has created a new validation error
		if ($model->hasErrors())
		{
			FabrikWorker::getPluginManager()->runPlugins('onError', $model);
			$view->display();
			return;
		}

		$listModel = $model->getListModel();
		$tid = $listModel->getTable()->id;

		$res = $model->getRedirectURL(true, $this->isMambot);
		$this->baseRedirect = $res['baseRedirect'];
		$url = $res['url'];

		$msg = $model->getRedirectMessage($model);

		if ($input->getInt('elid') !== 0)
		{
			// Inline edit show the edited element
			echo $model->inLineEditResult();
			return;
		}
		if ($input->getInt('packageId') !== 0)
		{
			$rowid = $input->getInt('rowid');
			echo json_encode(array('msg' => $msg, 'rowid' => $rowid));
			return;
		}
		if ($input->get('format') == 'raw')
		{
			$url = 'index.php?option=com_fabrik&view=list&format=raw&listid=' . $tid;
			$this->setRedirect($url, $msg);
		}
		else
		{
			$this->setRedirect($url, $msg);
		}
	}

	/**
	 * Generic function to redirect
	 *
	 * @param   object  &$model  form model
	 * @param   string  $msg     optional redirect message
	 *
	 * @deprecated - since 3.0.6 not used
	 * @return  null
	 */

	protected function makeRedirect($model, $msg = null)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		if (is_null($msg))
		{
			$msg = JText::_('COM_FABRIK_RECORD_ADDED_UPDATED');
		}
		if (array_key_exists('apply', $model->formData))
		{
			$page = 'index.php?option=com_fabrik&task=form.view&formid=' . $input->getInt('formid') . '&listid=' . $input->getInt('listid')
				. '&rowid=' . $input->getInt('rowid');
		}
		else
		{
			$page = 'index.php?option=com_fabrik&task=list.view&cid[]=' . $model->getlistModel()->getTable()->id;
		}
		$this->setRedirect($page, $msg);
	}
}
