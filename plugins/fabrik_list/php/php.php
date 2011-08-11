<?php

/**
* Add an action button to run PHP
* @package Joomla
* @subpackage Fabrik
* @author Rob Clayburn
* @copyright (C) Pollen 8 Design Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();


//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND.DS.'models'.DS.'plugin-list.php');
require_once(COM_FABRIK_FRONTEND.DS.'helpers'.DS.'html.php');

class plgFabrik_ListPhp extends plgFabrik_List {

	//@TODO this doesnt work if you have a module on the same page pointing to the same table

	var $_counter = null;

	var $_buttonPrefix = 'tablephp';


	function button()
	{
		return "run php";
	}

	function button_result()
	{
		$params =& $this->getParams();
		if ($this->canUse()) {
			$name = $this->_getButtonName();
			return "<a href=\"#\" class=\"$name listplugin\"/>".$params->get('table_php_button_label')."</a>";
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see FabrikModelTablePlugin::getAclParam()
	 */

	function getAclParam()
	{
		return 'table_php_access';
	}

	/**
	 * determine if the table plugin is a button and can be activated only when rows are selected
	 * @return bol
	 */

	function canSelectRows()
	{
		return true;
	}

	/**
	 * do the plug-in action
	 * @param object parameters
	 * @param object table model
	 * @param array custom options
	 */

	function process(&$params, &$model, $opts = array())
	{
		$file = JFilterInput::clean($params->get('table_php_file'), 'CMD');
		if ($file == -1 || $file == '') {
			$code = $params->get('table_php_code');
			@eval($code);
		} else {
			require_once(JPATH_ROOT.DS.'plugins'.DS.'fabrik_list'.DS.'scripts'.DS.$file);
		}
		return true;
	}

	function process_result()
	{
		$params =& $this->getParams();
		$msg = $params->get('table_php_msg', JText::_('Code run'));
		return $msg;
	}

	/**
	 * return the javascript to create an instance of the class defined in formJavascriptClass
	 * @param object parameters
	 * @param object table model
	 * @param array [0] => string table's form id to contain plugin
	 * @return bool
	 */

	function onLoadJavascriptInstance($params, $model, $args)
	{
		parent::onLoadJavascriptInstance($params, $model, $args);
		$opts = new stdClass();
		$opts->name = $this->_getButtonName();
		$opts->listid = $model->getId();
		$opts = json_encode($opts);
		$this->jsInstance = "new fbTableRunPHP($opts)";
		return true;
	}

}
?>