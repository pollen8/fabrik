<?php
/**
 * Fabrik Import Controller
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Pollen 8 Design Ltd. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

require_once(COM_FABRIK_FRONTEND.DS.'helpers'.DS.'params.php');
require_once(COM_FABRIK_FRONTEND.DS.'helpers'.DS.'string.php');
require_once(COM_FABRIK_FRONTEND.DS.'helpers'.DS.'html.php');


class FabrikControllerImport extends JController
{
	/**
	 * Display the view
	 */

	function display()
	{
		$this->listid = JRequest::getVar('listid', 0);
		$listModel =& $this->getModel('list', 'FabrikFEModel');
		$listModel->setId($this->listid);
		$this->table =& $listModel->getTable();
		$document = JFactory::getDocument();
		$viewName	= JRequest::getVar('view', 'form', 'default', 'cmd');
		$viewType	= $document->getType();
		// Set the default view name from the Request
		$view = &$this->getView($viewName, $viewType);
		$model = &$this->getModel('Importcsv', 'FabrikFEModel');
		$view->setModel($model, true);
		$view->display();
	}

	function doimport()
	{
		$model = &$this->getModel('Importcsv', 'FabrikFEModel');
		if (!$model->import()){
			$this->display();
			return;
		}
		$id = $model->getListModel()->getId();

		$document = JFactory::getDocument();
		$viewName = JRequest::getVar('view', 'form', 'default', 'cmd');
		$viewType = $document->getType();
		// Set the default view name from the Request
		$view = &$this->getView($viewName, $viewType);
		$Itemid = JRequest::getInt('Itemid');
		if (!empty($model->newHeadings)) {
			//as opposed to admin you can't alter table structure with a CSV import
			//from the front end
			JError::raiseNotice(500, $model->_makeError());
			$this->setRedirect("index.php?option=com_fabrik&view=import&fietype=csv&listid=".$id."&Itemid=".$Itemid);
		} else {
			JRequest::setVar('fabrik_list', $id);
			$msg = $model->makeTableFromCSV();
			$this->setRedirect('index.php?option=com_fabrik&view=list&listid='.$id."&resetfilters=1&Itemid=".$Itemid, $msg);
		}
	}

}
?>