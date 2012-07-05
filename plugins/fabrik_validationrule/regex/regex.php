<?php
/**
* @package fabrikar
* @author Rob Clayburn
* @copyright (C) Rob Clayburn
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/validation_rule.php';

class PlgFabrik_ValidationruleRegex extends PlgFabrik_Validationrule
{

	protected $pluginName = 'regex';

	/** @var bool if true uses icon of same name as validation, otherwise uses png icon specified by $icon */
	protected $icon = 'notempty';

	/**
	 * (non-PHPdoc)
	 * @see PlgFabrik_Validationrule::validate()
	 */

	public function validate($data, &$elementModel, $pluginc, $repeatCounter)
	{
		//for multiselect elements
		if (is_array($data))
		{
			$data = implode('', $data);
		}
		$params = $this->getParams();
		$domatch = $params->get('regex-match');
		$domatch = $domatch[$pluginc];
		if ($domatch)
		{
			$matches = array();
			$v = (array) $params->get('regex-expression');
			$v = JArrayHelper::getValue($v, $pluginc);
			$v = trim($v);
			$found = empty($v) ? true : preg_match($v, $data, $matches);
			return $found;
		}
		return true;
	}

 	function replace($data, &$element, $pluginc, $repeatCounter)
 	{
 		$params = $this->getParams();
		$domatch = (array) $params->get('regex-match');
		$domatch = JArrayHelper::getValue($domatch, $pluginc);
		if (!$domatch)
		{
	 		$v = (array) $params->get($this->pluginName . '-expression');
	 		$v = JArrayHelper::getValue($v, $pluginc);
	 		$v = trim($v);
			$replace = (array) $params->get('regex-replacestring');
			$return = empty($v) ? $data : preg_replace($v, JArrayHelper::getValue($replace, $pluginc), $data);
			return $return;
		}
		return $data;
 	}
}
?>