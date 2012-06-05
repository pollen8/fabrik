<?php

/**
* Add an action button to the table to copy rows
* @package Joomla
* @subpackage Fabrik
* @author Rob Clayburn
* @copyright (C) Pollen 8 Design Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND . '/models/plugin-list.php');

class plgFabrik_ListCopy extends plgFabrik_List {

	protected $buttonPrefix = 'copy';

	function button()
	{
		return "copy records";
	}

	/**
	 * (non-PHPdoc)
	 * @see FabrikModelTablePlugin::getAclParam()
	 */

	function getAclParam()
	{
		return 'copytable_access';
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
	 * do the plugin action
	 * @param object parameters
	 * @param object table model
	 * @return string message
	 */

	function process(&$params, &$model)
	{
		$ids	= JRequest::getVar('ids', array(), 'method', 'array');
		$item = $model->getTable();
		$formModel = $model->getFormModel();
		$origPost = JRequest::get('post', 2);
		JRequest::set(array(), 'post');
		foreach ($ids as $id) {
			$formModel->_rowId = $id;
			$row = $formModel->getData();
			$row['Copy'] = '1';
			$row['fabrik_copy_from_table'] = 1;
			foreach ($row as $key=>$val) {
				JRequest::setVar($key, $val, 'post');
			}
			$formModel->setFormData();
			$formModel->_formDataWithTableName = $formModel->_formData;
			$formModel->processToDB();
		}

		JRequest::set(array(), 'post');
		JRequest::set($origPost, 'post', true);
		return true;
	}

	function process_result()
	{
		$ids	= JRequest::getVar('ids', array(), 'method', 'array');
		return JText::sprintf('PLG_LIST_ROWS_COPIED', count($ids));
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
		$opts = $this->getElementJSOptions($model);
		$opts = json_encode($opts);
		$this->jsInstance = "new FbListCopy($opts)";
		return true;
	}

}
?>