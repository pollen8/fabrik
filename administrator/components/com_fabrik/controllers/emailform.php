<?php
/**
 * Fabrik Email From Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       1.6
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * Fabrik Email From Controller
 *
 * @package  Fabrik
 * @since    3.0
 */

class FabrikAdminControllerEmailform extends JControllerLegacy
{

	/**
	 * Display the view
	 *
	 * @return  void
	 */

	public function display()
	{
		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		$input = $app->input;
		$viewName = $input::get('view', 'emailform');
		$modelName = 'form';
		$viewType = $document->getType();

		// Set the default view name from the Request
		$view = $this->getView($viewName, $viewType);

		// Push a model into the view (may have been set in content plugin already)
		if ($model = JModelLegacy::getInstance($modelName, 'FabrikFEModel'))
		{
			$view->setModel($model, true);
		}
		// Display the view
		$view->error = $this->getError();
		$view->display();
	}

}
