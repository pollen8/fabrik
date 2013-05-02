<?php
/**
 * Raw List controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       1.6
 */

// No direct access
defined('_JEXEC') or die;

require_once 'fabcontrollerform.php';

/**
 * Raw List controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       3.0
 */

class FabrikControllerList extends FabControllerForm
{
	/**
	 * The prefix to use with controller messages.
	 *
	 * @var	string
	 */
	protected $text_prefix = 'COM_FABRIK_LIST';

	/**
	 * Ajax load drop down of all columns in a given table
	 *
	 * @return  null
	 */

	public function ajax_loadTableDropDown()
	{
		$conn = JRequest::getInt('conn', 1);
		$oCnn = JModel::getInstance('Connection', 'FabrikFEModel');
		$oCnn->setId($conn);
		$oCnn->getConnection();
		$db = $oCnn->getDb();
		$table = JRequest::getVar('table', '');
		$fieldNames = array();
		if ($table != '')
		{
			$table = FabrikString::safeColName($table);
			$name = JRequest::getVar('name', 'jform[params][table_key][]');
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

	/**
	 * Delete list items
	 *
	 * @return  null
	 */

	public function delete()
	{
		// Check for request forgeries
		JSession::checkToken() or die('Invalid Token');
		$app = JFactory::getApplication();
		$model = JModel::getInstance('List', 'FabrikFEModel');
		$listid = JRequest::getInt('listid');
		$model->setId($listid);
		$ids = JRequest::getVar('ids', array(), 'request', 'array');
		$limitstart = JRequest::getVar('limitstart' . $listid);
		$length = JRequest::getVar('limit' . $listid);
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
		JRequest::setVar('view', 'list');
		$this->view();

	}

	/**
	 * Show the lists data in the admin
	 *
	 * @param   object  $model  list model
	 *
	 * @return  void
	 */

	public function view($model = null)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$cid = JRequest::getVar('cid', array(0), 'method', 'array');
		if (is_array($cid))
		{
			$cid = $cid[0];
		}
		if (is_null($model))
		{
			$cid = JRequest::getInt('listid', $cid);

			// Grab the model and set its id
			$model = JModel::getInstance('List', 'FabrikFEModel');
			$model->setState('list.id', $cid);
		}

		$viewType = JFactory::getDocument()->getType();

		// Use the front end renderer to show the table
		$this->setPath('view', COM_FABRIK_FRONTEND . '/views');
		$viewLayout = $input->getWord('layout', 'default');
		$view = $this->getView($this->view_item, $viewType, '');
		$view->setModel($model, true);

		// Set the layout
		$view->setLayout($viewLayout);
		JToolBarHelper::title(JText::_('COM_FABRIK_MANAGER_LISTS'), 'lists.png');
		$view->display();
		FabrikAdminHelper::addSubmenu(JRequest::getWord('view', 'lists'));
	}

	/**
	 * Order the lists
	 *
	 * @return  null
	 */

	public function order()
	{
		// Check for request forgeries
		JRequest::checkToken() or die('Invalid Token');
		$model = JModel::getInstance('List', 'FabrikFEModel');
		$id = JRequest::getInt('listid');
		$model->setId($id);
		JRequest::setvar('cid', $id);
		$model->setOrderByAndDir();

		// $$$ hugh - unset 'resetfilters' in case it was set on QS of original table load.
		JRequest::setVar('resetfilters', 0);
		JRequest::setVar('clearfilters', 0);
		$this->view();
	}

	/**
	 * Clear filters
	 *
	 * @return  null
	 */

	public function clearfilter()
	{
		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::_('COM_FABRIK_FILTERS_CLEARED'));
		$app->input->set('clearfilters', 1);
		$this->filter();
	}

	/**
	 * Filter list items
	 *
	 * @return  null
	 */

	public function filter()
	{
		// Check for request forgeries
		JSession::checkToken() or die('Invalid Token');
		$app = JFactory::getApplication();
		$model = JModel::getInstance('List', 'FabrikFEModel');
		$id = $app->input->getInt('listid');
		$model->setId($id);
		JRequest::setVar('cid', $id);
		$app->input->set('cid', $id);
		$request = $model->getRequestData();
		$model->storeRequestData($request);

		// Pass in the model otherwise display() rebuilds it and the request data is rebuilt
		$this->view($model);
	}

	/**
	 * Called via ajax when element selected in advanced search popup window
	 * OR in update_col plugin
	 *
	 * @return  null
	 */

	public function elementFilter()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$id = $input->getInt('id');
		$model = $this->getModel('list', 'FabrikFEModel');
		$model->setId($id);
		echo $model->getAdvancedElementFilter();
	}
}
