<?php

/**
 * @package     Joomla
 * @subpackage	Fabik
 * @copyright	Copyright (C) 2005 - 2008 Pollen 8 Design Ltd. All rights reserved.
 * @license		GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class FabrikViewElement extends JViewLegacy
{

	var $id = null;
	var $isMambot = null;

	function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * display the template
	 *
* @param sting $tpl
	 */

	function display($tpl = null)
	{
		echo "form id = ".JRequest::getInt('formid');exit;
		$pluginManager = JModelLegacy::getInstance('Pluginmanager', 'FabrikFEModel');
		$ids = JRequest::getVar('plugin');
		foreach ($ids as $id) {
			$plugin = $pluginManager->getElementPlugin($id);
		}
/* 		$elementid = JRequest::getVar('elid');
		$pluginManager = JModelLegacy::getInstance('Pluginmanager', 'FabrikFEModel');
		$className = JRequest::getVar('plugin');
		$plugin = $pluginManager->getPlugIn($className, 'element');
		$plugin->setId($elementid);
		$plugin->inLineEdit(); */
	}

}
?>