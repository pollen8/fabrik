<?php
/**
*
* @package fabrikar
* @author Rob Clayburn
* @copyright (C) Rob Clayburn
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND . '/models/validation_rule.php');

class plgFabrik_ValidationrulePhp extends plgFabrik_Validationrule
{

	protected $pluginName = 'php';

	/** @var bool if true uses icon of same name as validation, otherwise uses png icon specified by $icon */
	protected $icon = 'notempty';

	/**
	 * (non-PHPdoc)
	 * @see plgFabrik_Validationrule::validate()
	 */

	public function validate($data, &$elementModel, $pluginc, $repeatCounter)
	{
		//for multiselect elements
		if (is_array($data))
		{
			$data = implode('', $data);
		}
		$params = $this->getParams();
		$domatch = $params->get('php-match');
		$domatch = $domatch[$pluginc];
		if ($domatch)
		{
			$formModel = $elementModel->getFormModel();
			$php_code = $params->get('php-code');
			$retval = eval($php_code[$pluginc]);
			return $retval;
		}
		return true;
	}

 	/**
 	 * checks if the validation should replace the submitted element data
 	 * if so then the replaced data is returned otherwise original data returned
 	 * @param	string	original data
 	 * @param	model	$element
 	 * @param	int		$c validation plugin counter
 	 * @return	string	original or replaced data
 	 */

 	function replace($data, &$element, $pluginc, $repeatCounter)
 	{
 		$params = $this->getParams();
		$domatch = $params->get('php-match');
		$domatch = $domatch[$pluginc];
		if (!$domatch)
		{
			$php_code = $params->get('php-code');
			return eval($php_code[$pluginc]);
		}
		return $data;
 	}
}
?>