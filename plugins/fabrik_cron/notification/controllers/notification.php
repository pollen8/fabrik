<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.notification
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

require_once COM_FABRIK_FRONTEND . '/helpers/params.php';
require_once COM_FABRIK_FRONTEND . '/helpers/string.php';

/**
 * Cron Notification Fabrik Plug-in Controller
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.cron.notification
 * @since       3.0
 */

class FabrikControllerCroncronnotification extends JController
{
	/**
	 * Display the view
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController  A JController object to support chaining.
	 */

	public function display($cachable = false, $urlparams = false)
	{
		$document = JFactory::getDocument();

		$viewName = 'cronnotification';

		$viewType = $document->getType();

		// Set the default view name from the Request
		$view = $this->getView($viewName, $viewType);

		// Push a model into the view
		$model = $this->getModel($viewName);

		if (!JError::isError($model))
		{
			$view->setModel($model, true);
		}
		// Display the view
		$view->assign('error', $this->getError());
		$view->display();
		return $this;
	}

	/**
	 * Delete a notification
	 *
	 * @return  void
	 */

	public function delete()
	{
		$model = $this->getModel('cronnotification');
		$model->delete();
		$this->setRedirect('index.php?option=com_fabrikn&task=cron.cronnotification', JText::_('NOTIFICATIONS_REMOVED'));
	}

}
