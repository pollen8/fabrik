<?php


/**
 * Submit or update data to Salesforce.com
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

class plgFabrik_FormSalesforce extends plgFabrik_Form {

	var $_data = null;

	private function client()
	{
		//Get the path to the Toolkit, set in the options on install.
		$toolkit_path = JPATH_SITE . '/components/com_fabrik/libs/salesforce';

		//Ok, now use SOAP to send the information to SalesForce
		require_once($toolkit_path .'/soapclient/SforcePartnerClient.php');
		require_once($toolkit_path.'/soapclient/SforceHeaderOptions.php');

		// Salesforce Login information
		$wsdl = $toolkit_path . '/soapclient/partner.wsdl.xml';
		// Process of logging on and getting a salesforce.com session
		$client = new SforcePartnerClient();
		$client->createConnection($wsdl);
		return $client;
	}

	public function getBottomContent($params, $formModel)
	{
		if (!class_exists('SoapClient'))
		{
			JError::raiseWarning(E_WARNING, "Salesforce Plug-in: PHP has not been compiled with the SOAP extension. We will be unable to send this data to Salesforce.com");
		}
	}

	public function onAfterProcess($params, &$formModel)
	{
		@ini_set("soap.wsdl_cache_enabled", "0");

		$client = $this->client();
		$userName = $params->get('salesforce_username');
		$password = $params->get('salesforce_password');
		$token = $params->get('salesforce_token');
		$updateObject = $params->get('salesforce_updateobject', 'Lead');
		$loginResult = $client->login($userName, $password.$token);

		$givenObject = array($updateObject);
		$fields = $client->describeSObjects($givenObject)->fields;
		$submission = array();
		//map the posted data into acceptable fields
		foreach ($fields as $f)
		{
			$name = $f->name;
			foreach ( $formModel->_fullFormData as $key => $val)
			{
				if (is_array($val))
				{
					$val = implode(';', $val);
				}
				$key = array_pop(explode('___', $key));
				if (strtolower($key) == strtolower($name) && strtolower($name) != 'id')
				{
					$submission[$name] = $val;
				}
				else
				{
					// check custom fields
					if (strtolower($key.'__c') == strtolower($name) && strtolower($name) != 'id')
					{
						$submission[$name] = $val;
					}
				}
			}
		}

		$key = FabrikString::safeColNameToArrayKey($formModel->getlistModel()->getTable()->db_primary_key);
		$customkey =$params->get('salesforce_customid') . '__c';
		if ($params->get('salesforce_allowupsert', 0))
		{
			$submission[$customkey] = $formModel->_fullFormData[$key];
		}
		$sObjects = array();
		$sObject = new sObject();
		$sObject->type = $updateObject; // Salesforce Table or object that you will perform the upsert on
		$sObject->fields = $submission;
		array_push($sObjects, $sObject);
		$app = JFactory::getApplication();
		if ($params->get('salesforce_allowupsert', 0))
		{
			$result = $this->upsert($client, $sObjects, $customkey);
		}
		else
		{
			$result = $this->insert($client, $sObjects);
		}
		if ($result->success == 1)
		{
			if ($result->created == '' && $params->get('salesforce_allowupsert', 0))
			{
				$app->enqueueMessage(JText::sprintf(SALESFORCE_UPDATED, $updateObject));
			}
			else
			{
				$app->enqueueMessage(JText::sprintf(SALESFORCE_CREATED, $updateObject));
			}
		}
		else
		{
			if (isset($result->errors))
			{
				if (is_array($result->errors))
				{
					foreach ($result->errors as $error)
					{
						JError::raiseWarning(500, JText::_('SALESFORCE_ERR').$errors->message);
					}
				}
				else
				{
					JError::raiseWarning(500, JText::_('SALESFORCE_ERR'). $result->errors->message);
				}
			}
			else
			{
				JError::raiseWarning(500, JText::sprintf(SALESFORCE_NOCREATE, $updateObject));
			}
		}
	}

	function upsert($client, $sObjects, $key)
	{
		try
		{
			// The upsert process
			$results = $client->upsert($key, $sObjects);
			return $results;
		}
		catch (exception $e)
		{
			// This is reached if there is a major problem in the data or with
			// the salesforce.com connection. Normal data errors are caught by
			// salesforce.com
			return $e;
		}
	}

	function insert($client, $sObjects)
	{
		try
		{
			// The upsert process
			$results = $client->create($sObjects);
			return $results;
		}
		catch (exception $e)
		{
			// This is reached if there is a major problem in the data or with
			// the salesforce.com connection. Normal data errors are caught by
			// salesforce.com
			return $e;
		}
	}

}
?>