<?php

/**
* Run some php when the form is submitted
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

class plgFabrik_FormPHP extends plgFabrik_Form {

	/**
	 * store the html to insert at the bottom of the form(non-PHPdoc)
	 * @see components/com_fabrik/models/FabrikModelFormPlugin#getBottomContent()
	 */

	public function getBottomContent($params, $formModel)
	{
		$this->html = '';
		if ($params->get('only_process_curl') == 'getBottomContent')
		{
			$this->html = $this->_runPHP($params, $formModel);
 			if ($this->html === false)
 			{
				return JError::raiseWarning(E_WARNING, 'php form plugin failed');
			}
 		}
		return true;
	}

	/**
	 * store the html to insert at the top of the form(non-PHPdoc)
	 * @see components/com_fabrik/models/FabrikModelFormPlugin#getTopContent()
	 */

	function getTopContent($params, $formModel)
	{
		$this->html = '';
		if ($params->get('only_process_curl') == 'getTopContent')
		{
			$this->html = $this->_runPHP($params, $formModel);
 			if ($this->html === false)
 			{
				return false;
			}
 		}
		return true;
	}

	/**
	 * get the html to insert after the end of the form(non-PHPdoc)
	 * @see components/com_fabrik/models/FabrikModelFormPlugin#getEndContent_result()
	 */

	function getEndContent_result($c)
	{
		return $this->html;
	}

	/**
	 * store the html to insert after the end of the form(non-PHPdoc)
	 * @see components/com_fabrik/models/FabrikModelFormPlugin#getEndContent()
	 */

	function getEndContent(&$params, &$formModel)
	{
		$this->html = '';
		if ($params->get('only_process_curl') == 'getEndContent')
		{
			$this->html = $this->_runPHP($params, $formModel);
 			if ($this->html === false)
 			{
				return false;
			}
 		}
		return true;
	}

	/**
	 *
	 * @param	object	$params
	 * @param	object	$formModel
	 */

 	public function onBeforeProcess($params, &$formModel)
 	{
 		if ($params->get('only_process_curl') == 'onBeforeProcess')
 		{
 			if ($this->_runPHP($params, $formModel) === false)
 			{
				return false;
			}
 		}
 		return true;
 	}

 	function onBeforeStore($params, &$formModel)
 	{
 	 	if ($params->get('only_process_curl') == 'onBeforeStore')
 	 	{
 			if ($this->_runPHP($params, $formModel) === false)
 			{
				return false;
			}
 		}
 		return true;
 	}

 	/**
 	 * (non-PHPdoc)
 	 * @see plgFabrik_Form::onBeforeCalculations()
 	 */
 	
 	function onBeforeCalculations($params, $formModel)
 	{
 	 	if ($params->get('only_process_curl') == 'onBeforeCalculations')
 	 	{
 	 		if ($this->_runPHP($params, $formModel) === false)
 	 		{
				return JError::raiseWarning(E_WARNING, 'php form plugin failed');
			}
 		}
 		return true;
 	}

 	public function onAfterProcess($params, &$formModel)
 	{
 	 	if ($params->get('only_process_curl') == 'onAfterProcess')
 	 	{
 			if ($this->_runPHP($params, $formModel) === false)
 			{
				return false;
			}
 		}
 		return true;
 	}

 	/**
 	 * run when the form is loaded - after its data has been created
 	 * data found in $formModel->_data
 	 * @param	object	$params
 	 * @param	object	$formModel
 	 * @return	unknown_type
 	 */

 	function onLoad( &$params, &$formModel)
 	{
 	 	if ($params->get('only_process_curl') == 'onLoad')
 	 	{
 			return $this->_runPHP($params, $formModel);
 		}
 		return true;
 	}

 	/**
 	* run when the form is loaded - before its data has been created
 	* data found in $formModel->_data
 	* @param	object	$params
 	* @param	object	$formModel
 	* @return	bool
 	*/

 	function onBeforeLoad(&$params, &$formModel)
 	{
 		if ($params->get('only_process_curl') == 'onBeforeLoad')
 		{
 			return $this->_runPHP($params, $formModel);
 		}
 		return true;
 	}

 	/**
 	 * process the plugin, called when form is submitted
 	 * @param	object	$params
 	 * @param	object	form model
 	 */

 	public function onError($params, &$formModel)
 	{
 	 	if ($params->get('only_process_curl') == 'onError')
 	 	{
 			$this->_runPHP($params, $formModel);
 		}
 		return true;
	}

	/**
	 * @private
	 * run plugins php code/script
	 * @param object $params
	 * @param object $formModel
	 * @return bool false if error running php code
	 */

	private function _runPHP(&$params, &$formModel)
	{
		/**
		 * if you want to modify the submitted form data
		 * $formModel->updateFormData('tablename___elementname', $newvalue);
		 */

		// $$$ rob this is poor when submitting the form the data is stored in _formData, when editing its stored in _data -
		// as this method can run on render or on submit we have to do a little check to see which one we should use.
		// really we should use the same form property to store the data regardless of form state
		if (!empty($formModel->_formData))
		{
			$this->html = $formModel->_formData;
		}
		else
		{
			$this->html = $formModel->_data;
		}
		if ($params->get('form_php_file') == -1)
		{
			$w = new FabrikWorker;
			$code = $w->parseMessageForPlaceHolder($params->get('curl_code', ''), $this->html, true, true);
			return eval($code);
		}
		else
		{
			// $$$ hugh - give them some way of getting at form data
			// (I'm never sure if $_REQUEST is 'safe', i.e. if it has post-validation data)
			global $fabrikFormData, $fabrikFormDataWithTableName;
			// for some reason, = wasn't working??
			$fabrikFormData = $this->html;
			// $$$ hugh - doesn't exist for tableless forms
			if (isset($formModel->_formDataWithtableName))
			{
				$fabrikFormDataWithTableName = $formModel->_formDataWithtableName;
			}
			$php_file = JFilterInput::getInstance()->clean($params->get('form_php_file'), 'CMD');
			$php_file = JPATH_ROOT . '/plugins/fabrik_form/php/scripts/' . $php_file;

			if (!JFile::exists($php_file))
			{
				JError::raiseNotice(500, 'Mssing PHP form plugin file');
				return;
			}
			$method = $params->get('only_process_curl');
			if ($method == 'getBottomContent' || $method == 'getTopContent' || $method == 'getEndContent')
			{
				//for these types of scripts any out put you want to inject into the form should be echo'd out
				// @TODO - shouldn't we apply this logic above as well (direct eval)?
				ob_start();
				require($php_file);
				$output = ob_get_contents();
				ob_end_clean();
				return $output;
			}
			else
			{
				$php_result = require($php_file);
			}
			if ($php_result === false)
			{
				return false;
			}
		}
		return true;
	}

}
?>