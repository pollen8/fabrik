<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.rest
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
 * Submit or update data to a REST service
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.rest
 * @since       3.0
 */

class plgFabrik_FormRest extends plgFabrik_Form
{

	/**
	 * Run right at the end of the form processing
	 * form needs to be set to record in database for this to hook to be called
	 *
	 * @param   object  $params      plugin params
	 * @param   object  &$formModel  form model
	 *
	 * @return	bool
	 */

	public function onAfterProcess($params, &$formModel)
	{
		$w = new FabrikWorker;
		$config_userpass = $params->get('username') . ':' . $params->get('password');
		$endpoint = $params->get('endpoint');

		$headers = array('Content-Type: application/xml', 'Accept: application/xml');

		// Set where we should post the REST request to
		$endpoint = $w->parseMessageForPlaceholder($endpoint);
		$endpoint = $w->parseMessageForPlaceholder($endpoint);

		// What is the root node for the xml data we are sending
		$xmlParent = $params->get('xml_parent', 'ticket');
		$xmlParent = $w->parseMessageForPlaceholder($xmlParent);
		$xml = new SimpleXMLElement('<' . $xmlParent . '></' . $xmlParent . '>');

		// Set up CURL object
		$chandle = curl_init();

		// Set which fields should be included in the XML data.
		$include = $w->parseMessageForPlaceholder($params->get('include_list', 'milestone-id, status, summary'));
		$include = explode(',', $include);
		foreach ($include as $i)
		{
			if (array_key_exists($i, $formModel->_formData))
			{
				$xml->addChild($i, $formModel->_formData[$i]);
			}
			elseif (array_key_exists($i, $formModel->_fullFormData, $i))
			{
				$xml->addChild($i, $formModel->_fullFormData[$i]);
			}
		}

		$output = $xml->asXML();

		$curl_options = array(CURLOPT_URL => $endpoint, CURLOPT_RETURNTRANSFER => 1, CURLOPT_HTTPHEADER => $headers, CURLOPT_POST => 1,
			CURLOPT_POSTFIELDS => $output, CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0, CURLOPT_USERPWD => $config_userpass,
			CURLOPT_CUSTOMREQUEST => 'POST');

		foreach ($curl_options as $key => $value)
		{
			curl_setopt($chandle, $key, $value);
		}
		$output = curl_exec($chandle);

		$httpCode = curl_getinfo($chandle, CURLINFO_HTTP_CODE);
		switch ($httpCode)
		{
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
	}

}
