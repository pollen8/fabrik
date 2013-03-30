<?php
/**
 * Submit or update data to a REST service
 *
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
	 * Are we using POST to create new records
	 * or PUT to update existing records
	 *
	 * @return  string
	 */
	protected function requestMethod()
	{
		$method = $this->formModel->_formData['rowid'] == 0 ? 'POST' : 'PUT';
		$fkData = $this->fkData();

		// If existing record but no Fk value stored presume its a POST
		if (empty($fkData))
		{
			$method = 'POST';
		}
		return $method;
	}

	/**
	 * Get the foreign keys value.
	 *
	 * @return  mixed string|int
	 */

	protected function fkData()
	{
		if (!isset($this->fkData))
		{
			$this->fkData = array();

			// Get the foreign key element
			$fkElement = $this->fkElement();
			if ($fkElement)
			{
				$fkElementKey = $fkElement->getFullName();
				$this->fkData = json_decode(JArrayHelper::getValue($this->formModel->_formData, $fkElementKey));
				$this->fkData = JArrayHelper::fromObject($this->fkData);

				$fkEval = $this->params->get('foreign_key_eval', '');
				if ($fkEval !== '')
				{
					$fkData = $this->fkData;
					$eval = eval($fkEval);
					if ($eval !== false)
					{
						$this->fkData = $eval;
					}
				}
			}
		}
		return $this->fkData;
	}

	/**
	 * Get the foreign key element
	 *
	 * @return  object  Fabrik element
	 */

	protected function fkElement()
	{
		return $this->formModel->getElement($this->params->get('foreign_key'), true);
	}

	/**
	 * Run right before the form is processed
	 * form needs to be set to record in database for this to hook to be called
	 *
	 * @param   object  $params      plugin params
	 * @param   object  &$formModel  form model
	 *
	 * @return	bool
	 */

	public function onBeforeStore($params, &$formModel)
	{
		$this->formModel = $formModel;
		$this->params = $params;

		$w = new FabrikWorker;
		if (!function_exists('curl_init'))
		{
			JError::raiseError(500, 'CURL NOT INSTALLED');
			return;
		}
		// The username/password
		$config_userpass = $params->get('username') . ':' . $params->get('password');

		// POST new records, PUT exisiting records
		$method = $this->requestMethod();

		$fkData = $this->fkData();
		$fkElement = $this->fkElement();

		// Set where we should post the REST request to
		$endpoint = $method === 'PUT' ? $params->get('put') : $params->get('endpoint');
		$endpoint = $w->parseMessageForPlaceholder($endpoint);
		$endpoint = str_replace('{fk}', $fkData, $endpoint);

		// What is the root node for the xml data we are sending
		$xmlParent = $params->get('xml_parent', '');
		$xmlParent = $w->parseMessageForPlaceholder($xmlParent);

		// Request headers
		$headers = array();

		// Set up CURL object
		$chandle = curl_init();

		$dataMap = $params->get('put_include_list', '');

		$include = $w->parseMessageForPlaceholder($dataMap, $formModel->_formData, true);
		$endpoint = $w->parseMessageForPlaceHolder($endpoint, $fkData);

		$output = $this->buildOutput($formModel, $include, $xmlParent, $headers);

		$curlOpts = $this->buildCurlOpts($method, $headers, $endpoint, $params, $output);

		foreach ($curlOpts as $key => $value)
		{
			curl_setopt($chandle, $key, $value);
		}
		$output = curl_exec($chandle);
		$jsonOutPut = FabrikWorker::isJSON($output) ? true : false;

		if (!$this->handleError($output, $formModel, $chandle))
		{
			curl_close($chandle);
			return false;
		}

		curl_close($chandle);

		// Set FK value in Fabrik form data
		if ($method === 'POST' && $fkElement)
		{
			if ($jsonOutPut)
			{
				$fkVal = json_encode($output);
			}
			else
			{
				$fkVal = $output;
			}
			$fkElementKey = $fkElement->getFullName();

			$formModel->updateFormData($fkElementKey, $fkVal, true, true);
		}

	}

	/**
	 * Create the data structure containing the data to send
	 *
	 * @param   object  $formModel  Form Model
	 * @param   string  $include    list of fields to include
	 * @param   xml     $xmlParent  Parent node if rendering as xml (ignored if include is json and prob something i want to deprecate)
	 * @param   array   &$headers   Headeres
	 *
	 * @return mixed
	 */

	private function buildOutput($formModel, $include, $xmlParent, &$headers)
	{
		$postData = array();
		$w = new FabrikWorker;
		$fkElement = $this->fkElement();
		$fkData = $this->fkData();

		if (FabrikWorker::isJSON($include))
		{
			$include = json_decode($include);

			$keys = $include->put_key;
			$values = $include->put_value;
			$format = $include->put_type;
			$i = 0;
			$fkName = $fkElement->getFullName(false, true);
			foreach ($values as &$v)
			{
				if ($v === $fkName)
				{
					$v = $fkData;
				}
				else
				{
					$v = FabrikString::safeColNameToArrayKey($v);

					$v = $w->parseMessageForPlaceHolder('{' . $v . '}', $formModel->_formData, true);
				}
				if ($format[$i] == 'number')

				{
					$v = floatval(preg_replace('#^([-]*[0-9\.,\' ]+?)((\.|,){1}([0-9-]{1,2}))*$#e', "str_replace(array('.', ',', \"'\", ' '), '', '\\1') . '.\\4'", $v));
				}
				$i ++;
			}
			$i = 0;
			foreach ($keys as $key)
			{
				// Can be in format "foo[bar]" in which case we want to map into nested array
				preg_match('/\[(.*)\]/', $key, $matches);
				if (count($matches) >= 2)
				{
					$bits = explode('[', $key);
					if (!array_key_exists($bits[0], $postData))
					{
						$postData[$bits[0]] = array();
					}
					$postData[$bits[0]][$matches[1]] = $values[$i];
				}
				else
				{
					$postData[$key] = $values[$i];
				}
				$i ++;
			}
		}
		else
		{
			$include = explode(',', $include);

			foreach ($include as $i)
			{
				if (array_key_exists($i, $formModel->formData))
				{
					$postData[$i] = $formModel->formData[$i];
				}
				elseif (array_key_exists($i, $formModel->fullFormData, $i))
				{
					$postData[$i] = $formModel->fullFormData[$i];
				}
			}
		}
		$postAsXML = false;
		if ($postAsXML)
		{
			$xml = new SimpleXMLElement('<' . $xmlParent . '></' . $xmlParent . '>');
			$headers = array('Content-Type: application/xml', 'Accept: application/xml');
			foreach ($postData as $k => $v)
			{
				$xml->addChild($k, $v);
			}
			$output = $xml->asXML();
		}
		else
		{
			$output = http_build_query($postData);
		}
		return $output;
	}

	/**
	 * Create the CURL options when sending
	 *
	 * @param   string  $method    POST/PUT
	 * @param   array   &$headers  Headers
	 * @param   string  $endpoint  URL to post/put to
	 * @param   object  $params    Plugin params
	 * @param   string  $output    URL Encoded querystring
	 *
	 * @return  array
	 */

	private function buildCurlOpts($method, &$headers, $endpoint, $params, $output)
	{
		// The username/password
		$config_userpass = $params->get('username') . ':' . $params->get('password');
		$curlOpts = array();
		if ($method === 'POST')
		{
			$curlOpts[CURLOPT_POST] = 1;
		}
		else
		{
			$curlOpts[CURLOPT_CUSTOMREQUEST] = "PUT";
		}

		$curlOpts[CURLOPT_URL] = $endpoint;
		$curlOpts[CURLOPT_SSL_VERIFYPEER] = 0;
		$curlOpts[CURLOPT_POSTFIELDS] = $output;
		$curlOpts[CURLOPT_HTTPHEADER] = $headers;
		$curlOpts[CURLOPT_RETURNTRANSFER] = 1;
		$curlOpts[CURLOPT_HTTPAUTH] = CURLAUTH_ANY;
		$curlOpts[CURLOPT_USERPWD] = $config_userpass;
		$curlOpts[CURLOPT_VERBOSE] = true;
		return $curlOpts;
	}

	/**
	 * Handle any error generated
	 *
	 * @param   mixed              &$output    CURL request result - may be a json string
	 * @param   FabrikFEModelForm  $formModel  Form Model
	 * @param   object             $chandle    CURL object
	 *
	 * @return boolean
	 */

	private function handleError(&$output, $formModel, $chandle)
	{
		if (FabrikWorker::isJSON($output))
		{
			$output = json_decode($output);

			// @TODO make this more generic - currently only for apparty
			if (isset($output->errors))
			{
				// Have to set something in the errors array otherwise form validates
				$formModel->_arErrors['dummy___elementname'][] = 'woops!';
				$formModel->getForm()->error = implode(', ', $output->errors);
				return false;
			}
		}
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
			JError::raiseNotice($httpCode, curl_error($chandle));
			return false;
		}
		return true;
	}

}
