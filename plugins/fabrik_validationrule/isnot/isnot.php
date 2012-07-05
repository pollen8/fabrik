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

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/validation_rule.php';

class PlgFabrik_ValidationruleIsNot extends PlgFabrik_Validationrule
{

	protected $pluginName = 'isnot';

	/** @var bool if true uses icon of same name as validation, otherwise uses png icon specified by $icon */
	protected $icon = 'notempty';

	/**
	 * (non-PHPdoc)
	 * @see PlgFabrik_Validationrule::validate()
	 */

	public function validate($data, &$elementModel, $pluginc, $repeatCounter)
	{
		if (is_array($data))
		{
			$data = implode('', $data);
		}
		$params = $this->getParams();
		$isnot = $params->get('isnot-isnot');
		$isnot = $isnot[$pluginc];
		$isnot = explode('|', $isnot);
		foreach ($isnot as $i)
		{
			if((string) $data === (string) $i)
			{
				return false;
			}
		}
		return true;
	}
}
?>