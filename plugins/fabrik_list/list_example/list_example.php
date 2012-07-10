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

// Require the abstract plugin class
require_once(COM_FABRIK_FRONTEND . '/models/plugin-list.php');

class plgFabrik_ListList_Example extends plgFabrik_List {

	/**
	 * called when the active table filter array is loaded
	 */

	function onFiltersGot(&$params, &$model) {

	}

	/**
	 * called when the table HTML filters are loaded
	 *
	 */

	function onMakeFilters(&$params, &$model) {
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
	function onLoadData(&$params, &$oRequest)
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


	function canUse(&$model = null, $location = null, $event = null)
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
	 * return the javascript to create an instance of the class defined in formJavascriptClass
	 * @param object parameters
	 * @param list table model
	 * @param array [0] => string table's form id to contain plugin
	 * @return bool
	 */

	function onLoadJavascriptInstance($params, $model, $args)
	{
		parent::onLoadJavascriptInstance($params, $model, $args);
		$opts = $this->getElementJSOptions($model);
		$opts = json_encode($opts);
		return "new FbListExample('$formid', $opts)";
	}

}
?>