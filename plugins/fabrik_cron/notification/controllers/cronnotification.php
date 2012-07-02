<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
* @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

require_once(COM_FABRIK_FRONTEND . '/helpers/params.php');
require_once(COM_FABRIK_FRONTEND . '/helpers/string.php');

/**
 * Cron Notification Fabrik Plug-in Controller
 *
 * @static
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       1.5
 */
class FabrikControllerCroncronnotification extends JController
{
	/**
	 * Display the view
	 */
	function display()
	{
		$document = JFactory::getDocument();

		$viewName = 'cronnotification';

		$viewType	= $document->getType();

		// Set the default view name from the Request
		$view = &$this->getView( $viewName, $viewType);

		// Push a model into the view
		$model	= &$this->getModel( $viewName);

		if (!JError::isError($model )) {
			$view->setModel( $model, true);
		}
		// Display the view
		$view->assign('error', $this->getError());
		return $view->display();
	}

	function delete()
	{
		$model	= &$this->getModel( 'cronnotification');
		$model->delete();
		$this->setRedirect('index.php?option=com_fabrik&view=cron&controller=cron.cronnotification', JText::_('NOTIFICATIONS_REMOVED'));
	}

}
?>
