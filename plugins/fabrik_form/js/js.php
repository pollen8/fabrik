<?php

/**
* Create a Joomla user from the forms data
* @package Joomla
* @subpackage Fabrik
* @author Rob Clayburn
* @copyright (C) Rob Clayburn
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND.DS.'models'.DS.'plugin-form.php');

class plgFabrik_FormJs extends plgFabrik_Form {

 	/**
 	 * process the plugin, called when form is submitted
 	 *
 	 * @param object $params
 	 * @param object form
 	 */

 	function onAfterJSLoad(&$params, &$formModel)
 	{
 		$script = $params->get('jsfile');
 		if ($script == '-1') {
			return;
		}
		$className = substr($script, 0, strlen($script) -3);
		$document = JFactory::getDocument();
		$form = $formModel->getForm();
		$container = $formModel->_editable ? 'form' : 'details';
		if (JRequest::getVar('tmpl') != 'component') {
			FabrikHelperHTML::script('plugins/fabrik_form/js/scripts/'.$script);
			FabrikHelperHTML::addScriptDeclaration("
			head.ready(function() {
			{$container}_{$form->id}.addPlugin(new $className({$container}_{$form->id}));
	 		});");
		} else {
			// included scripts in the head don't work in mocha window
			// read in the class and insert it into the body as an inline script
			$class = JFile::read(JPATH_BASE."/plugins/fabrik_form/js/scripts/$script");
			FabrikHelperHTML::addScriptDeclaration($class);
			FabrikHelperHTML::addScriptDeclaration("
				head.ready(function() {
			{$container}_{$form->id}.addPlugin(new $className({$container}_{$form->id}));
	 		});");
		}
	}
}
?>