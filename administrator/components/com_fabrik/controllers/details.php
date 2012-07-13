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
 * @subpackage	Fabrik
 * @since		1.6
 */
class FabrikControllerDetails extends JControllerForm
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_FORM';

	/**
	 * show the form in the admin
	 */

	function view()
	{
		$document = JFactory::getDocument();
		$model = JModel::getInstance('Form', 'FabrikFEModel');
		JRequest::setVar('view', 'details');
		$viewType = $document->getType();
		$this->setPath('view', COM_FABRIK_FRONTEND . '/views');
		$viewLayout	= JRequest::getCmd('layout', 'default');
		$view = $this->getView('form', $viewType, '');
		$view->setModel($model, true);

		// Set the layout
		$view->setLayout($viewLayout);

		//todo check for cached version
		JToolBarHelper::title(JText::_('COM_FABRIK_MANAGER_FORMS'), 'forms.png');
		$view->display();
		FabrikHelper::addSubmenu(JRequest::getWord('view', 'lists'));
	}

}