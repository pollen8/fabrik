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
 * List controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	Fabrik
 * @since		1.6
 */
class FabrikControllerList extends JControllerForm
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_LIST';

	/**
	 * ajax load drop down of all columns in a given table
	 */

	public function ajax_loadTableDropDown()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$conn = $input->getInt('conn', 1);
		$oCnn = JModel::getInstance('Connection', 'FabrikFEModel');
		$oCnn->setId($conn);
		$oCnn->getConnection();
		$db = $oCnn->getDb();
		$table = $input->get('table', '');
		$fieldNames = array();
		if ($table != '')
		{
			$table = FabrikString::safeColName($table);
			$name = $input->get('name', 'jform[params][table_key][]');
			$sql = 'DESCRIBE ' . $table;
			$db->setQuery($sql);
			$aFields = $db->loadObjectList();
			if (is_array($aFields))
			{
				foreach ($aFields as $oField)
				{
					$fieldNames[] = JHTML::_('select.option', $oField->Field);
				}
			}
		}
		$fieldDropDown = JHTML::_('select.genericlist', $fieldNames, $name, "class=\"inputbox\"  size=\"1\" ", 'value', 'text', '');
		echo $fieldDropDown;
	}

	function delete()
	{
		// Check for request forgeries
		JSession::checkToken() or die('Invalid Token');
		$app = JFactory::getApplication();
		$input = $app->input;
		$model = JModel::getInstance('List', 'FabrikFEModel');
		$listid = $input->getInt('listid');
		$model->setId($listid);
		$ids = $input->get('ids', array(), 'array');
		$limitstart = $input->getInt('limitstart'. $listid);
		$length = $input->getInt('limit' . $listid);
		$oldtotal = $model->getTotalRecords();
		$model->deleteRows($ids);
		$total = $oldtotal - count($ids);
		if ($total >= $limitstart)
		{
			$newlimitstart = $limitstart - $length;
			if ($newlimitstart < 0)
			{
				$newlimitstart = 0;
			}
			$context = 'com_fabrik.list' . $model->getRenderContext() . '.list.';
			$app->setUserState($context . 'limitstart' . $listid, $newlimitstart);
		}
		$input->set('view', 'list');
		$this->view();

	}

	public function filter()
	{
		// Check for request forgeries
		//JSession::checkToken() or die('Invalid Token');
		$app = JFactory::getApplication();
		$input = $app->input;
		$model = JModel::getInstance('List', 'FabrikFEModel');
		$id = $input->getInt('listid');
		$model->setId($id);
		$input->set('cid', $id);
		$request = $model->getRequestData();
		$model->storeRequestData($request);
		$this->view();
	}

	/**
	* Show the lists data in the admin
	*/

	public function view()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$cid = $input->get('cid', array(0), 'array');
		$cid = $cid[0];
		$cid = $input->getInt('listid', $cid);

		// Grab the model and set its id
		$model = JModel::getInstance('List', 'FabrikFEModel');
		$model->setState('list.id', $cid);
		$viewType = JFactory::getDocument()->getType();

		// Use the front end renderer to show the table
		$this->setPath('view', COM_FABRIK_FRONTEND . '/views');
		$viewLayout	= $input->get('layout', 'default');
		$view = $this->getView($this->view_item, $viewType, '');
		$view->setModel($model, true);

		// Set the layout
		$view->setLayout($viewLayout);
		JToolBarHelper::title(JText::_('COM_FABRIK_MANAGER_LISTS'), 'lists.png');
		$view->display();
		FabrikAdminHelper::addSubmenu($input->getWord('view', 'lists'));
	}
}
