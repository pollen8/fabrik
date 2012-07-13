<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.firefox_contacts
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
 * Firefox contacts - enables http://mozillalabs.com/conceptseries/identity/contacts/
 * for your site - currently works only on element's named 'email'
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.firefox_contacts
 */

class plgFabrik_FormFirefox_contacts extends plgFabrik_Form
{

	/**
	 * process the plugin, called when form is loaded
	 *
* @param object $params
* @param object form model
	 * @returns bol
	 */

	function onLoad($params, &$formModel)
	{
		$document = JFactory::getDocument();
		$document->addScriptDeclaration("head.ready(function() {
		if(navigator.people) {
			navigator.people.find();
		}
	})");
	}

}
