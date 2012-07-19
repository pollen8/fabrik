<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
* @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class fabrikViewImport extends JViewLegacy
{

	function display($tpl = null)
	{
		FabrikHelperHTML::framework();
		$this->listid = JRequest::getVar('listid', 0);
		$listModel = JModelLegacy::getInstance('List', 'FabrikFEModel');
		$listModel->setId($this->listid);
		$this->table = $listModel->getTable();
		$this->form = $this->get('Form');
		if (!$listModel->canCSVImport()) {
			JError::raiseError(400, 'Naughty naughty!');
			jexit;
		}
		parent::display($tpl);
	}
}
?>