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


	protected $_buttonPrefix = 'php';

	function button()
	{
		return "run php";
	}
	
	protected function buttonLabel()
	{
		return $this->getParams()->get('table_php_button_label', parent::buttonLabel());
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
		$msg = $params->get('table_php_msg', JText::_('PLG_LIST_PHP_CODE_RUN'));
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
		$this->jsInstance = "new FbListPHP($opts)";
		return true;
	}

}
?>