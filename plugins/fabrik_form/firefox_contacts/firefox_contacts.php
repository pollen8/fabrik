<?php
/**
 * Firefox contacts - enables http://mozillalabs.com/conceptseries/identity/contacts/
 * for your site - currently works only on element's named 'email'
 * @package     Joomla
 * @subpackage  Fabrik
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

class PlgFabrik_FormFirefox_contacts extends PlgFabrik_Form {

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
?>