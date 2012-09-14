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
class FabrikControllerDetails extends JControllerForm
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_FORM';

	/**
	 * show the form in the admin
	 *
	 * @return  null
	 */

	public function view()
	{
		$document = JFactory::getDocument();
		$model = JModelLegacy::getInstance('Form', 'FabrikFEModel');
		JRequest::setVar('view', 'details');
		$viewType = $document->getType();
		$this->setPath('view', COM_FABRIK_FRONTEND . '/views');
		$viewLayout = JRequest::getCmd('layout', 'default');
		$view = $this->getView('form', $viewType, '');
		$view->setModel($model, true);

		// Set the layout
		$view->setLayout($viewLayout);

<<<<<<< HEAD
		// @TODO check for cached version
=======
		// @Todo check for cached version
>>>>>>> master
		JToolBarHelper::title(JText::_('COM_FABRIK_MANAGER_FORMS'), 'forms.png');
		$view->display();
		FabrikAdminHelper::addSubmenu(JRequest::getWord('view', 'lists'));
	}

}
