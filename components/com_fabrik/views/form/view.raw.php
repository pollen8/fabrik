<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class fabrikViewForm extends JView
{

	var $_id 			= null;

	function setId($id)
	{
		$this->_id = $id;
	}

	function display($tpl = null)
	{
		$app 			= JFactory::getApplication();
		$w = new FabrikWorker();
		$config		= JFactory::getConfig();
		$model		=& $this->getModel('form', 'FabrikFEModel');
		$document = JFactory::getDocument();

		//Get the active menu item
		$usersConfig = JComponentHelper::getParams('com_fabrik');

		if (!isset($this->_id)) {
			$model->setId($usersConfig->get('formid', JRequest::getInt('formid')));
		} else {
			//when in a package the id is set from the package view
			$model->setId($this->_id);
		}

		$form 	=& $model->getForm();
		$model->render();

		$listModel = $model->_table;
		$table = is_object($listModel) ? $listModel->getTable() : null;
		if (!$model->canPublish()) {
			if (!$app->isAdmin()) {
				echo JText::_('COM_FABRIK_FORM_NOT_PUBLISHED');
				return false;
			}
		}

		$access = $model->checkAccessFromListSettings();
		if ($access == 0) {
			return JError::raiseWarning(500, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		if (is_object($listModel)) {
			$joins = $listModel->getJoins();
			$model->getJoinGroupIds($joins);
		}

		$params = $model->getParams();
		$params->def('icons', $app->getCfg('icons'));
		$pop = (JRequest::getVar('tmpl') == 'component') ? 1 : 0;
		$params->set('popup', $pop);

		$view = JRequest::getVar('view', 'form');
		if ($view == 'details') {
			$model->_editable = false;
		}

		$groups = $model->getGroupsHiarachy();
		$gkeys = array_keys($groups);
		$JSONarray = array();
		$JSONHtml = array();

		for ($i = 0; $i < count($gkeys); $i ++) {
			$groupModel = $groups[$gkeys[$i]];
			$groupTable 	=& $groupModel->getGroup();
			$group 				= new stdClass();
			$groupParams 	=& $groupModel->getParams();
			$aElements 		= array();
			//check if group is acutally a table join

			$repeatGroup = 1;
			$foreignKey = null;

			if ($groupModel->canRepeat()) {
				if ($groupModel->isJoin()) {

					$joinModel = $groupModel->getJoinModel();
					$joinTable = $joinModel->getJoin();

					$foreignKey  = '';
					if (is_object($joinTable)) {
						$foreignKey = $joinTable->table_join_key;
						//need to duplicate this perhaps per the number of times
						//that a repeat group occurs in the default data?
						if (isset($model->_data['join']) && array_key_exists($joinTable->id, $model->_data['join'])) {
							$elementModels = $groupModel->getPublishedElements();
							reset($elementModels);
							$tmpElement = current($elementModels);
							$smallerElHTMLName = $tmpElement->getFullName(false, true, false);
							$repeatGroup = count($model->_data['join'][$joinTable->id][$smallerElHTMLName]);
						} else {
							//$$$ rob test!!!
							if (!$groupParams->get('repeat_group_show_first')) {
								continue;
							}
						}
					}
				} else {
					// repeat groups which arent joins
					$elementModels = $groupModel->getPublishedElements();
					foreach ($elementModels as $tmpElement) {
						$smallerElHTMLName = $tmpElement->getFullName(false, true, false);
						if (array_key_exists($smallerElHTMLName."_raw", $model->_data)) {
							$d = $model->_data[$smallerElHTMLName."_raw"];
						} else {
							$d = @$model->_data[$smallerElHTMLName];
						}
						/*if (is_string($d) && strstr($d, GROUPSPLITTER)) {
							$d = explode(GROUPSPLITTER, $d);
						}*/
						$d = json_decode($d, true);
						$c = count($d);
						if ($c > $repeatGroup) { $repeatGroup = $c;}
					}
				}
			}

			$groupModel->_repeatTotal = $repeatGroup;

			$aSubGroups = array();
			for ($c = 0; $c < $repeatGroup; $c++) {
				$aSubGroupElements = array();
				$elCount = 0;
				$elementModels = $groupModel->getPublishedElements();
				foreach ($elementModels as $elementModel) {
					if (!$model->_editable) {
						// $$$ rob 22/03/2011 changes element keys by appending "_id" to the end, means that
						// db join add append data doesn't work if for example the popup form is set to allow adding,
						// but not editing records
						//$elementModel->_inDetailedView = true;
						$elementModel->_editable = false;
					}

					//force reload?
					$elementModel->_HTMLids = null;
					$elementHTMLId 	= $elementModel->getHTMLId($c);
					if (!$model->_editable) {
						$JSONarray[$elementHTMLId] = $elementModel->getROValue($model->_data, $c);
					}else{
						$JSONarray[$elementHTMLId] = $elementModel->getValue($model->_data, $c);
					}
					//test for paginate plugin
					if (!$model->_editable) {
						$elementModel->_HTMLids = null;
						$elementModel->_inDetailedView = true;
					}
					$JSONHtml[$elementHTMLId] = htmlentities($elementModel->render($model->_data, $c), ENT_QUOTES, 'UTF-8');
				}
			}
		}
		$data = array("id"=>$model->getId(), 'model'=>'table', "errors"=> $model->_arErrors, "data" => $JSONarray, 'html'=>$JSONHtml, 'post'=>$_REQUEST);
		echo json_encode($data);
	}

}
?>