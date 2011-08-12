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
		$opts->trigger = str_replace('.', '___', $params->get('autofill_trigger'));
		$opts->formid = $formModel->getId();
		$opts->map = $params->get('autofill_map');
		$opts->cnn = $params->get('autofill_cnn');
		$opts->table = $params->get('autofill_table');
		$opts->editOrig = $params->get('autofill_edit_orig', 0) == 0 ? false : true;
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
		$params = $this->getParams();
		$cnn = (int)JRequest::getInt('cnn');
		$element 		= JRequest::getVar('observe');
		$value 			= JRequest::getVar('v');
		JRequest::setVar('resetfilters', 1);
		
		if ($cnn === 0 || $cnn == -1) { //no connection selected so query current forms' table data
			$formid 		= JRequest::getInt('formid');
			JRequest::setVar($element, $value, 'get');
			$model = JModel::getInstance('form', 'FabrikFEModel');
			$model->setId($formid);
			$listModel = $model->getlistModel();
		} else {
			$listModel = JModel::getInstance('list', 'FabrikFEModel');
			$listModel->setId(JRequest::getInt('table'));
			$pk = $listModel->getTable()->db_primary_key;
			JRequest::setVar($pk, $value, 'get');

		}
		$nav	=& $listModel->getPagination(1, 0, 1);
		$listModel->_outPutFormat = 'raw';
		$data = $listModel->getData();
		$data = $data[0];
		if (empty($data)) {
			echo  "{}";
		} else {
			$map = JRequest::getVar('map');
			$map = json_decode($map);
			if (!empty($map)) {
				$newdata = new stdClass();
				foreach($map as $from => $to) {
					$toraw = $to.'_raw';
					$fromraw = $from.'_raw';
					$newdata->$to = $data[0]->$from;
				if (strstr($newdata->$to, GROUPSPLITTER2)) {
						$newdata->$to = explode(GROUPSPLITTER2, $newdata->$to);
					}
					$newdata->$toraw = $data[0]->$fromraw;
				if (strstr($newdata->$toraw, GROUPSPLITTER2)) {
						$newdata->$toraw = explode(GROUPSPLITTER2, $newdata->$toraw);
					}
				}
			} else {
				$newdata = $data[0];
			}
			echo json_encode($data[0]);
		}
	}

}
?>