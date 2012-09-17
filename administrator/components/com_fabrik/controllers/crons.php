<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       3.0
 */

// No direct access.
defined('_JEXEC') or die;

require_once 'fabcontrolleradmin.php';

/**
 * Cron list controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       3.0
 */

class FabrikAdminControllerCrons extends FabControllerAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */

	protected $text_prefix = 'COM_FABRIK_CRONS';

	protected $view_item = 'crons';

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    model name
	 * @param   string  $prefix  model prefix
	 *
	 * @return  J model
	 */

	public function &getModel($name = 'Cron', $prefix = 'FabrikModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 * Run the selected cron plugins
	 *
	 * @return  void
	 */

	public function run()
	{
		$db = FabrikWorker::getDbo(true);
		$cid = JRequest::getVar('cid', array(0), 'method', 'array');
		JArrayHelper::toInteger($cid);
		$cid = implode(',', $cid);
		$query = $db->getQuery(true);
		$query->select('*')->from('#__{package}_cron')->where('id IN (' . $cid . ')');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$adminListModel = JModelLegacy::getInstance('List', 'FabrikModel');
		$pluginManager = JModelLegacy::getInstance('Pluginmanager', 'FabrikFEModel');
		$listModel = JModelLegacy::getInstance('list', 'FabrikFEModel');
		$c = 0;
		$log = FabTable::getInstance('Log', 'FabrikTable');
		foreach ($rows as $row)
		{
			// Load in the plugin
			$rowParams = json_decode($row->params);
			$log->message = '';
			$log->id = null;
			$log->referring_url = '';
			$log->message_type = 'plg.cron.' . $row->plugin;
			$plugin = $pluginManager->getPlugIn($row->plugin, 'cron');
			$table = FabTable::getInstance('cron', 'FabrikTable');
			$table->load($row->id);
			$plugin->setRow($table);
			$params = $plugin->getParams();
			$thisListModel = clone ($listModel);
			$thisAdminListModel = clone ($adminListModel);
			$tid = (int) $rowParams->table;
			if ($tid !== 0)
			{
				$thisListModel->setId($tid);
				$log->message .= "\n\n$row->plugin\n listid = " . $thisListModel->getId();
				if ($plugin->requiresTableData())
				{
					$table = $listModel->getTable();
					$data = $thisListModel->getData();
					$log->message .= "\n" . $thisListModel->buildQuery();
				}
			}
			else
			{
				$data = array();
			}
			// $$$ hugh - added table model param, in case plugin wants to do further table processing
			$c = $c + $plugin->process($data, $thisListModel, $thisAdminListModel);
			if ($plugin->getParams()->get('log', 0) == 1)
			{
				$log->message = $plugin->getLog() . "\n\n" . $log->message;
				$log->store();
			}
		}
		$this->setRedirect('index.php?option=com_fabrik&view=crons', $c . ' records updated');
	}

}
