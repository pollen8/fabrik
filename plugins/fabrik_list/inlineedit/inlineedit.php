<?php

/**
* Allows double-clicking in a cell to enable in-line editing
* @package Joomla
* @subpackage Fabrik
* @author Rob Clayburn
* @copyright (C) Pollen 8 design Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND.DS.'models'.DS.'plugin-list.php');
require_once(COM_FABRIK_FRONTEND.DS.'helpers'.DS.'html.php');

class plgFabrik_ListInlineedit extends plgFabrik_List {

	var $_counter = null;

	/**
	 * (non-PHPdoc)
	 * @see FabrikModelTablePlugin::getAclParam()
	 */

	function getAclParam()
	{
		return 'inline_access';
	}

	/**
	 * determine if the table plugin is a button and can be activated only when rows are selected
	 *
	 * @return bol
	 */

	function canSelectRows()
	{
		return false;
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
		FabrikHelperHTML::addStyleDeclaration('.focusClass{border:1px solid red !important;}');
		FabrikHelperHTML::script('media/com_fabrik/js/element.js');
		$listModel =& JModel::getInstance('list', 'FabrikFEModel');
		$listModel->setId(JRequest::getVar('listid'));

		$elements =& $model->getElements('filtername');
		$pels = $params->get('inline_editable_elements');
		$use = trim($pels) == '' ? array() : explode(",", $pels);
		$els = array();
		foreach ($elements as $key => $val) {
			$key = FabrikString::safeColNameToArrayKey($key);
			if (empty($use) || in_array($key, $use)) {
				$els[$key] = new stdClass();
				$els[$key]->elid = $val->_id;
				$els[$key]->plugin = $val->getElement()->plugin;
				//load in all element js classes
				$val->formJavascriptClass();
			}
		}
		$opts = new stdClass();
		$opts->elements = $els;
		$opts->listid = $model->getId();
		$opts->focusClass = 'focusClass';
		$opts->editEvent = $params->get('inline_edit_event', 'dblclick');
		$opts->tabSave = $params->get('inline_tab_save', false);
		$opts->showCancel = $params->get('inline_show_cancel', true);
		$opts->showSave = $params->get('inline_show_save', true);
		$opts->loadFirst = (bool)$params->get('inline_load_first', false);
		$opts = json_encode($opts);
		$formid = 'list_'+$model->getFormModel()->getForm()->id;
		$this->jsInstance = "new FbListInlineEdit($opts)";
		return true;
	}

}
?>