<?php
/**
* @package     Joomla
* @subpackage  Fabrik
* @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
* @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// No direct access
defined('_JEXEC') or die;

require_once 'fabcontrollerform.php';

/**
 * Admin List PDF controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	Fabrik
 * @since		3.0.7
 */
class FabrikAdminControllerList extends FabControllerForm
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
		$app = JFactory::getApplication();
		$input = $app->input;
		$cid = $input->get('cid', array(0), 'array');
		$cid = $cid[0];
		$cid = $input->getInt('listid', $cid);
echo $cid;exit;
		// Grab the model and set its id
		$model = JModelLegacy::getInstance('List', 'FabrikFEModel');
		$model->setState('list.id', $cid);
		$viewType = JFactory::getDocument()->getType();

		// Use the front end list renderer
		$this->setPath('view', COM_FABRIK_FRONTEND . '/views');
		$viewLayout	= $input->get('layout', 'default');
		echo $this->view_item . $viewType;exit;
		$view = $this->getView($this->view_item, $viewType, 'FabrikView');
		//$view = $this->getView($this->view_item, $viewType, '');
		$view->setModel($model, true);

		// Set the layout
		$view->setLayout($viewLayout);
		JToolBarHelper::title(JText::_('COM_FABRIK_MANAGER_LISTS'), 'lists.png');
		$view->display();
	}
}
