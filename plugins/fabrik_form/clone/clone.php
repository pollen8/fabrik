<?php
/**
 * Form email plugin
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

class plgFabrik_FormClone extends plgFabrik_Form {

	/**
	 * process the plugin, called when form is submitted
	 *
	 * @param object $params
	 * @param object form model
	 * @returns bol
	 */

	function onAfterProcess($params, &$formModel)
	{
		return $this->_process($params, $formModel);
	}

	private function _process(&$params, &$formModel)
	{
		$clone_times_field_id = $params->get('clone_times_field', '');
		$clone_batchid_field_id = $params->get('clone_batchid_field', '');
		if ($clone_times_field_id != '') {
			$elementModel = FabrikWorker::getPluginManager()->getElementPlugin($clone_times_field_id);
			$element = $elementModel->getElement(true);
			if ($clone_batchid_field_id != '') {
				$elementModel = FabrikWorker::getPluginManager()->getElementPlugin($clone_batchid_field_id);
				$id_element = $id_elementModel->getElement(true);
				$formModel->_formData[$id_element->name] = $formModel->_fullFormData['rowid'];
				$formModel->_formData[$id_element->name . '_raw'] = $formModel->_fullFormData['rowid'];
				$listModel = $formModel->getlistModel();
				$listModel->_oForm = $formModel;
				$primaryKey = FabrikString::shortColName($listModel->getTable()->db_primary_key);
				$formModel->_formData[$primaryKey] = $formModel->_fullFormData['rowid'];
				$formModel->_formData[$primaryKey . '_raw'] = $formModel->_fullFormData['rowid'];
				$listModel->storeRow( $formModel->_formData, $formModel->_fullFormData['rowid']);
			}
			//$clone_times_field = $elementModel->getFullName(false, true, false);
			$clone_times = $formModel->_formData[$element->name];
			if (is_numeric($clone_times)) {
				$clone_times = (int)$clone_times;
				$formModel->_formData['Copy'] = 1;
				for ($x=1; $x < $clone_times; $x++) {
					$formModel->processToDB();
				}
				return true;
			}
		}
		JError::raiseNotice(JText::_('CLONEERR'), "Couldn't find a valid number of times to clone!");
		return true;
	}

}
?>