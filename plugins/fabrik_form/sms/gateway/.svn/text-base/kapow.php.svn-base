<?php
/**
 * Send an SMS via the kapow sms gateway
 * @package Joomla
 * @subpackage Fabrik
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class Kapow extends JObject{

  var $_url = 'http://www.kapow.co.uk/scripts/sendsms.php?username=%s&password=%s&mobile=%s&sms=%s';

  function process($message)
  {
    $params =& $this->getParams();
    $username = $params->get('sms-username');
 	  $password = $params->get('sms-password');
 	  $smsto	= $params->get('sms-to');
 	  $smstos = explode(",", $smsto);
 	  foreach ($smstos as $smsto) {
	 	  $url = sprintf($this->_url, $username, $password, $smsto, $message);
	 	  fabrikSMS::_doRequest('GET', $url, '');
 	  }
  }

  function getParams()
  {
    return $this->params;
  }

}
?>