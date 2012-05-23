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

class Itagg extends JObject{

	var $_url = 'https://secure.itagg.com/smsg/sms.mes';

	function process($message)
	{
		$params = $this->getParams();
		$username = $params->get('sms-username');
		//$username = urlencode($username);
		$password = $params->get('sms-password');
		//$password = urlencode($password);
		$smsfrom  = urlencode($params->get('sms-from'));
		$smsto = $params->get('sms-to');
		$smstos = explode(",", $smsto);
		
		$message = urlencode($message);
		$message = 'test';
		foreach ($smstos as $smsto)
		{
			
			if(substr($smsto, 0, 1) == '+' && substr($smsto, 1, 2) != '44')
			{
				$route= 8; //global sms
			}
			else
			{
				$route = 7; // UK (itagg)
			}
			$smsto = urlencode($smsto);
			
			$url = $this->_url;
			echo "url = $url <br>";
			$vars = 'usr=' . $username . '&pwd=' . $password . '&from=rob&to=' . $smsto . '&type=text&route=' . $route . '&txt=' . $message;
			echo $vars;
			
			$itaggapi = "https://secure.itagg.com/smsg/sms.mes";
			/* $params="usr=XXX&pwd=YYY&from=steve&to=07712345678,447912345678,3912345678&type=text&rout
			e=7&txt=hello+via+POST"; */
			$ch = curl_init();
			if (!$ch) {
				echo "cant ini curl session";exit;
			}
			curl_setopt($ch, CURLOPT_URL, $itaggapi);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
			
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			
			$returned = curl_exec ($ch);
			curl_close ($ch);
			print($returned); // This will be the OK / error message
					if ($returned === true) {
						echo "sent ok";
					}
			exit;
			$res = fabrikSMS::doRequest('POST', $url, $vars);
			echo "<pre>res = ";print_r($res);
			exit;
		}
	}

	function getParams()
	{
		return $this->params;
	}

}
?>