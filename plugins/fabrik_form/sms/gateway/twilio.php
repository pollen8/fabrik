<?php
/**
 * Send an SMS via the twilio sms gateway
 * 
 * requires https://github.com/cory-webb-media/Twilio-for-Joomla
 * 
 * @package Joomla
 * @subpackage Fabrik
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class Twilio extends JObject{

	function process($message = '')
	{
		jimport('twilio.services.twilio');
		$params = $this->getParams();
		$username = $params->get('sms-username');
		$token = $params->get('sms-password');
		$smsto = $params->get('sms-to');
		$smsfrom = $params->get('sms-from'); // From a valid Twilio number
		$smstos = explode(",", $smsto);
		foreach ($smstos as $smsto)
		{
			$client = new Services_Twilio($username, $token);
			$call = $client->account->sms_messages->create(
			$smsfrom,
			$smsto,
			$message
			);
				
		}
	}

	function getParams()
	{
		return $this->params;
	}

}

?>
