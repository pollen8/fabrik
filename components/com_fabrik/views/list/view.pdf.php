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
		$menuItem = $app->getMenu('site')->getActive();
		$Itemid	= is_object($menuItem) ? $menuItem->id : 0;
		$model = $this->getModel();
		$item = $model->getTable();
		$listid = $model->getId();
		$formModel = $model->getFormModel();
		$elementsNotInTable = $formModel->getElementsNotInTable();

		if ($model->requiresSlimbox()) {
			FabrikHelperHTML::slimbox();
		}

		FabrikHelperHTML::script('media/com_fabrik/js/list.js');
		$tmpl = $this->get('tmpl');
		$this->assign('tmpl', $tmpl);

		$this->get('ListCss');
		// check for a custom js file and include it if it exists
		$aJsPath = JPATH_SITE.DS."components".DS."com_fabrik".DS."views".DS."list".DS."tmpl".DS.$tmpl.DS."javascript.js";
		if (JFile::exists($aJsPath)) {
			FabrikHelperHTML::script('components/com_fabrik/views/list/tmpl/'.$tmpl.'/javascript.js');
		}

		$origRows = $this->rows;
		$this->rows = array(array());

		$tmpItemid = !isset($Itemid) ?  0 : $Itemid;

		$this->_c = 0;
		$this->_row = new stdClass();
		$script = '';

		$opts = new stdClass();
		$opts->admin = $app->isAdmin();
		$opts->ajax = (int)$model->isAjax();
		$opts->filterMethod = $this->filter_action;
		$opts->form = 'listform_' . $listid;
		$opts->headings = $model->_jsonHeadings();
		$labels = $this->headings;
		foreach ($labels as &$l) {
			$l = strip_tags($l);
		}
		$opts->labels 		= $labels;
		$opts->primaryKey = $item->db_primary_key;
		$opts->Itemid 		= $tmpItemid;
		$opts->formid 		= $model->getFormModel()->getId();
		$opts->canEdit 		= $model->canEdit() ? "1" : "0";
		$opts->canView 		= $model->canView() ? "1" : "0";
		$opts->page 			= JRoute::_('index.php');
		$opts->isGrouped = $this->isGrouped;
		$opts->formels		= $elementsNotInTable;
		$opts->actionMethod = $model->getParams()->get('actionMethod');
		$opts->data = $data;
		//if table data starts as empty then we need the html from the row
		// template otherwise we can't add a row to the table

		if ($model->isAjax()) {
			ob_start();
			$this->_row = new stdClass();
			$this->_row->id = '';
			$this->_row->class = 'fabrik_row';
			require(COM_FABRIK_FRONTEND.DS.'views'.DS.'list'.DS.'tmpl'.DS.$tmpl.DS.'default_row.php');
			$opts->rowtemplate = ob_get_contents();
			ob_end_clean();
		}
		//$$$rob if you are loading a table in a window from a form db join select record option
		// then we want to know the id of the window so we can set its showSpinner() method
		$opts->winid = JRequest::getVar('winid', '');
		$opts = json_encode($opts);

		JText::script('COM_FABRIK_PREV');
		JText::script('COM_FABRIK_SELECT_ROWS_FOR_DELETION');
		JText::script('JYES');
		JText::script('JNO');
		JText::script('COM_FABRIK_SELECT_COLUMNS_TO_EXPORT');
		JText::script('COM_FABRIK_INCLUDE_FILTERS');
		JText::script('COM_FABRIK_INCLUDE_DATA');
		JText::script('COM_FABRIK_INCLUDE_RAW_DATA');
		JText::script('COM_FABRIK_INLCUDE_CALCULATIONS');
		JText::script('COM_FABRIK_EXPORT');
		JText::script('COM_FABRIK_START');
		JText::script('COM_FABRIK_NEXT');
		JText::script('COM_FABRIK_END');
		JText::script('COM_FABRIK_PAGE');
		JText::script('COM_FABRIK_OF');
		JText::script('COM_FABRIK_LOADING');
		JText::script('COM_FABRIK_RECORDS');
		JText::script('COM_FABRIK_SAVING_TO');
		JText::script('COM_FABRIK_CONFIRM_DROP');
		JText::script('COM_FABRIK_CONFIRM_DELETE');
		JText::script('COM_FABRIK_NO_RECORDS');
		JText::script('COM_FABRIK_CSV_COMPLETE');
		JText::script('COM_FABRIK_CSV_DOWNLOAD_HERE');
		JText::script('COM_FABRIK_CONFIRM_DELETE');
		JText::script('COM_FABRIK_CSV_DOWNLOADING');
		JText::script('COM_FABRIK_FILE_TYPE');
		//keyboard short cuts
		JText::script('COM_FABRIK_LIST_SHORTCUTS_ADD');
		JText::script('COM_FABRIK_LIST_SHORTCUTS_EDIT');
		JText::script('COM_FABRIK_LIST_SHORTCUTS_DELETE');
		JText::script('COM_FABRIK_LIST_SHORTCUTS_FILTER');

		$script .= "\n" . "var list = new FbList($listid,";
		$script .= $opts;
		$script .= "\n" . ");";
		$script .= "\n" . "Fabrik.addBlock('list_{$listid}', list);";

		//add in plugin objects
		$params = $model->getParams();
		//$activePlugins = $params->get('plugins');
		$pluginManager = FabrikWorker::getPluginManager();
		$c = 0;

		$pluginManager->runPlugins('onLoadJavascriptInstance', $model, 'list');
		$aObjs = $pluginManager->_data;

		$script .= "\nlist.addPlugins([\n";
		$script .= "  " . implode(",\n  ", $aObjs);
		$script .= "\n]);\n";

		//@since 3.0 inserts content before the start of the list render (currently on f3 tmpl only)
		$pluginManager->runPlugins('onGetContentBeforeList', $model, 'list');
		$this->assign('pluginBeforeList', $pluginManager->_data);

		$script = "
		head.ready(function() {
		".$model->filterJs."
		$script
		})";
		FabrikHelperHTML::addScriptDeclaration($script);
		$this->getElementJs();
		//reset data back to original settings
		$this->rows = $origRows;
	}
	
	protected function getElementJs()
	{
		$model = $this->getModel();
		$model->getElementJs();
	}

	/**
	 * display the template
	 *
	 * @param sting $tpl
	 */

	function display($tpl = null)
	{
		$profiler = JProfiler::getInstance('Application');
		$app = JFactory::getApplication();
		$Itemid	= @$app->getMenu('site')->getActive()->id;
		// turn off deprecated warnings in 5.3 or greater,
		// or J!'s PDF lib throws warnings about set_magic_quotes_runtime()
		if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
			$current_level = error_reporting();
			error_reporting($current_level & ~E_DEPRECATED);
		}
		require_once(COM_FABRIK_FRONTEND.DS.'views'.DS.'modifiers.php');
		$user = JFactory::getUser();
		$model = $this->getModel();

		$document = JFactory::getDocument();

		//this gets the component settings
		$usersConfig = JComponentHelper::getParams('com_fabrik');

		$item = $model->getTable();
		$model->render();

		$w = new FabrikWorker();
		if (!$this->isMambot) {
			$document->setTitle($w->parseMessageForPlaceHolder($item->label, $_REQUEST));
		}
		//$document->setName($w->parseMessageForPlaceHolder($item->label, $_REQUEST));
		$data = $model->getData();

		//add in some styling short cuts
		$c 		= 0;
		$form = $model->getFormModel();
		$nav 	= $model->getPagination();

		foreach ($data as $groupk => $group) {
			$last_pk = '';
			$last_i = 0;
			for ($i=0; $i<count($group); $i++) {
				$o = new stdClass();
				// $$$ rob moved merge wip code to FabrikModelTable::formatForJoins() - should contain fix for pagination
				$o->data = $data[$groupk][$i];
				$o->cursor = $i + $nav->limitstart;
				$o->total = $nav->total;
				$o->id = "list_".$item->id."_row_".@$o->data->__pk_val;
				$o->class = "fabrik_row oddRow".$c;
				$data[$groupk][$i] = $o;
				$c = 1-$c;
			}
		}
		$groups = $form->getGroupsHiarachy();
		foreach ($groups as $groupModel) {
			$elementModels = $groupModel->getPublishedElements();
			foreach ($elementModels as $elementModel) {
				$e = $elementModel->getElement();
				$elementModel->setContext($groupModel, $form, $model);
				$elparams = $elementModel->getParams();
				$col 	= $elementModel->getFullName(false, true, false);
				$col .= "_raw";
				$rowclass = $elparams->get('use_as_row_class');
				if ($rowclass == 1) {
					foreach ($data as $groupk => $group) {
						for ($i=0; $i < count($group); $i++) {
							$data[$groupk][$i]->class .= " ". preg_replace('/[^A-Z|a-z|0-9]/', '-', $data[$groupk][$i]->data->$col);
						}
					}
				}
			}
		}
		$this->rows = $data;
		reset($this->rows);
		$firstRow = current($this->rows); //cant use numeric key '0' as group by uses groupd name as key
		$this->nodata = (empty($this->rows) || (count($this->rows) == 1 && empty($firstRow))) ? true : false;
		$this->tableStyle = $this->nodata ? 'display:none' : '';
		$this->emptyStyle = $this->nodata ? '' : 'display:none';
		$params = $model->getParams();

		if (!$model->canPublish()) {
			echo JText::_('COM_FABRIK_LIST_NOT_PUBLISHED');
			return false;
		}

		if (!$model->canView()) {
			echo JText::_('JERROR_ALERTNOAUTHOR');
			return false;
		}

		$this->table 					= new stdClass();
		$this->table->label 	= $w->parseMessageForPlaceHolder($item->label, $_REQUEST);
		$this->table->intro 	= $w->parseMessageForPlaceHolder($item->introduction);
		$this->table->id			= $item->id;
		$this->group_by				= $item->group_by;
		$this->formid = 'listform_' . $item->id;
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
		//$this->deleteButton 	= $model->canDelete() ?  "<input class='button' type='button' onclick=\"$jsdelete\" value='" . JText::_('COM_FABRIK_DELETE') . "' name='delete'/>" : '';

		$this->showPDF = false;
		$this->pdfLink = false;

		$this->emptyLink = $model->canEmpty() ? '#' : '';
		$this->csvImportLink = $this->showCSVImport ? JRoute::_("index.php?option=com_fabrik&view=import&filetype=csv&listid=" . $item->id) : '';
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
		JDEBUG ? $profiler->mark('fabrik getfilters end') : null;
		$form->getGroupsHiarachy();
		$this->filters = $model->getFilters('listform_'. $model->getRenderContext());
		$this->assign('showFilters', (count($this->filters) > 0 && $params->get('show-table-filters', 1)) && JRequest::getVar('showfilters', 1) == 1 ?  1 : 0);

		$this->assign('requiredFiltersFound', $this->get('RequiredFiltersFound'));
		$this->assign('emptyDataMessage', $this->get('EmptyDataMsg'));
		$this->calculations 	= $this->_getCalculations($this->headings);

		$this->assign('isGrouped', $item->group_by);
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
			$tmpl = JRequest::getVar('layout', $item->template);
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

