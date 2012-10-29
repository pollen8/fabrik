<?php
/**
* @package     Joomla
* @subpackage  Fabrik
* @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
* @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Admin List PDF controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	Fabrik
 * @since		3.0.7
 */
class FabrikControllerList extends JControllerForm
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_LIST';

	/**
	 * Show the lists data in the admin
	 *
	 * @return  void
	 */

	public function view()
	{
		$cid = JRequest::getVar('cid', array(0), 'method', 'array');
		if(is_array($cid))
		{
			$cid = $cid[0];
		}
		$cid = JRequest::getInt('listid', $cid);

		// Grab the model and set its id
		$model = JModel::getInstance('List', 'FabrikFEModel');
		$model->setState('list.id', $cid);
		$viewType	= JFactory::getDocument()->getType();

		// Use the front end list renderer
		$this->setPath('view', COM_FABRIK_FRONTEND . '/views');
		$viewLayout	= JRequest::getCmd('layout', 'default');
		$view = $this->getView($this->view_item, $viewType, '');
		$view->setModel($model, true);

		// Set the layout
		$view->setLayout($viewLayout);
		JToolBarHelper::title(JText::_('COM_FABRIK_MANAGER_LISTS'), 'lists.png');
		$view->display();
	}
}
