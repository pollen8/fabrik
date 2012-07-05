<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
* @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/validation_rule.php';

class PlgFabrik_ValidationruleIsalphanumeric extends PlgFabrik_Validationrule
{
	protected $pluginName = 'isalphanumeric';

	/** @var bool if true uses icon of same name as validation, otherwise uses png icon specified by $icon */
	protected $icon = 'notempty';
	
	/**
	 * (non-PHPdoc)
	 * @see PlgFabrik_Validationrule::validate()
	 */

	public function validate($data, &$elementModel, $pluginc, $repeatCounter)
	{
		//could be a dropdown with multivalues
		if (is_array($data)) {
			$data = implode('', $data);
		}
		if ($data == '') {
			return false;
		}
		preg_match('/[^\w\s]/', $data, $matches); //not a word character
		return empty($matches) ? true : false;
	}

}
?>