protected function _getCalculations($aCols)
	{
		$aData = array();
		$found = false;
		$model = $this->getModel();
		$modelCals = $model->getCalculations();

		foreach ($aCols as $key=>$val) {
			$calc = '';
			$res = '';
			$oCalcs = new stdClass();
			$oCalcs->grouped = array();

			if (array_key_exists($key, $modelCals['sums'])) {
				$found = true;
				$res = $modelCals['sums'][$key];
				$calc .= $res;
				$tmpKey = str_replace(".", "___", $key) . "_calc_sum";
				$oCalcs->$tmpKey = $res;
			}
			if (array_key_exists($key . '_obj', $modelCals['sums'])) {
				$found = true;
				$res = $modelCals['sums'][$key. '_obj'];
				foreach ($res as $k => $v) {
					if ($k != 'calc') {
						@$oCalcs->grouped[$k] .= "<span class=\"calclabel\">".$v->calLabel . ":</span> " . $v->value . "<br />";
					}
				}
			}

			if (array_key_exists($key, $modelCals['avgs'])) {
				$found = true;
				$res = $modelCals['avgs'][$key];
				$calc .= $res;
				$tmpKey = str_replace(".", "___", $key) . "_calc_average";
				$oCalcs->$tmpKey = $res;
			}

			if (array_key_exists($key . '_obj', $modelCals['avgs'])) {
				$found = true;
				$res = $modelCals['avgs'][$key. '_obj'];
				foreach ($res as $k => $v) {
					if ($k != 'calc') {
						@$oCalcs->grouped[$k] .= "<span class=\"calclabel\">".$v->calLabel . ":</span> " . $v->value . "<br />";
					}
				}
			}

			if (array_key_exists($key. '_obj', $modelCals['medians'])) {
				$found = true;
				$res = $modelCals['medians'][$key. '_obj'];
				foreach ($res as $k => $v) {
					if ($k != 'calc') {
						@$oCalcs->grouped[$k] .= "<span class=\"calclabel\">".$v->calLabel . ":</span> " . $v->value . "<br />";
					}
				}
			}

			if (array_key_exists($key, $modelCals['medians'])) {
				$found = true;
				$res = $modelCals['medians'][$key];
				$calc .= $res;
				$tmpKey = str_replace(".", "___", $key) . "_calc_median";
				$oCalcs->$tmpKey = $res;
			}

			if (array_key_exists($key. '_obj', $modelCals['count'])) {
				$found = true;
				$res = $modelCals['count'][$key. '_obj'];
				foreach ($res as $k => $v) {
					if ($k != 'calc') {
						@$oCalcs->grouped[$k] .= "<span class=\"calclabel\">".$v->calLabel . ":</span> " . $v->value . "<br />";
					}
				}
			}

			if (array_key_exists($key, $modelCals['count'])) {
				$res = $modelCals['count'][$key];
				$calc .= $res;
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