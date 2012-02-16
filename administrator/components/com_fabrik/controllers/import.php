<?php
/**
 * @version
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/**
 * @package		Joomla
 * @subpackage	Fabrik
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once('fabcontrollerform.php');
/**
 * @package		Joomla
 * @subpackage	Fabrik
 */

class FabrikControllerImport extends FabControllerForm
{

	/**
	 * if new elements found in the CSV file and user decided to
	 * add them to the table then do it here
	 * @param object import model
	 * @param array existing headings
	 * @return unknown_type
	 */
	protected function addElements($model, $headings)
	{
		$user =& JFactory::getUser();
		$c = 0;
		$listModel = &$this->getModel('List', 'FabrikFEModel');
		$listModel->setId(JRequest::getInt('list_id'));
		$listModel->getTable();
		$formModel 	= $listModel->getFormModel();
		$groupId = current(array_keys($formModel->getGroupsHiarachy()));
		$plugins = JRequest::getVar('plugin');
		$pluginManager = FabrikWorker::getPluginManager();
		$elementModel = $pluginManager->getPlugIn('field', 'element');
		$element = FabTable::getInstance('Element', 'FabrikTable');
		$elementsCreated = 0;
		$newElements = JRequest::getVar('createElements', array());
		$dataRemoved = false;
		
		// @TODO use actual element plugin getDefaultProperties()
		foreach ($newElements as $elname => $add) {
			if ($add) {
				$element->id = 0;
				$element->name = JFilterInput::clean($elname, 'CMD');
				$element->label = strtolower($elname);
				$element->plugin = $plugins[$c];
				$element->group_id 			= $groupId;
				$element->eval 					= 0;
				$element->published 		= 1;
				$element->width 				= 255;
				$element->created 			= date('Y-m-d H:i:s');
				$element->created_by 		= $user->get('id');
				$element->created_by_alias 	= $user->get('username');
				$element->checked_out 	= 0;
				$element->show_in_list_summary = 1;
				$element->ordering 	= 0;
				$element->params = $elementModel->getDefaultAttribs();
				$headings[] = $element->name;

				$element->store();
				$where = " group_id = '" . $element->group_id . "'";
				$element->move(1, $where);
				//$elementModel->addToDBTable();
				$elementsCreated ++;
			} else {
				//need to remove none selected element's (that dont already appear in the table structure
				// data from the csv data
				$session = JFactory::getSession();
				$allHeadings = $session->get('com_fabrik.csvheadings');
				$index = array_search($elname, $allHeadings);
				if ($index !== false) {
					$dataRemoved = true;
					foreach ($model->data as &$d) {
						unset($d[$index]);
					}
				}
			}
			$c ++;
		}
		
		$listModel->ammendTable(); //3.0 testing?
		if ($dataRemoved) {
			//reindex data array
			foreach ($model->data as $k => $d) {
				$model->data[$k] = array_reverse(array_reverse($d));
			}
		}
		return $headings;
	}

	/**
	 * cancel import
	 * @return null
	 */

	function cancel()
	{
		$this->setRedirect('index.php?option=com_fabrik&view=lists');
	}

	/**
	 * make or update the table from the CSV file
	 * @return null
	 */

	function makeTableFromCSV()
	{
		//called when creating new elements from csv import into existing list
		$session = JFactory::getSession();
		$model = $this->getModel('Importcsv', 'FabrikFEModel');
		$model->import();
		
		if (JRequest::getInt('fabrik_list') == 0) {
			
			$plugins = JRequest::getVar('plugin');
			$createElements = JRequest::getVar('createElements', array());
			$dataRemoved = false;
			$newElements = array();
			$c = 0;
			$dbname = JRequest::getVar('db_table_name');
			$model->matchedHeadings = array();
			foreach ($createElements as $elname => $add) {
				if ($add) {
					$name = JFilterInput::clean($elname, 'CMD');
					$plugin = $plugins[$c];
					$newElements[$name] = $plugin;
					$model->matchedHeadings[$dbname.'.'.$name] = $name;
				}
				$c ++;
			}
			//stop id and date_time being added to the table and instead use $newElements
			JRequest::setVar('defaultfields', $newElements);
			
			//$model->matchedHeadings = array_keys($newElements);
			//create db
			$listModel = $this->getModel('list', 'FabrikModel');
			$data = array(
			'id' => 0,
			'_database_name' => $dbname,
			'connection_id' => JRequest::getInt('connection_id'),
			'access' => 0,
			'rows_per_page' => 10,
			'template' => 'default',
			'published' => 1,
			'access' => 1,
			'label' => JRequest::getVar('label'),
			'jform' => array('id' => 0, '_database_name' => $dbname, 'db_table_name' =>  '')
			);
			JRequest::setVar('jform', $data['jform']);
			
			if (!$listModel->save($data)) {
				return $listModel->getError();
			}
			$model->listModel = null;
			JRequest::setVar('listid', $listModel->getItem()->id);
		} else {
			//$model->data = $session->get('com_fabrik.csvdata');
			$headings = $session->get('com_fabrik.matchedHeadings');
			$model->matchedHeadings = $this->addElements($model, $headings);
			JRequest::setVar('listid', JRequest::getInt('fabrik_list'));
		}
		
		$msg = $model->insertData();
		$this->setRedirect('index.php?option=com_fabrik&view=lists', $msg);
	}

	/**
	 * display the import CSV file form
	 */

	function display()
	{
		
		$viewType	= JFactory::getDocument()->getType();
		$view = & $this->getView('import', $viewType);
		$this->getModel('Importcsv', 'FabrikFEModel')->clearSession();
		$model = $this->getModel();
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}
		$view->display();
	}

	/**
	 * perform the file upload and set the session state
	 * Unlike front end import if there are unmatched heading we take the user to
	 * a form asking if they want to import those new headings (creating new elements for them)
	 * @return null
	 */

	public function doimport()
	{
		$model = $this->getModel('Importcsv', 'FabrikFEModel');
		if (!$model->checkUpload()) {
			$this->display();
			return;
		}
		$id = $model->getListModel()->getId();
		$document = JFactory::getDocument();
		$viewName	= 'import';
		$viewType	= $document->getType();
		// Set the default view name from the Request
		$view = $this->getView($viewName, $viewType);
		
		$model->import();
		if (!empty($model->newHeadings)) {
			$view->setModel($model, true);
			$view->setModel($this->getModel('pluginmanager', 'FabrikFEModel'));
			$view->chooseElementTypes();
		} else {
			$model->import();
			JRequest::setVar('fabrik_list', $id);
			$msg = $model->makeTableFromCSV();
			$model->removeCSVFile();
			$this->setRedirect('index.php?option=com_fabrik&task=list.view&cid='.$id, $msg);
		}
	}
}
?>