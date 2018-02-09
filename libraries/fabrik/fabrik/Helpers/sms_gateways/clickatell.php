<?php
/**
 * Twilio SMS gateway class
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.sms
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Clickatell\Api\ClickatellRest;
use Fabrik\Helpers\ArrayHelper;

/**
 * Twilio SMS gateway class
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.sms
 * @since       3.0
 */

class Clickatell extends JObject
{
	/**
	 * Send SMS
	 *
	 * @param   string  $message  sms message
	 * @param   array   $opts     options
	 *
	 * @return  void
	 */

	public function process($message = '', $opts)
	{
		$sid = ArrayHelper::getValue($opts, 'sms-username');
		$token = ArrayHelper::getValue($opts, 'sms-password');

		if (empty($token) && !empty($sid))
		{
			$token = $sid;
		}

		$smsto = ArrayHelper::getValue($opts, 'sms-to');

		// From a valid Twilio number
		$smsfrom = ArrayHelper::getValue($opts, 'sms-from');
		$smstos = empty($smsto) ? array() : explode(",", $smsto);

		//$client = new Twilio\Rest\Client($sid, $token);
		$client = new ClickatellRest($token);

		try {
			$response = $client->sendMessage(
				$smstos,
				$message
			);
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		foreach ($response as $item)
		{
			if ($item->error === false)
			{
				JFactory::getApplication()->enqueueMessage('SMS failed with error code: ' . $item->errorCode, 'error');

				return false;
			}
		}

		return true;
	}
}
