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

class fabrikViewCsv extends JView
{

	function display($tpl = null)
	{
		FabrikHelperHTML::framework();
		$this->listid = JRequest::getVar('listid', 0);
		$listModel = JModel::getInstance('List', 'FabrikFEModel');
		$listModel->setId($this->listid);
		$this->setModel($listModel, true);
		$this->table = $listModel->getTable();
		$data = array();
		$this->getManagementJS($data);
		$this->assign('id', $this->get('id'));
		$this->form = $this->get('Form');
		if (!$listModel->canCSVExport()) {
			JError::raiseError(400, 'Naughty naughty!');
			jexit;
		}
		parent::display($tpl);
	}
	
	protected function getManagementJS($data = array())
	{
		$app = JFactory::getApplication();
		$model = $this->getModel();
		$listid = $model->getId();
	
		FabrikHelperHTML::script('media/com_fabrik/js/list.js', true);

		$script = '';
	
		$opts = new stdClass();
		$opts->admin = $app->isAdmin();
		$opts->form = 'listform_' . $listid;
		$opts->headings = $model->_jsonHeadings();

		$listParams = $model->getParams();
		$opts->csvChoose = (bool)$listParams->get('csv_frontend_selection');
		$csvOpts = new stdClass();
		$csvOpts->excel = (int)$listParams->get('csv_format');
		$csvOpts->inctabledata = (int)$listParams->get('csv_include_data');
		$csvOpts->incraw = (int)$listParams->get('csv_include_raw_data');
		$csvOpts->inccalcs = (int)$listParams->get('csv_include_calculations');
		$opts->csvOpts = $csvOpts;
	
		$opts->csvFields = $this->get('CsvFields');
		$csvOpts->incfilters = 0;
		
		$opts->view = 'csv';
		
		//$$$rob if you are loading a table in a window from a form db join select record option
		// then we want to know the id of the window so we can set its showSpinner() method
		$opts->winid = JRequest::getVar('winid', '');
		$opts = json_encode($opts);
	
		JText::script('COM_FABRIK_CSV_COMPLETE');
		JText::script('COM_FABRIK_CSV_DOWNLOAD_HERE');
		JText::script('COM_FABRIK_CONFIRM_DELETE');
		JText::script('COM_FABRIK_CSV_DOWNLOADING');
		JText::script('COM_FABRIK_FILE_TYPE');
		
		JText::script('COM_FABRIK_LOADING');
		JText::script('COM_FABRIK_RECORDS');
		JText::script('COM_FABRIK_SAVING_TO');

	
		$script .= "\n" . "var list = new FbList($listid,";
		$script .= $opts;
		$script .= "\n" . ");";
		$script .= "\n" . "Fabrik.addBlock('list_'.$listid, list);";
	
	 	$script = "
			head.ready(function() {
		$script
			})"; 
		FabrikHelperHTML::addScriptDeclaration($script);
	}
}
?>