<?php
/**
 * Send an SMS via the textopoly sms gateway
 * @package Joomla
 * @subpackage Fabrik
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class Textopoly extends JObject{

	var $_url = 'http://sms.mxtelecom.com/SMSSend?user=%s&pass=%s&smsfrom=%s&smsto=%s&smsmsg=%s';

	function process($message = '')
	{
		$params = $this->getParams();
		$username = $params->get('sms-username');
		$password = $params->get('sms-password');
		$smsto = $params->get('sms-to');
		$smsfrom  = $params->get('sms-from');
		$smstos = explode(",", $smsto);
		foreach ($smstos as $smsto)
		{
			$url = sprintf($this->_url, $username, $password, $smsfrom, $smsto, $message);
			$response = fabrikSMS::doRequest('GET', $url, '');
		}
	}

	function getParams()
	{
		return $this->params;
	}

}
?>