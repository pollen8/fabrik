<?php

/**
 * @package		Joomla
 * @subpackage	Fabik
 * @copyright	Copyright (C) 2005 - 2008 Pollen 8 Design Ltd. All rights reserved.
 * @license		GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');
require_once('components/com_fabrik/views/form/view.base.php');

class fabrikViewForm extends FabrikViewFormBase {

	function display($tpl = null)
	{
		parent::display($tpl);
		$this->output();
	}
}
?>