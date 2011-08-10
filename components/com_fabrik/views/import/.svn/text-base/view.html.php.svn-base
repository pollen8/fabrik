<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class fabrikViewImport extends JView
{

	function display($tpl = null)
	{
		FabrikHelperHTML::framework();
		$this->listid = JRequest::getVar('listid', 0);
		$listModel = JModel::getInstance('List', 'FabrikFEModel');
		$listModel->setId($this->listid);
		$this->table = $listModel->getTable();
		$this->form = $this->get('Form');
		parent::display($tpl);
	}
}
?>