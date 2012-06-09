<?php
/**
 * Submit or update data to a REST service
 * @package Joomla
 * @subpackage Fabrik
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND . '/models/plugin-form.php');

class plgFabrik_FormRest extends plgFabrik_Form {

	public function onAfterProcess($params, &$formModel)
	{
		$w = new FabrikWorker();
		$config_userpass = $params->get('username').':'.$params->get('password');
		$endpoint = $params->get('endpoint');

		$headers = array(
			'Content-Type: application/xml',
			'Accept: application/xml'
		);
		$endpoint = $params->get('endpoint');
		$endpoint = $w->parseMessageForPlaceholder($endpoint);

		// Here we set up CURL to grab the data from Unfuddle
		$chandle = curl_init();
		$ticket = new SimpleXMLElement("<ticket></ticket>");

		$ignore = array('trkr_tickets___id');
		if ($formModel->_origRowId == 0) {
			$ingore[] = 'id';
		}
		/*foreach($formModel->_formData as $key => $val){
			if ($formModel->hasElement($key) && !in_array($key, $ignore)) {
				if (is_array($val)) {
					$val = implode(',', $val);
				}
				$ticket->addChild($key, $val);
			}
		}
*/
		/*
		 * $ticket = new SimpleXMLElement("<ticket></ticket>");
		//$ticket->addChild('assignee-id',  $this->config['default_assignee']);
		//$ticket->addChild('component-id', $data['component']);
		//$ticket->addChild('description',  $data['description']);
		$ticket->addChild('milestone-id', 47420);
		//$ticket->addChild('priority',     $data['priority']);
		//$ticket->addChild('severity-id',  $data['severity']);
		$ticket->addChild('status',       'new');
		$ticket->addChild('summary',      'test REST API from fabrik');
		 */

		$include = array('milestone-id', 'status', 'summary');
		foreach ($include as $i) {
			echo "$i = " . $formModel->_formData[$i] . "<br>";
			$ticket->addChild($i, $formModel->_formData[$i]);
		}

		$output = $ticket->asXML();

		$curl_options = array(
		CURLOPT_URL            => $endpoint,
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_HTTPHEADER     => $headers,
		CURLOPT_POST           => 1,
		CURLOPT_POSTFIELDS     => $output,
		CURLOPT_SSL_VERIFYHOST => 0,
		CURLOPT_SSL_VERIFYPEER => 0,
		CURLOPT_USERPWD        => $config_userpass,
		CURLOPT_CUSTOMREQUEST => 'POST'
		);

		foreach ($curl_options as $key => $value)
		{
			curl_setopt($chandle, $key, $value);
		}
		$output = curl_exec($chandle);

		$httpCode = curl_getinfo($chandle, CURLINFO_HTTP_CODE);
		echo $httpCode . " : ";
		switch ($httpCode) {
			case '400':
				echo "Bad Request";
				break;
			case '401':
				echo "Unauthorized";
				break;
			case '404':
				echo "Not found";
				break;
			case '405':
				echo "Method Not Allowed";
				break;
			case '406':
				echo "Not Acceptable";
				break;
			case '415':
				echo "Unsupported Media Type";
				break;
			case '500':
				echo "Internal Server Error";
				break;
		}
	if (curl_errno($chandle))
		{
			die("ERROR: " . curl_error($chandle));
		}
		curl_close($chandle);
exit;
	}

}
?>