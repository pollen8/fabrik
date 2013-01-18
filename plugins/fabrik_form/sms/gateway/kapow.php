<?php
/**
 * Send an SMS via the kapow sms gateway
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.sms
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Kapow SMS gateway class
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.sms
 * @since       3.0
 */

class Kapow extends JObject{

	/**
	 * URL To Post SMS to
	 *
	 * @var string
	 */
	var $_url = 'http://www.kapow.co.uk/scripts/sendsms.php?username=%s&password=%s&mobile=%s&sms=%s';

	function process($message)
	{
		$params = $this->getParams();
		$username = $params->get('sms-username');
		$password = $params->get('sms-password');
		$smsto = $params->get('sms-to');
		$smstos = explode(",", $smsto);
		foreach ($smstos as $smsto)
		{
			$url = sprintf($this->_url, $username, $password, $smsto, $message);
			FabrikSMS::doRequest('GET', $url, '');
		}
	}

	function getParams()
	{
		return $this->params;
	}

}
