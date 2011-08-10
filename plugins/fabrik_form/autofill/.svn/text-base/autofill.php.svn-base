<?php

/**
 * Allows you to observe an element, and when it its blurred asks if you want to lookup
 * other records in the table to auto fill in the rest of the form with that records data
 *
 * Does not alter the record you search for but creates a new record
 *
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

class plgFabrik_FormAutofill extends plgFabrik_Form {

	var $_counter = null;

	//
	function onLoad(&$params, &$formModel)
	{
		FabrikHelperHTML::script('plugins/fabrik_form/autofill/autofill.js');
		$opts = new stdClass();
		$opts->observe = str_replace('.', '___', $params->get('autofill_field_name'));
		$opts->formid = $formModel->get('id');
		$opts = json_encode($opts);
		JText::script('PLG_FORM_AUTOFILL_DO_UPDATE');
		JText::script('PLG_FORM_AUTOFILL_SEARCHING');
		JText::script('PLG_FORM_AUTOFILL_NORECORDS_FOUND');
		FabrikHelperHTML::addScriptDeclaration("var autofill = new Autofill($opts);");
	}

	/**
	 * called via ajax to get the first match record
	 * @return string json object of record data
	 */
	function ajax_getAutoFill()
	{
		$formid 		= JRequest::getInt('formid');
		$element 		= JRequest::getVar('observe');
		$value 			= JRequest::getVar('v');
		JRequest::setVar('resetfilters', 1);
		JRequest::setVar($element, $value, 'get');
		$model 			=& JModel::getInstance('form', 'FabrikModel');
		$model->setId($formid);
		$listModel =& $model->getlistModel();
		$nav	=& $listModel->getPagination(1, 0, 1);
		$listModel->_outPutFormat = 'raw';
		$data = $listModel->getData();
		$data = $data[0];
		if (empty( $data)) {
			echo  "{}";
		} else {
			echo json_encode($data[0]);
		}
	}

}
?>