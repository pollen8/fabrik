<?php
/**
 * Send an push notification with JoomPush
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.joompush
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';


/**
 * Send an JoomPush notification
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.joompush
 * @since       3.0
 */
class PlgFabrik_FormJoompush extends PlgFabrik_Form
{
	/**
	 * Run right at the end of the form processing
	 * form needs to be set to record in database for this to hook to be called
	 *
	 * @return	bool
	 */
	public function onAfterProcess()
	{
		if (JComponentHelper::getComponent('com_joompush', true)->enabled)
		{
			require_once JPATH_ROOT . '/components/com_joompush/helpers/jpush.php';
			require_once JPATH_ROOT . '/components/com_joompush/helpers/joompush.php';
			return $this->process();
		}

		return false;
	}

	/**
	 * Send JoomPush notification
	 *
	 * @return	bool
	 */
	protected function process()
	{
		$formModel = $this->getModel();
		$params = $this->getParams();
		$data = $formModel->formData;
		$result = false;

		if (!$this->shouldProcess('joompush_conditon', $data, $params))
		{
			return true;
		}

		$message = $this->getMessage();

		if (!empty($message))
		{
			$rowid = $this->app->input->get('rowid', '', 'string');
			$title      = $this->getTitle();
			$url = JRoute::_('index.php?option=com_' . $this->package . 'view=details&formid=' . $formModel->get('id') . 'rowid=' . $rowid);
			$track_code = md5(uniqid(rand(), true));

			$pushMsg                    = new stdClass;
			$pushMsg->template->icon    = $params->get('joompush_notification_icon');
			$pushMsg->template->url     = $url;
			$pushMsg->code              = $track_code;
			$pushMsg->template->title   = $title;
			$pushMsg->template->message = $message;
			$pushMsg->gid               = $params->get('joompush_group', $this->getAdminGroupId());

			$JoompushHelpersJpush = new JoompushHelpersJpush;
			$result               = $JoompushHelpersJpush::jtopicPush($pushMsg);

			if ($result)
			{
				$JoompushHelpersJoompush = new JoompushHelpersJoompushsite;
				$JoompushHelpersJoompush->saveNotification('', $pushMsg->gid, 'group', $pushMsg->template, 1, 'com_fabrik.form', $rowid, $track_code);
			}
		}

		return $result;
	}


	/**
	 * Default email handling routine, called if no email template specified
	 *
	 * @return	string	email message
	 */
	protected function getMessage()
	{
		$params = $this->getParams();
		$msg    = $params->get('joompush_message', '');
		$formModel = $this->getModel();
		$data = $formModel->formData;
		$w = new FabrikWorker;
		return $w->parseMessageForPlaceHolder($msg, $data);
	}


	/**
	 * Default email handling routine, called if no email template specified
	 *
	 * @return	string	email message
	 */
	protected function getTitle()
	{
		$params = $this->getParams();
		$msg    = $params->get('joompush_title', '');
		$formModel = $this->getModel();
		$data = $formModel->formData;
		$w = new FabrikWorker;
		return $w->parseMessageForPlaceHolder($msg, $data);
	}

	/**
	 * Get Admin user group.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	protected function getAdminGroupId()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query
			->select('id')
			->from($db->quoteName('#__joompush_subscriber_groups'))
			->where($db->quoteName('is_default') . ' = 2');
		$db->setQuery($query);
		return $db->loadResult();
	}
}
