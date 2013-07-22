<?php
/**
 * PHP Validation Rule
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.validationrule.php
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/validation_rule.php';

/**
 * PHP Validation Rule
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.validationrule.php
 * @since       3.0
 */

class PlgFabrik_ValidationrulePhp extends PlgFabrik_Validationrule
{

	/**
	 * Plugin name
	 *
	 * @var string
	 */
	protected $pluginName = 'php';

	/**
	 * Validate the elements data against the rule
	 *
	 * @param   string  $data           To check
	 * @param   int     $repeatCounter  Repeat group counter
	 *
	 * @return  bool  true if validation passes, false if fails
	 */

	public function validate($data, $repeatCounter)
	{
		// For multiselect elements
		if (is_array($data))
		{
			$data = implode('', $data);
		}
		$params = $this->getParams();
		$domatch = $params->get('php-match');
		if ($domatch)
		{
			return $this->_eval($data);
		}
		return true;
	}

	/**
	 * Checks if the validation should replace the submitted element data
	 * if so then the replaced data is returned otherwise original data returned
	 *
	 * @param   string  $data           Original data
	 * @param   int     $repeatCounter  Repeat group counter
	 *
	 * @return  string	original or replaced data
	 */

	public function replace($data, $repeatCounter)
	{
		$params = $this->getParams();
		$domatch = $params->get('php-match');
		if (!$domatch)
		{
			return $this->_eval($data);
		}
		return $data;
	}

	/**
	 * Run eval
	 *
	 * @param   string  $data  Original data
	 *
	 * @return  string	Evaluated PHP function
	 */

	private function _eval($data)
	{
		$params = $this->getParams();
		$elementModel = $this->elementModel;
		$formModel = $elementModel->getFormModel();
		$formData = $formModel->formData;
		$w = new FabrikWorker;
		$phpCode = $params->get('php-code');
		$phpCode = $w->parseMessageForPlaceHolder($phpCode, $formData, true, true);
		$retval = @eval($phpCode);
		FabrikWorker::logEval($retval, 'Caught exception on php validation of ' . $elementModel->getFullName(true, false) . ': %s');
		return $retval;
	}
}
