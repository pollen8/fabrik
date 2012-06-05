<?php
/**
 * @package		Joomla
 * @subpackage fabrik
 * @copyright	Copyright (C) 2005 - 2008 Pollen 8 Design Ltd. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die( 'Restricted access');

jimport('joomla.plugin.plugin');
jimport('joomla.filesystem.file');

/**
 * Joomla! Fabrik cron job plugin
 *
 * @author		Rob Clayburn <rob@pollen-8.co.uk>
 * @package		Joomla
 * @subpackage	fabrik
 */
class plgSystemFabrikcron extends JPlugin
{

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @access	protected
	 * @param	object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since	1.0
	 */

	function plgSystemFabrikcron(& $subject, $config)
	{
		parent::__construct($subject, $config);

	}

	function doCron()
	{
		$app = JFactory::getApplication();
		if ($app->isAdmin() || JRequest::getVar('option') == 'com_acymailing') {
			return;
		}
		// $$$ hugh - don't want to run on things like AJAX calls
		if (JRequest::getVar('format', '') == 'raw') {
			return;
		}
		//3.0 done in inAfterInitialize()
		//$defines = JFile::exists(JPATH_SITE . '/components/com_fabrik/user_defines.php') ? JPATH_SITE . '/components/com_fabrik/user_defines.php' : JPATH_SITE . '/components/com_fabrik/defines.php';
		//require_once($defines);
		/* jimport('joomla.application.component.model');
		require_once(COM_FABRIK_FRONTEND . '/helpers/params.php');
		require_once(COM_FABRIK_FRONTEND . '/helpers/string.php');
		require_once(COM_FABRIK_FRONTEND . '/helpers/html.php');
		require_once(COM_FABRIK_FRONTEND . '/models/parent.php');
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fabrik/tables');
		 */
		//get all active tasks
		$db = FabrikWorker::getDbo(true);
		$now = JRequest::getVar('fabrikcron_run', false);

		$log = FabTable::getInstance('Log', 'FabrikTable');

		if (!$now)
		{
			// $$$ hugh - changed from using NOW() to JFactory::getDate(), to avoid time zone issues, see:
			// http://fabrikar.com/forums/showthread.php?p=102245#post102245
			// .. which seems reasonable, as we use getDate() to set 'lastrun' to at the end of this func

			$nextrun = "CASE "
			."WHEN unit = 'second' THEN DATE_ADD( lastrun, INTERVAL frequency SECOND )\n"
			."WHEN unit = 'minute' THEN DATE_ADD( lastrun, INTERVAL frequency MINUTE )\n"
			."WHEN unit = 'hour' THEN DATE_ADD( lastrun, INTERVAL frequency HOUR )\n"
			."WHEN unit = 'day' THEN DATE_ADD( lastrun, INTERVAL frequency DAY )\n"
			."WHEN unit = 'week' THEN DATE_ADD( lastrun, INTERVAL frequency WEEK )\n"
			."WHEN unit = 'month' THEN DATE_ADD( lastrun, INTERVAL frequency MONTH )\n"
			."WHEN unit = 'year' THEN DATE_ADD( lastrun, INTERVAL frequency YEAR ) END";

			$query = "SELECT id, plugin, lastrun, unit, frequency, $nextrun AS nextrun FROM #__{package}_cron\n";
			$query .= "WHERE published = '1' ";
			$query .= "AND $nextrun < '". JFactory::getDate()->toMySQL() ."'";
		}
		else
		{
			$query = "SELECT id, plugin FROM #__{package}_cron WHERE published = '1'";
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if (empty($rows))
		{
			return;
		}

		$log->message = '';

		// $$$ hugh - set 'state' to 2 for selected rows, so we don't end up running
		// multiple copies, if this code is run again before selected plugins have
		// finished running, see:
		// http://fabrikar.com/forums/showthread.php?p=114008#post114008
		$ids = array();
		foreach ($rows as $row)
		{
			$ids[] = (int) $row->id;
		}
		$db->setQuery("UPDATE #__{package}_cron SET published='2' WHERE id IN (" . implode(',', $ids) . ")");
		//$db->query();

		JModel::addIncludePath(JPATH_SITE . '/components/com_fabrik/models');
		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');
		$listModel = JModel::getInstance('list', 'FabrikFEModel');

		foreach ($rows as $row)
		{
			$log->message = '';
			$log->id = null;
			$log->referring_url = '';
			//load in the plugin
			//$plugin = $pluginManager->getPlugIn($row->plugin, 'cron');
			//$plugin->setId($row->id);
			$plugin = $pluginManager->getPluginFromId($row->id, 'Cron');
			$log->message_type = 'plg.cron.'.$row->plugin;
			if (!$plugin->queryStringActivated())
			{
				// $$$ hugh - don't forget to make it runnable again before continuing
				$db->setQuery('UPDATE #__{package}_cron SET published="1" WHERE id = '.$row->id);
				$db->query();
				continue;
			}
			$tid = (int) $plugin->getParams()->get('table');
			$thisListModel = clone($listModel);
			if ($tid !== 0)
			{
				$thisListModel->setId($tid);
				$log->message .= "\n\n$row->plugin\n listid = ".$thisListModel->getId();//. var_export($table);
				if ($plugin->requiresTableData())
				{
					$table = $thisListModel->getTable();
					$total = $thisListModel->getTotalRecords();
					$nav = $thisListModel->getPagination($total, 0, $total);
					$data = $thisListModel->getData();
					$log->message .= "\n" . $thisListModel->_buildQuery();
				}
			}
			else
			{
				$data = array();
			}
			$res = $plugin->process($data, $thisListModel);
			$log->message = $plugin->getLog() . "\n\n" . $log->message;
			$now = JFactory::getDate();
			$now = $now->toUnix();
			$new = JFactory::getDate($row->nextrun);
			$tmp = $new->toUnix();

			switch ($row->unit)
			{
				case 'second':
					$inc = 1;
					break;
				case 'minute':
					$inc = 60;
					break;
				case 'hour':
					$inc = 60 * 60;
					break;
				default:
				case 'day':
					$inc = 60 * 60 * 24;
					break;
			}
			//don't use NOW() as the last run date as this could mean that the cron
			//jobs aren't run as frequently as specified
			//if the lastrun date was set in admin to ages ago, then incrementally increase the
			//last run date until it is less than now
			while ($tmp + ($inc * $row->frequency) < $now)
			{
				$tmp = $tmp + ($inc * $row->frequency);
			}

			//mark them as being run
			// $$$ hugh - and make it runnable again by setting 'state' back to 1
			$nextrun = JFactory::getDate($tmp);
			$db->setQuery('UPDATE #__{package}_cron SET published = "1", lastrun = "'.$nextrun->toMySQL() .'" WHERE id = '.$row->id);
			$db->query();
			//log if asked for
			if ($plugin->getParams()->get('log', 0) == 1)
			{
				$log->store();
			}
		}
	}

	/**
	 * perform the actual cron after the page has rendered
	 */

	function onAfterRender()
	{
		$this->doCron();
	}

}