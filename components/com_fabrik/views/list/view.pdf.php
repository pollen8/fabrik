<?php

/**
 * @package		Joomla
 * @subpackage	Fabik
 * @copyright	Copyright (C) 2005 - 2008 Pollen 8 Design Ltd. All rights reserved.
 * @license		GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class FabrikViewList extends JView{

	public $isMambot 	= null;

	protected function getManagementJS($data = array())
	{
		$app = JFactory::getApplication();
		$Itemid	= $app->getMenu('site')->getActive()->id;
		$model =& $this->getModel();
		$table =& $model->getTable();
		FabrikHelperHTML::slimbox();
		FabrikHelperHTML::mocha();
		FabrikHelperHTML::script('media/com_fabrik/js/list.js', true);

		$tmpl = JRequest::getVar('layout', $table->template);

		// check for a custom css file and include it if it exists
		$ab_css_file = JPATH_SITE.DS."components".DS."com_fabrik".DS."views".DS."list".DS."tmpl".DS.$tmpl.DS."template.css";
		if (file_exists($ab_css_file)) {
			JHTML::stylesheet('template.css', 'components/com_fabrik/views/table/tmpl/'.$tmpl . '/');
		}

		// check for a custom js file and include it if it exists
		$aJsPath = JPATH_SITE.DS."components".DS."com_fabrik".DS."views".DS."list".DS."tmpl".DS.$tmpl.DS."javascript.js";
		if (file_exists($aJsPath)) {
			FabrikHelperHTML::script('components/com_fabrik/views/table/tmpl/'.$tmpl . '/javascript.js', true);
		}

		// temporarily set data to load requierd info for js templates

		$origRows 	= $this->rows;
		$this->rows = array(array());

		$tmpItemid = (!isset($Itemid)) ?  0 : $Itemid;

		$this->_c = 0;
		$this->_row = new stdClass();

		$script = '';

		$opts 				= new stdClass();
		$opts->admin 		= $app->isAdmin();
		$opts->ajax 	= $model->isAjax();
		$opts->filterMethod = $this->filter_action;
		$opts->form 		= 'listform_' . $model->getId();
		$opts->headings 	= $model->_jsonHeadings();
		$opts->labels 		= $this->headings;
		$opts->primaryKey 	= $table->db_primary_key;
		$opts->data 		= $data;
		$opts->Itemid 		= $tmpItemid;
		$opts->formid 		= $model->_oForm->getId();
		$opts->canEdit 		= $model->canEdit() ? "1" : "0";
		$opts->canView 		= $model->canView() ? "1" : "0";
		$opts->page 		= JRoute::_('index.php');
		$opts 				= json_encode($opts);

		$lang = new stdClass();
		$lang->select_rows =  JText::_('COM_FABRIK_SELECT_ROWS_FOR_DELETION');
		$lang = json_encode($lang);

		//$inpackage = $model->_inPackage ? 1 : 0;

		$script .= "\n" . "var list = new FbList(".$model->getId().",";
		$script .= $opts.",".$lang;
		$script .= "\n" . ");";
		//$script .= "\n" . "list.addListenTo('form_".$model->_oForm->getId()."');";
		//$script .= "\n" . "list.addListenTo('list_".$model->getId()."');";
		$script .= "\n" . "Fabrik.addBlock('list_".$model->getId()."', list);";

		//add in plugin objects
		$params =& $model->getParams();
		$activePlugins = $params->get('plugin', array(), '_default', 'array');
		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikModel');
		$plugins =& $pluginManager->getPlugInGroup('list');

		FabrikHelperHTML::addScriptDeclaration($script);
		//reset data back to original settings
		$this->rows = $origRows;
	}

	/**
	 * display the template
	 *
	 * @param sting $tpl
	 */

	function display($tpl = null)
	{
		global $_PROFILER;
		$app = JFactory::getApplication();
		$Itemid	= $app->getMenu('site')->getActive()->id;
		// turn off deprecated warnings in 5.3 or greater,
		// or J!'s PDF lib throws warnings about set_magic_quotes_runtime()
		if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
			$current_level = error_reporting();
    		error_reporting($current_level & ~E_DEPRECATED);
		}
		require_once(COM_FABRIK_FRONTEND.DS.'views'.DS.'modifiers.php');
		$user 		= JFactory::getUser();
		$model		=& $this->getModel();

		$document = JFactory::getDocument();

		//this gets the component settings
		$usersConfig = JComponentHelper::getParams('com_fabrik');

		$table			=& $model->getTable();
		$model->render();

		$w = new FabrikWorker();
		if (!$this->isMambot) {
			$document->setTitle($w->parseMessageForPlaceHolder($table->label, $_REQUEST));
		}
		$document->setName($w->parseMessageForPlaceHolder($table->label, $_REQUEST));
		$data =& $model->getData();

		//add in some styling short cuts
		$c 		= 0;
		$form =& $model->getForm();
		$nav 	=& $model->getPagination();

		foreach ($data as $groupk => $group) {
			$last_pk = '';
			$last_i = 0;
			for ($i=0; $i<count($group); $i++) {
				$o = new stdClass();
				// $$$ rob moved merge wip code to FabrikModelTable::formatForJoins() - should contain fix for pagination
				$o->data = $data[$groupk][$i];
				$o->cursor = $i + $nav->limitstart;
				$o->total = $nav->total;
				$o->id = "list_".$table->id."_row_".@$o->data->__pk_val;
				$o->class = "fabrik_row oddRow".$c;
				$data[$groupk][$i] = $o;
				$c = 1-$c;
			}
		}
		$groups =& $form->getGroupsHiarachy();
		foreach ($groups as $groupModel) {
			$elementModels =& $groupModel->getPublishedElements();
			foreach ($elementModels as $elementModel) {
				$e =& $elementModel->getElement();
				$elementModel->setContext($groupModel, $form, $model);
				$elparams =& $elementModel->getParams();
				$col 	= $elementModel->getFullName(false, true, false);
				$col .= "_raw";
				$rowclass = $elparams->get('use_as_row_class');
				if ($rowclass == 1) {
					foreach ($data as $groupk => $group) {
						for ($i=0; $i<count($group); $i++) {
							$data[$groupk][$i]->class .= " ". preg_replace('/[^A-Z|a-z|0-9]/', '-', $data[$groupk][$i]->data->$col);
						}
					}
				}
			}
		}
		$this->rows =& $data;
		reset($this->rows);
		$firstRow = current($this->rows); //cant use numeric key '0' as group by uses groupd name as key
		$this->nodata = (empty($this->rows) || (count($this->rows) == 1 && empty($firstRow))) ? true : false;
		$this->tableStyle = $this->nodata ? 'display:none' : '';
		$this->emptyStyle = $this->nodata ? '' : 'display:none';
		$params =& $model->getParams();

		if (!$model->canPublish()) {
			echo JText::_('COM_FABRIK_LIST_NOT_PUBLISHED');
			return false;
		}

		if (!$model->canView()) {
			echo JText::_('JERROR_ALERTNOAUTHOR');
			return false;
		}

		$this->table 					= new stdClass();
		$this->table->label 	= $w->parseMessageForPlaceHolder($table->label, $_REQUEST);
		$this->table->intro 	= $w->parseMessageForPlaceHolder($table->introduction);
		$this->table->id			= $table->id;
		$this->group_by				= $table->group_by;
		$this->formid = 'listform_' . $table->id;
		$page = $model->isAjax() ? "index.php?format=raw" : "index.php?";
		$this->table->action 	=  $page . str_replace('&', '&amp;', JRequest::getVar('QUERY_STRING', 'index.php?option=com_fabrik', 'server'));

		if ($model->isAjax()) {
			$this->table->action .= '&format=raw';
			$this->table->action = str_replace("task=package", "task=viewTable", $this->table->action);
			//$this->table->action 	= JRoute::_($this->table->action);
		}
		$this->table->action 	= JRoute::_($this->table->action);

		$this->showCSV 				= $model->canCSVExport();
		$this->canGroupBy = $model->canGroupBy();
		$this->showCSVImport	= $model->canCSVImport();
		$this->nav 						= $params->get('show-table-nav', 1) ? $nav->getListFooter($model->getId(), $this->get('tmpl')) : '';
		$this->fabrik_userid 	= $user->get('id');
		$this->canDelete 			= $model->canDelete() ? true : false;
		$this->deleteButton 	= $model->canDelete() ?  "<input class='button' type='button' onclick=\"$jsdelete\" value='" . JText::_('COM_FABRIK_DELETE') . "' name='delete'/>" : '';

		$this->showPDF = false;
		$this->pdfLink = false;

		$this->emptyLink = $model->canEmpty() ? '#' : '';
		$this->csvImportLink = $this->showCSVImport ? JRoute::_("index.php?option=com_fabrik&view=import&filetype=csv&listid=" . $table->id) : '';
		$this->showAdd = $model->canAdd();
		if ($this->showAdd) {
			if ($params->get('show-table-add', 1)) {
				$this->assign('addRecordLink', $this->get('AddRecordLink'));
			}
			else {
				$this->showAdd = false;
			}
		}
		$this->showRSS = $params->get('rss', 0) == 0 ?  0 : 1;

		if ($this->showRSS) {
			$this->rssLink = $model->getRSSFeedLink();
			if ($this->rssLink != '') {
				$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
				if (method_exists($document, 'addHeadLink')) {
					$document->addHeadLink($this->rssLink, 'alternate', 'rel', $attribs);
				}
			}
		}
		list($this->headings, $groupHeadings, $this->headingClass, $this->cellClass) = $this->get('Headings');

		$this->filter_action = $model->getFilterAction();
		$modelFilters = $model->makeFilters('list_'. $model->getId());
		$this->assign('clearFliterLink', $this->get('clearButton'));
		JDEBUG ? $_PROFILER->mark('fabrik getfilters end') : null;
		$form->getGroupsHiarachy();
		$this->assign('showFilters', (count($this->filters) > 0 && $params->get('show-table-filters', 1)) && JRequest::getVar('showfilters', 1) == 1 ?  1 : 0);

		$this->assign('requiredFiltersFound', $this->get('RequiredFiltersFound'));
		$this->assign('emptyDataMessage', $this->get('EmptyDataMsg'));
		$this->calculations 	= $this->_getCalculations($this->headings);

		$this->assign('isGrouped', $table->group_by);
		$this->assign('colCount', count($this->headings));
		$this->assignRef('grouptemplates', $model->grouptemplates);
		$this->assignRef('params', $params);
		$this->assignRef('groupheadings', $groupHeadings);
		$this->_loadTemplateBottom();

		$this->getManagementJS( $this->rows);

		// get dropdown list of other tables for quick nav in admin
		$this->tablePicker = ($app->isAdmin()) ? FabrikHelperHTML::tableList($this->table->id) : '';

		$this->pluginButtons = $model->getPluginButtons();

		//force front end templates
		$this->_basePath = COM_FABRIK_FRONTEND . DS . 'views';

		$tmpl = $params->get('pdf_template');
		if ($tmpl == -1) {
			$tmpl = JRequest::getVar('layout', $table->template);
		}

		$this->addTemplatePath($this->_basePath.DS.$this->_name.DS.'tmpl'.DS.$tmpl);
		$this->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_fabrik'.DS.'list'.DS.$tmpl);
		//ensure we don't have an incorrect version of mootools loaded

		$this->fixForPDF();

		parent::display();
	}

	/**
	 * ensure vars are correct for pdf output
	 */

	protected function fixForPDF()
	{
		$this->pluginButtons = array();
		$this->nav = null;
		$this->emptyButton  = '';
		$this->assign('showFilters', false);
		$this->showCSV 				= false;
		$this->showCSVImport	= false;
		$this->canDelete 			= false;
		$this->deleteButton 	='';
		$this->showPDF = false;
		$this->showAdd = false;
		$this->showRSS = false;
	}

	/**
	 *
	 */

	protected function _getCalculations($aCols )
	{

		$aData = array();
		$found = false;
		$model = $this->getModel();
		foreach ($aCols as $key=>$val) {
			$calc = '';
			$res = '';
			$oCalcs = new stdClass();
			$oCalcs->grouped = array();

			if (array_key_exists($key, $model->_aRunCalculations['sums'])) {
				$found = true;
				$res = $model->_aRunCalculations['sums'][$key];
				$calc .= JText::_('COM_FABRIK_SUM') . ": " . $res . "<br />";
				$tmpKey = str_replace(".", "___", $key) . "_calc_sum";
				$oCalcs->$tmpKey = $res;
			}
			if (array_key_exists($key . '_obj', $model->_aRunCalculations['sums'])) {
				$found = true;
				$res = $model->_aRunCalculations['sums'][$key. '_obj'];
				foreach ($res as $k=>$v) {
					if ($k != 'calc') {
						@$oCalcs->grouped[$k] .= JText::_('COM_FABRIK_SUM') . ": " . $v->value . "<br />";
					}
				}
			}

			if (array_key_exists($key, $model->_aRunCalculations['avgs'])) {
				$found = true;
				$res = $model->_aRunCalculations['avgs'][$key];
				$calc .= JText::_('COM_FABRIK_AVERAGE') . ": " . $res . "<br />";
				$tmpKey = str_replace(".", "___", $key) . "_calc_average";
				$oCalcs->$tmpKey = $res;
			}

			if (array_key_exists($key . '_obj', $model->_aRunCalculations['avgs'])) {
				$found = true;
				$res = $model->_aRunCalculations['avgs'][$key. '_obj'];
				foreach ($res as $k=>$v) {
					if ($k != 'calc') {
						@$oCalcs->grouped[$k] .=  JText::_('COM_FABRIK_AVERAGE') . ": " . $v->value . "<br />";
					}
				}
			}

			if (array_key_exists($key. '_obj', $model->_aRunCalculations['medians'])) {
				$found = true;
				$res = $model->_aRunCalculations['medians'][$key. '_obj'];
				foreach ($res as $k=>$v) {
					if ($k != 'calc') {
						@$oCalcs->grouped[$k] .=  JText::_('COM_FABRIK_MEDIAN') . ": " . $v->value . "<br />";
					}
				}
			}

			if (array_key_exists($key, $model->_aRunCalculations['medians'])) {
				$found = true;
				$res = $model->_aRunCalculations['medians'][$key];
				$calc .= JText::_('COM_FABRIK_MEDIAN') . ": " . $res . "<br />";
				$tmpKey = str_replace(".", "___", $key) . "_calc_median";
				$oCalcs->$tmpKey = $res;
			}

			if (array_key_exists($key. '_obj', $model->_aRunCalculations['count'])) {
				$found = true;
				$res = $model->_aRunCalculations['count'][$key. '_obj'];
				foreach ($res as $k=>$v) {
					if ($k != 'calc') {
						@$oCalcs->grouped[$k] .=  JText::_('COM_FABRIK_COUNT') . ": " . $v->value . "<br />";
					}
				}
			}

			if (array_key_exists($key, $model->_aRunCalculations['count'])) {
				$res = $model->_aRunCalculations['count'][$key];
				$calc .= JText::_('COM_FABRIK_COUNT') . ": " . $res . "<br />";
				$tmpKey = str_replace(".", "___", $key) . "_calc_count";
				$oCalcs->$tmpKey = $res;
				$found = true;
			}
			$key = str_replace(".", "___", $key);
			$oCalcs->calc = $calc;
			$aData[$key] = $oCalcs;
		}
		$this->assign('hasCalculations', $found);
		return $aData;
	}

	/**
	 *
	 */

	protected function _loadTemplateBottom()
	{
		//no fieldds in pdfs!
		$this->hiddenFields = '';
		return;
	}

}
?>