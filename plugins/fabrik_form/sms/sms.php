<?php
/**
 * Send an SMS
 * @package     Joomla
 * @subpackage  Fabrik
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';
require_once (COM_FABRIK_FRONTEND . '/helpers/sms.php';

class PlgFabrik_FormSMS extends PlgFabrik_Form
{

	/**
	 * process the plugin, called when form is submitted
	 * @param   object	$params
	 * @param   object	form model
	 */

	public function onAfterProcess($params, &$formModel)
	{
		$this->process($params, $formModel);
	}

	function process($params, &$formModel)
	{
		$this->formModel = $formModel;
		$message = $this->getMessage();
		$aData = $oForm->formData;
		$gateway = $this->getInstance();
		$gateway->process($message);
	}

	function getInstance()
	{
		if (!isset($this->gateway))
		{
			$params = $this->getParams();
			$gateway = JFilterInput::clean($params->get('sms-gateway', 'kapow.php'), 'CMD');
			require_once(JPATH_ROOT . '/plugins/fabrik_form/sms/gateway/' . JString::strtolower($gateway));
			$gateway = JFile::stripExt($gateway);
			$this->gateway = new $gateway();
			$this->gateway->params = $params;
		}
		return $this->gateway;
	}

	/**
	 * default email handling routine, called if no email template specified
	 * @return  string	email message
	 */

	protected function getMessage()
	{
		$config = JFactory::getConfig();
		$data = $this->formModel->formData;
		$arDontEmailThesKeys = array();
		/*remove raw file upload data from the email*/
		foreach ($_FILES as $key => $file)
		{
			$arDontEmailThesKeys[] = $key;
		}
		$message = "";
		$pluginManager = FabrikWorker::getPluginManager();
		$groups = $this->formModel->getGroupsHiarachy();
		foreach ($groups as $groupModel)
		{
			$elementModels = $groupModel->getPublishedElements();
			foreach ($elementModels as $elementModel)
			{
				$element = $elementModel->getElement();
				$element->label = strip_tags($element->label);
				if (!array_key_exists($element->name, $data))
				{
					$elName = $element->getFullName();
				}
				else
				{
					$elName = $element->name;
				}
				$key = $elName;
				if (!in_array($key, $arDontEmailThesKeys))
				{
					if (array_key_exists($elName, $data))
					{
						$val = stripslashes($data[$elName]);
						$params = $elementModel->getParams();
						if (method_exists($elementModel, 'getEmailValue'))
						{
							$val = $elementModel->getEmailValue($val);
						}
						else
						{
							if (is_array($val))
							{
								$val = implode("\n", $val);
							}
						}
						$val = FabrikString::rtrimword($val, '<br />');
						$message .= $element->label . ': ' . $val . "\r\n";
					}
				}
			}
		}
		$message = JText::_('PLG_FORM_SMS_FROM') . $config->get('sitename') . "\r \n \r \nMessage:\r \n" . stripslashes($message);
		return $message;
	}

}
?>