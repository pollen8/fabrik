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

	/**
	 * need to do this rather than on onLoad as otherwise in chrome form.js addevents is fired
	 * before autocomplete class ini'd so then the autocomplete class never sets itself up
	 * @param object $params
	 * @param object $formModel
	 */

	function onAfterJSLoad(&$params, &$formModel)
	{
		FabrikHelperHTML::script('plugins/fabrik_form/autofill/autofill.js');
		$opts = new stdClass();
		$opts->observe = str_replace('.', '___', $params->get('autofill_field_name'));
		$opts->trigger = str_replace('.', '___', $params->get('autofill_trigger'));
		$opts->formid = $formModel->getId();
		$opts->map = $params->get('autofill_map');
		$opts->cnn = $params->get('autofill_cnn');
		$opts->table = $params->get('autofill_table');
		if ($opts->table === '') {
			JError::raiseNotice(500, 'Autofill plugin - no list selected');
		}
		$opts->editOrig = $params->get('autofill_edit_orig', 0) == 0 ? false : true;
		$opts->confirm = (bool)$params->get('autofill_confirm', true);
		$opts->fillOnLoad = (bool)$params->get('autofill_onload', false);
		$opts = json_encode($opts);
		JText::script('PLG_FORM_AUTOFILL_DO_UPDATE');
		JText::script('PLG_FORM_AUTOFILL_SEARCHING');
		JText::script('PLG_FORM_AUTOFILL_NORECORDS_FOUND');
		FabrikHelperHTML::addScriptDeclaration("head.ready(function(){var autofill = new Autofill($opts);});");
	}

	/**
	 * called via ajax to get the first match record
	 * @return string json object of record data
	 */

	function onajax_getAutoFill()
	{
		$params = $this->getParams();
		$cnn = (int)JRequest::getInt('cnn');
		$element = JRequest::getVar('observe');
		$value = JRequest::getVar('v');
		JRequest::setVar('resetfilters', 1);
		if ($cnn === 0 || $cnn == -1) {
			//no connection selected so query current forms' table data
			$formid = JRequest::getInt('formid');
			JRequest::setVar($element, $value, 'get');
			$model = JModel::getInstance('form', 'FabrikFEModel');
			$model->setId($formid);
			$listModel = $model->getlistModel();
		} else {
			$listModel = JModel::getInstance('list', 'FabrikFEModel');
			$listModel->setId(JRequest::getInt('table'));
		}
		if ($value !== '') {
			// dont get the row if its empty
			$data = $listModel->getRow($value, true, true);
			if (!is_null($data)) {
				$data = array_shift($data);
			}
		}
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
					if (is_array($to)) {
						foreach ($to as $to2) {
							$to2_raw = $to2.'_raw';
							$newdata->$to2 = $data->$from;
							$newdata->$to2_raw = $data->$fromraw;
						}
					}
					else {
						$newdata->$to = $data->$from;
						$newdata->$toraw = $data->$fromraw;
					}
				}
			} else {
				$newdata = $data;
			}
			echo json_encode($newdata);
		}
	}

}
?>