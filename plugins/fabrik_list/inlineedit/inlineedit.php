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

class plgFabrik_ListInlineedit extends plgFabrik_List {

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

	public function loadJavascriptClass_result()
	{
		$src = parent::loadJavascriptClass_result();
		return array($src, 'media/com_fabrik/js/element.js');
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
		$listModel = JModel::getInstance('list', 'FabrikFEModel');
		$listModel->setId(JRequest::getVar('listid'));

		$elements = $model->getElements('safecolname');
		
		$pels = $params->get('inline_editable_elements');
		$use = json_decode($pels);
		if (!is_object($use)) {
			$aEls = trim($pels) == '' ? array() : explode(",", $pels);
			$use = new stdClass();
			foreach ($aEls as $e) {
				$use->$e = array($e);
			}
		}
		$els = array();
		$srcs = array();

		$test = (array)$use;
		if (!empty($test)) {
			foreach ($use as $key => $fields) {
				$trigger = $elements[$key];
				$els[$key] = new stdClass();
				$els[$key]->elid = $trigger->_id;
				$els[$key]->plugins = array();
				foreach ($fields as $field) {
					$val = $elements[$field];
				//load in all element js classes
					$val->formJavascriptClass($srcs);
					$els[$key]->plugins[$field] = $val->getElement()->id;
				}
			}
		} else {
			foreach ($elements as $key => $val) {
				$key = FabrikString::safeColNameToArrayKey($key);
				
				$els[$key] = new stdClass();
				$els[$key]->elid = $val->_id;
				$els[$key]->plugins = array();
				$els[$key]->plugins[$key] = $val->getElement()->id;
				//load in all element js classes
				$val->formJavascriptClass($srcs);
				
			}
		}
		FabrikHelperHTML::script($srcs);
		$opts = $this->getElementJSOptions($model);
		$opts->elements = $els;
		$opts->formid = $model->getFormModel()->getId();
		$opts->focusClass = 'focusClass';
		$opts->editEvent = $params->get('inline_edit_event', 'dblclick');
		$opts->tabSave = $params->get('inline_tab_save', false);
		$opts->showCancel = $params->get('inline_show_cancel', true);
		$opts->showSave = (bool)$params->get('inline_show_save', true);
		$opts->loadFirst = (bool)$params->get('inline_load_first', false);
		$opts = json_encode($opts);
		$formid = 'list_'+$model->getFormModel()->getForm()->id;
		$this->jsInstance = "new FbListInlineEdit($opts)";
		return true;
	}

}
?>