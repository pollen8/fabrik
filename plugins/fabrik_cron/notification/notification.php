<?php

/**
 * A cron task to email records to a give set of users
 * @package     Joomla
 * @subpackage  Fabrik
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';

class plgFabrik_Cronnotification extends plgFabrik_Cron
{

	/**
	 * Check if the user can use the active element
	 *
	 * @param   object  &$model    calling the plugin list/form
	 * @param   string  $location  to trigger plugin on
	 * @param   string  $event     to trigger plugin on
	 *
	 * @return  bool can use or not
	 */

	public function canUse(&$model = null, $location = null, $event = null)
	{
		return true;
	}

	/**
	 * do the plugin action
	 * @return number of records updated
	 */

	function process(&$data)
	{

		$db = FabrikWorker::getDbo();

		$sql = "SELECT n.*, e.event AS event, e.id AS event_id,
		n.user_id AS observer_id, observer_user.name AS observer_name, observer_user.email AS observer_email,
		e.user_id AS creator_id, creator_user.name AS creator_name, creator_user.email AS creator_email
		 FROM #__{package}_notification AS n" . "\n LEFT JOIN #__{package}_notification_event AS e ON e.reference = n.reference"
			. "\n LEFT JOIN #__{package}_notification_event_sent AS s ON s.notification_event_id = e.id"
			. "\n INNER JOIN #__users AS observer_user ON observer_user.id = n.user_id"
			. "\n INNER JOIN #__users AS creator_user ON creator_user.id = e.user_id" . "\n WHERE (s.sent <> 1 OR s.sent IS NULL)"
			. "\n AND  n.user_id <> e.user_id" . "\n ORDER BY n.reference"; //don't bother informing users about events that they've created themselves
		$db->setQuery($sql);
		$rows = $db->loadObjectList();

		$config = JFactory::getConfig();
		$email_from = $config->get('mailfrom');
		$sitename = $config->get('sitename');
		$sent = array();
		$usermsgs = array();
		foreach ($rows as $row)
		{
			/*
			 * {observer_name, creator_name, event, record url
			 * dear %s, %s has %s on %s
			 */
			$event = JText::_($row->event);
			list($listid, $formid, $rowid) = explode('.', $row->reference);

			$url = JRoute::_('index.php?option=com_fabrik&view=details&listid=' . $listid . '&formid=' . $formid . '&rowid=' . $rowid);
			$msg = JText::sprintf('FABRIK_NOTIFICATION_EMAIL_PART', $row->creator_name, $url, $event);
			if (!array_key_exists($row->observer_id, $usermsgs))
			{
				$usermsgs[$row->observer_email] = array();
			}
			$usermsgs[$row->observer_email][] = $msg;

			$sent[] = 'INSERT INTO #__{package}_notification_event_sent (`notification_event_id`, `user_id`, `sent`) VALUES (' . $row->event_id
				. ', ' . $row->observer_id . ', 1)';
		}
		$subject = $sitename . ": " . JText::_('FABRIK_NOTIFICATION_EMAIL_SUBJECT');
		foreach ($usermsgs as $email => $messages)
		{
			$msg = implode(' ', $messages);
			$res = JUtility::sendMail($email_from, $email_from, $email, $subject, $msg, true);
		}
		if (!empty($sent))
		{
			$sent = implode(';', $sent);
			$db->setQuery($sent);
			$db->query();
		}
	}

	/**
	 * show a new for entering the form actions options
	 */

	function renderAdminSettings()
	{
		//JHTML::stylesheet('fabrikadmin.css', 'administrator/components/com_fabrik/views/');
		$this->getRow();
		$pluginParams = $this->getParams();

		$document = JFactory::getDocument();
?>
<div id="page-<?php echo $this->_name; ?>" class="pluginSettings"
	style="display: none"><?php
		echo $pluginParams->render('params');
						  ?></div>

	<?php
		return;
	}

}
			?>
