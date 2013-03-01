<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.php
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
 * Run some php when the form is submitted
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.php
 * @since       3.0
 */

class plgFabrik_FormPHP extends plgFabrik_Form
{

	/**
	 * Sets up HTML to be injected into the form's bottom
	 *
	 * @param   object  $params     params
	 * @param   object  $formModel  form model
	 *
	 * @return void
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
	 * Store the html to insert at the top of the form
	 *
	 * @param   object  $params     params
	 * @param   object  $formModel  form model
	 *
	 * @return  bool
	 */

	public function getTopContent($params, $formModel)
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
	 * Get any html that needs to be written after the form close tag
	 *
	 * @return	string	html
	 */

	public function getEndContent_result()
	{
		return $this->html;
	}

	/**
	 * Sets up any end html (after form close tag)
	 *
	 * @param   object  $params     plugin params
	 * @param   object  $formModel  form model
	 *
	 * @return  void
	 */

	public function getEndContent($params, $formModel)
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
	 * Run right at the beginning of the form processing
	 *
	 * @param   object  $params      plpugin params
	 * @param   object  &$formModel  form model
	 *
	 * @return	bool
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

	/**
	 * Before the record is stored, this plugin will see if it should process
	 * and if so store the form data in the session.
	 *
	 * @param   object  $params      params
	 * @param   object  &$formModel  form model
	 *
	 * @return  bool  should the form model continue to save
	 */

	public function onBeforeStore($params, &$formModel)
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
	 * Run before table calculations are applied
	 *
	 * @param   object  $params      plpugin params
	 * @param   object  &$formModel  form model
	 *
	 * @return	bool
	 */

	public function onBeforeCalculations($params, &$formModel)
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
	 * Run when the form is loaded - after its data has been created
	 * data found in $formModel->_data
	 *
	 * @param   object  $params      plugin params
	 * @param   object  &$formModel  form model
	 *
	 * @return	bool
	 */

	public function onLoad($params, &$formModel)
	{
		if ($params->get('only_process_curl') == 'onLoad')
		{
			return $this->_runPHP($params, $formModel);
		}
		return true;
	}

	/**
	 * Run when the form is loaded - before its data has been created
	 * data found in $formModel->_data
	 *
	 * @param   object  $params      plugin params
	 * @param   object  &$formModel  form model
	 *
	 * @return	bool
	 */

	public function onBeforeLoad($params, &$formModel)
	{
		if ($params->get('only_process_curl') == 'onBeforeLoad')
		{
			return $this->_runPHP($params, $formModel);
		}
		return true;
	}

	/**
	 * Process the plugin, called when form is submitted
	 *
	 * @param   object  $params      plugin params
	 * @param   object  &$formModel  form model
	 *
	 * @return  bool
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
	 * Run plugins php code/script
	 *
	 * @param   object  $params      plugin params
	 * @param   object  &$formModel  form model
	 *
	 * @return bool false if error running php code
	 */

	private function _runPHP($params, &$formModel)
	{
		/**
		 * if you want to modify the submitted form data
		 * $formModel->updateFormData('tablename___elementname', $newvalue);
		 */

		/*
		 *  $$$ rob this is poor when submitting the form the data is stored in _formData, when editing its stored in _data -
		 *  as this method can run on render or on submit we have to do a little check to see which one we should use.
		 *  really we should use the same form property to store the data regardless of form state
		 */
		$this->html = array();
		if (!empty($formModel->_formData))
		{
			$this->html = $formModel->_formData;
		}
		elseif (!empty($formModel->_data))
		{
			$this->html = $formModel->_data;
		}
		$w = new FabrikWorker;
		if ($params->get('form_php_file') == -1)
		{
			$code = $w->parseMessageForPlaceHolder($params->get('curl_code', ''), $this->html, true, true);
			return eval($code);
		}
		else
		{
			// $$$ hugh - give them some way of getting at form data
			// (I'm never sure if $_REQUEST is 'safe', i.e. if it has post-validation data)
			global $fabrikFormData, $fabrikFormDataWithTableName;

			// For some reason, = wasn't working??
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
				// For these types of scripts any out put you want to inject into the form should be echo'd out
				// @TODO - shouldn't we apply this logic above as well (direct eval)?
				ob_start();
				require $php_file;
				$output = ob_get_contents();
				ob_end_clean();
				return $output;
			}
			else
			{
				$php_result = require $php_file;
			}
			if ($php_result === false)
			{
				return false;
			}
			// $$$ hugh - added this to make it more convenient for defining functions to call in form PHP.
			// So you can have a 'script file' that defines function(s), AND a direct eval that calls them,
			// without having to stick a require() in the eval code.
			$code = $w->parseMessageForPlaceHolder($params->get('curl_code', ''), $this->html, true, true);
			if (!empty($code))
			{
				return eval($code);
			}
		}
		return true;
	}

}
