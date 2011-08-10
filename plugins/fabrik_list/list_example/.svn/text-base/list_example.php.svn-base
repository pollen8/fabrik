<?php

/**
* Add an action button to the table to copy rows
* @package Joomla
* @subpackage Fabrik
* @author Rob Clayburn
* @copyright (C) Rob Clayburn
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND.DS.'models'.DS.'plugin-list.php');
require_once(COM_FABRIK_FRONTEND.DS.'helpers'.DS.'html.php');

class plgFabrik_ListExample extends plgFabrik_List {

	var $_counter = null;

	/**
	 * called when the active table filter array is loaded
	 *
	 */

	function onFiltersGot(&$params, &$model ) {

	}

	/**
	 * called when the table HTML filters are loaded
	 *
	 */

	function onMakeFilters(&$params, &$model ) {
	}



		/**
	 * do the plugin action
	 * @param object table model
	 * @return string message
	 */
	function process(&$model)
	{}

	/**
	 * run before the table loads its data
	 * @param $model
	 * @return unknown_type
	 */
	function onPreLoadData(&$model)
	{}

	/**
	 * run when the table loads its data(non-PHPdoc)
	 * @see components/com_fabrik/models/FabrikModelTablePlugin#onLoadData($params, $oRequest)
	 */
	function onLoadData(&$model)
	{}


	/**
	 * called when the model deletes rows
	 * @param object table $model
	 * @return false if fail
	 */
	function onDeleteRows(&$model)
	{

	}


	function button()
	{
		return "copy records";
	}

	function button_result($c )
	{
		return "<a href=\"#\" name=\"copy\" value=\"".JText::_('COPY') . "\" class=\"listplugin\"/>";
	}

	function canUse()
	{
		return true;
	}

	/**
	 * determine if the table plugin is a button and can be activated only when rows are selected
	 *
	 * @return bol
	 */

	function canSelectRows()
	{
		return true;
	}

	/**
	 * return tehe javascript to create an instance of the class defined in formJavascriptClass
	 * @param string table's form id to contain plugin
	 * @return string javascript to create instance. Instance name must be 'el'
	 */

	function onLoadJavascriptInstance($formid)
	{
		$opts = new stdClass();
		$opts->listid = $model->get('id');
		$opts = json_encode($opts);
		return "new fbTableExample('$formid', $opts)";
	}

}
?>