<?php
/**
* @package fabrikar
* @author Rob Clayburn
* @copyright (C) Rob Clayburn
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND . '/models/validation_rule.php');

class plgFabrik_ValidationruleRegex extends plgFabrik_Validationrule
{

	protected $pluginName = 'regex';

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
		$domatch = $params->get('regex-match');
		$domatch = $domatch[$pluginc];
		if ($domatch)
		{
			$matches = array();
			$v = (array) $params->get('regex-expression');
			$found = preg_match(JArrayHelper::getValue($v, $pluginc), $data, $matches);
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
			$replace = (array) $params->get('regex-replacestring');
			$return = preg_replace(JArrayHelper::getValue($v, $pluginc), JArrayHelper::getValue($replace, $pluginc), $data);
			return $return;
		}
		return $data;
 	}
}
?>