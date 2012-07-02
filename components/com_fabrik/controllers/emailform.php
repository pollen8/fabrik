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
 * Fabrik Email From Controller
 *
 * @static
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       1.5
 */

class FabrikControllerEmailform extends JController
{

	/**
	 * Display the view
	 * 
	 * @return	null
	 */

	public function display()
	{
		$document = JFactory::getDocument();

		$viewName = JRequest::getVar('view', 'emailform', 'default', 'cmd');
		$modelName = 'form';

		$viewType = $document->getType();

		// Set the default view name from the Request
		$view = $this->getView($viewName, $viewType);

		$model = $this->getModel($modelName, 'FabrikFEModel');

		// Test for failed validation then page refresh
		$model->getErrors();
		if (!JError::isError($model) && is_object($model))
		{
			$view->setModel($model, true);
		}
		// Display the view
		$view->assign('error', $this->getError());
		$view->display();
	}

}
