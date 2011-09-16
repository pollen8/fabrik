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

	public $isMambot = null;

	var $repeatableJoinGroupCount = 0;

	var $access = null;

	/**
	 * main setup routine for displaying the form/detail view
	 * @param string template
	 */

	function display($tpl = null)
	{
		global $_PROFILER;
		FabrikHelperHTML::framework();
		$app = JFactory::getApplication();
		$w = new FabrikWorker();
		$config = JFactory::getConfig();
		$model = $this->getModel('form');
		$document = JFactory::getDocument();
		FabrikHelperHTML::mootools();

		$model->isMambot = $this->isMambot;
		$form = $model->getForm();
		if ($model->render() === false) {
			return false;
		}
		$this->isMultiPage = $model->isMultiPage();
		list($this->plugintop, $this->pluginbottom, $this->pluginend) = $model->_getFormPluginHTML();

		$listModel = $model->getlistModel();
		$table = $listModel->noTable() ? null : $listModel->getTable();
		if (!$model->canPublish()) {
			if (!$app->isAdmin()) {
				echo JText::_('COM_FABRIK_FORM_NOT_PUBLISHED');
				return false;
			}
		}

		$this->assign('access', $model->checkAccessFromListSettings());
		
		if ($this->access == 0) {
			return JError::raiseWarning(500, JText::_('JERROR_ALERTNOAUTHOR'));
		}
		JDEBUG ? $_PROFILER->mark('form view before join group ids got') : null;
		if (!$listModel->noTable()) {
			$joins = $listModel->getJoins();
			$model->getJoinGroupIds($joins);
		}
		$params = $model->getParams();
		$this->setTitle($w, $params);
		FabrikHelperHTML::debug($params->get('note'), 'note');
		$params->def('icons', $app->getCfg('icons'));
		$params->set('popup', (JRequest::getVar('tmpl') == 'component') ? 1 : 0);

		$this->editable = $model->_editable;
		
		$form->label = $this->get('label');
		$form->intro = $this->get('Intro');
		$form->action = $this->get('Action');
		
		$form->formid = $model->_editable ? "form_".$model->getId() : 'details_' . $model->getId();
		$form->name = "form_".$model->getId();

		$form->origerror = $form->error;
		$form->error = count($model->_arErrors) > 0 ? $form->error : '';

		$this->_addButtons();
		JDEBUG ? $_PROFILER->mark('form view before validation classes loaded') : null;
		
		$t = ($model->_editable)? $form->form_template : $form->view_only_template;
		if ($this->isMambot) {
			// need to do this otherwise when viewing form in form module and in category/blog content page
			// you get a 500 error regarding layout default not found.
			$form->form_template = JRequest::getVar('flayout', $t);
		} else {
			$form->form_template = JRequest::getVar('layout', $t);
		}
		
		$tmpl = $this->get('tmpl');
		$this->assign('tmpl', $tmpl);
		JDEBUG ? $_PROFILER->mark('form view before group view got') : null;
		
		$this->groups = $model->getGroupView($tmpl);
		JDEBUG ? $_PROFILER->mark('form view after group view got') : null;
		$this->assignRef('data', $model->data);
		$this->assignRef('modeldata', $model->_data);
		$this->assignRef('params', $params);
		FabrikHelperHTML::debug($this->groups, 'form:view:groups');
		
		//cck in admin?
		$this->cck();
		JDEBUG ? $_PROFILER->mark('form view: after cck') : null;
		//force front end templates
		$this->_basePath = COM_FABRIK_FRONTEND.DS.'views';

		$this->_addJavascript($listModel->getId());
		JDEBUG ? $_PROFILER->mark('form view: after add js') : null;
		$this->_loadTmplBottom($form);
		JDEBUG ? $_PROFILER->mark('form view: after tmpl bottom loaded') : null;

		if ($model->_editable) {
			$form->startTag = "<form action=\"{$form->action}\" class=\"fabrikForm\" method=\"post\" name=\"{$form->name}\" id=\"{$form->formid}\" enctype=\"{$model->getFormEncType()}\">";
			$form->endTag = "</form>";
		} else {
			$form->startTag = '<div class="fabrikForm fabrikDetails" id="'.$form->formid.'">';
			$form->endTag  = '</div>';
		}
		$form->endTag .= $this->pluginend;
		$this->assignRef('form', $form);
		JDEBUG ? $_PROFILER->mark('form view: form assigned as ref') : null;
		$list = new stdClass();
		$list->id = $form->record_in_database ? $model->getListModel()->getTable()->id : 0;
		$this->assignRef('list', $list);
		JDEBUG ? $_PROFILER->mark('form view: before getRelatedTables()') : null;
		$this->assignRef('linkedTables', $this->get('RelatedTables'));
		JDEBUG ? $_PROFILER->mark('form view: after getRelatedTables()') : null;
		$this->setMessage();

		$this->addTemplatePath($this->_basePath.DS.$this->_name.DS.'tmpl'.DS.$tmpl);
		$this->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_fabrik'.DS.'form'.DS.$tmpl);

		JDEBUG ? $_PROFILER->mark('form view before template load') : null;
		$text = $this->loadTemplate();
		if ($params->get('process-jplugins') == 1 || ($params->get('process-jplugins') == 2 && $model->_editable === false)) {
			$opt = JRequest::getVar('option');
			JRequest::setVar('option', 'com_content');
			jimport('joomla.html.html.content');
			$text .= '{emailcloak=off}';
			$text = JHTML::_('content.prepare', $text);
			$text = preg_replace('/\{emailcloak\=off\}/', '', $text);
			JRequest::setVar('option', $opt);
		}
		//$text = "<div id=\"fabrik\">$text</div>";
		JDEBUG ? $_PROFILER->mark('form view display end') : null;
		echo $text;
	}

	/**
	 * set template message when using session multipages
	 */

	private function setMessage()
	{
		$model = $this->getModel();
		if (!$model->isMultiPage()) {
			$this->assign('message', '');
			return;
		}
		$message = '';
		if ($model->sessionModel) {
			$this->message = $model->sessionModel->status;
			//see http://fabrikar.com/forums/showpost.php?p=73833&postcount=14
			//if ($model->sessionModel->statusid == _FABRIKFORMSESSION_LOADED_FROM_COOKIE) {
			if ($model->sessionModel->last_page > 0) {
				$message .= " <a href=\"#\" class=\"clearSession\">" . JText::_('COM_FABRIK_CLEAR') . "</a>";
			}
		}

		$this->assign('message', $message);
	}

	/**
	 * set the page title
	 *
	 * @param object parent worker
	 */

	protected function setTitle($w, &$params)
	{
		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		$title = '';
		if ($app->getName() !== 'administrator') {
			$menus	= JSite::getMenu();
			$menu	= $menus->getActive();
			//if there is a menu item available AND the form is not rendered in a content plugin or module
			if (is_object($menu) && !$this->isMambot) {
				$menu_params = new JParameter($menu->params);
				if (!$menu_params->get('page_title') || $menu_params->get('show_page_title') == 0) {
					$params->set('page_title', $title);
				} else {
					$params->set('page_title', $menu_params->get('page_title'));
				}
				$params->set('show_page_title', $menu_params->get('show_page_title', 0));
			} else {
				$params->set('show_page_title', JRequest::getInt('show_page_title', 0));
				$params->set('page_title', JRequest::getVar('title', $title));
				$params->set('show-title', JRequest::getInt('show-title', $params->get('show-title')));
			}
			if (!$this->isMambot) {
				$document->setTitle($w->parseMessageForPlaceHolder($params->get('page_title'), $_REQUEST));
			}
		} else {
			$params->set('page_title', $title);
			$params->set('show_page_title', 0);
		}

		$model = $this->getModel();
		if (!$this->isMambot) {
			$title = $model->getPageTitle($params->get('page_title'));
			$document->setTitle($w->parseMessageForPlaceHolder($title, $_REQUEST));
		}
	}

	/**
	 * add buttons to the view e.g. print, pdf
	 */

	protected function _addButtons()
	{
		$model = $this->getModel();
		$params	= $model->getParams();
		$this->showEmail = $params->get('email', 0);
		$this->emailLink = '';
		$this->printLink = '';
		$this->pdfLink = '';
		$this->showPrint = $params->get('print', 0);

		if ($this->showPrint) {
			$text = JHTML::_('image.site',  'printButton.png', '/images/M_images/', NULL, NULL, JText::_('Print'));
			$this->printLink = '<a href="#" onclick="window.print();return false;">'.$text.'</a>';
		}

		if (JRequest::getVar('tmpl') != 'component') {
			if ($this->showEmail) {
				$this->emailLink = FabrikHelperHTML::emailIcon($model, $params);
			}

			if ($this->showPrint) {
				$this->printLink = FabrikHelperHTML::printIcon($model, $params, $model->_rowId);
			}

			$this->showPDF = $params->get('pdf', 0);
			if ($this->showPDF) {
				$this->pdfLink = FabrikHelperHTML::pdfIcon($model, $params, $model->_rowId);
			}
		} else {
			$this->showPDF = false;
		}
	}

	/**
	 * append the form javascript into the document head
	 * @param int table id
	 */

	protected function _addJavascript($tableId)
	{
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		$model = $this->getModel();

		$aLoadedElementPlugins = array();
		$jsActions = array();
		$jsControllerKey = $model->_editable ? 'form_'. $model->getId() : 'details_'. $model->getId();

		if (!defined('_JOS_FABRIK_FORMJS_INCLUDED')) {
			define('_JOS_FABRIK_FORMJS_INCLUDED', 1);
			FabrikHelperHTML::slimbox();
			$srcs = array('media/com_fabrik/js/form.js', 'media/com_fabrik/js/element.js');

		}

		$aWYSIWYGNames = array();
		// $$$ hugh - yeat another one where if we =, the $groups array pointer get buggered up and it
		// skips a group
		$groups = $model->getGroupsHiarachy();
		foreach ($groups as $groupModel) {
			$elementModels = $groupModel->getPublishedElements();
			foreach ($elementModels as $elementModel) {
				$res = $elementModel->useEditor();
				if ($res !== false) {
					$aWYSIWYGNames[] = $res;
				}
				$eparams = $elementModel->getParams();
				//load in once the element js class files
				// $$$ hugh - only needed getParent when we weren't saving changes to parent params to child
				// which we should now be doing ... and getParent() causes an extra table lookup for every child
				// element on the form.
				//$element = $elementModel->getParent();
				$element = $elementModel->getElement();
				if (!in_array($element->plugin, $aLoadedElementPlugins)) {
					$aLoadedElementPlugins[] = $element->plugin;
					$elementModel->formJavascriptClass($srcs);
				}
				$eventMax = ($groupModel->_repeatTotal == 0) ? 1 : $groupModel->_repeatTotal;
				for ($c = 0; $c < $eventMax; $c ++) {
					$jsActions[] = $elementModel->getFormattedJSActions($jsControllerKey, $c);
				}
			}
		}

		FabrikHelperHTML::script($srcs, true);
		//new
		$actions = trim(implode("\n", $jsActions));
		//end new

		$params = $model->getParams();
		$listModel = $model->getlistModel();
		$table = $listModel->getTable();
		$form = $model->getForm();
		FabrikHelperHTML::mocha();

		$bkey = $model->_editable ? 'form_'. $model->getId() : 'details_'. $model->getId();

		FabrikHelperHTML::tips('.hasTip', array(), "$('$bkey')");
		$key = FabrikString::safeColNameToArrayKey($table->db_primary_key);

		$this->get('FormCss');
		$this->get('CustomJsAction');

		$startJs = "head.ready(function() {\n";
		$endJs = "});\n";
		$start_page = isset($model->sessionModel->last_page) ? (int)$model->sessionModel->last_page : 0;
		if ($start_page !== 0) {
			$app->enqueueMessage(JText::_('COM_FABRIK_RESTARTING_MUTLIPAGE_FORM'));
		} else {
			// form submitted but fails validation - needs to go to the last page
			$start_page = JRequest::getInt('currentPage', 0);
		}

		$opts = new stdClass();

		$opts->admin = $app->isAdmin();
		$opts->ajax = $model->isAjax();
		$opts->ajaxValidation = $params->get('ajax_validations');
		$opts->primaryKey = $key;
		$opts->error = @$form->origerror;
		$opts->pages = $model->getPages();
		$opts->plugins	= array();
		$opts->multipage_save = (bool)$model->saveMultiPage();
		$opts->editable = $model->_editable;
		$opts->start_page = $start_page;
		$opts->inlineMessage = (bool)$this->isMambot;
		//$$$rob dont int this as keys may be string
		$opts->rowid = $model->_rowId;
		//3.0 needed for ajax requests
		$opts->listid = (int)$this->get('ListModel')->getId();

		$imgs = new stdClass();
		$imgs->alert = FabrikHelperHTML::image('alert.png', 'form', $this->tmpl, '', true);
		$imgs->action_check = FabrikHelperHTML::image('action_check.png', 'form', $this->tmpl, '', true);
		$imgs->ajax_loader = FabrikHelperHTML::image('ajax-loader.gif', 'form', $this->tmpl, '', true);
		$opts->images = $imgs;
		//$$$rob if you are loading a table in a window from a form db join select record option
		// then we want to know the id of the window so we can set its showSpinner() method
		$opts->fabrik_window_id	= JRequest::getVar('fabrik_window_id', ''); //3.0 changed to fabrik_window_id (automatically appended by Fabrik.Window xhr request to load window data

		//for editing groups with joined data and an empty joined record (ie no joined records)
		$hidden = array();
		$maxRepeat = array();
		foreach ($this->groups as $g) {
			$hidden[$g->id] = $g->startHidden;
			$maxRepeat[$g->id] = $g->maxRepeat;
		}
		$opts->hiddenGroup = $hidden;
		$opts->maxRepeat = $maxRepeat;
		//$$$ rob 26/04/2011 joomfish translations of password validation error messages
		//$opts->lang = FabrikWorker::getJoomfishLang();

		$opts = json_encode($opts);

		$lang = new stdClass();

		JText::script('COM_FABRIK_VALIDATING');
		JText::script('COM_FABRIK_SUCCESS');
		JText::script('COM_FABRIK_NO_REPEAT_GROUP_DATA');
		JText::script('COM_FABRIK_VALIDATION_ERROR');
		JText::script('COM_FABRIK_FORM_SAVED');
		Jtext::script('COM_FABRIK_CONFIRM_DELETE');

		//$$$ rob dont declare as var $bkey, but rather assign to window, as if loaded via ajax window the function is wrapped
		// inside an anoymous function, and therefore $bkey wont be available as a global var in window
		$str ="head.ready(function() {
		window.$bkey = new FbForm(".$model->getId().", $opts);\n";
		$str .= "if(typeOf(Fabrik) !== 'null') {\n";
		$str .= "Fabrik.addBlock('$bkey', $bkey);\n";
		$str .= "}\n
		});";
		//instantaite js objects for each element

		$vstr = "\n";

		$str .= "$startJs";
		$groups = $model->getGroupsHiarachy();

		// $$$ rob in php5.2.6 (and possibly elsewhere) $groups's elements havent been updated
		// to contain the default value used by the element
		//foreach ($groups as $groupModel) {

		//testing this one again as Ive updated getGroupsHiarchy
		$str .= "{$bkey}.addElements({";
		foreach ($groups as $groupModel) {
			$showGroup = $groupModel->getParams()->get('repeat_group_show_first');
			if ($showGroup == -1 || ($showGroup == 2 && $model->_editable)) {
				// $$$ rob unpublished group so dont include the element js
				continue;
			}
			$aObjs = array();
			$elementModels = $groupModel->getPublishedElements();
			// $$$ rob if _repeatTotal is 0 we still want to add the js objects as the els are only hidden
			$max = $groupModel->_repeatTotal > 0 ? $groupModel->_repeatTotal : 1;
			$str .= $groupModel->getGroup()->id . ":[";
			foreach ($elementModels as $elementModel) {
				$element = $elementModel->getElement();
				if ($element->published == 0) {
					continue;
				}
				$fullName = $elementModel->getFullName();
				$id = $elementModel->getHTMLId();
				$elementModel->_editable = ($model->_editable);

				if ($elementModel->canUse() || $elementModel->canView()) {
					for ($c = 0; $c < $max; $c ++) {
						// $$$ rob ensure that some js code has been returned otherwise dont add empty data to array
						$ref = trim($elementModel->elementJavascript($c));
						if ($ref !== '') {
							$aObjs[] = $ref;
						}

						$validations = $elementModel->getValidations();
						if (!empty($validations) && $elementModel->_editable) {
							$watchElements = $elementModel->getValidationWatchElements($c);
							foreach ($watchElements as $watchElement) {
								$vstr .= "$bkey.watchValidation('".$watchElement['id']."', '".$watchElement['triggerEvent']."');\n";
							}
						}
					}
				}
			}
			$str .= implode(",\n", $aObjs);
			$str .= "],";
		}
		$str = FabrikString::rtrimword($str, ',');
		$str .= "});\n";
		$str .=  $actions;
		$str .= $vstr;
		$str .= $endJs;
		$str .= "function submit_form() {";
		if (!empty($aWYSIWYGNames)) {
			jimport('joomla.html.editor');
			$editor =JFactory::getEditor();
			$str .= $editor->save('label');

			foreach ($aWYSIWYGNames as $parsedName) {
				$str .= $editor->save($parsedName);
			}
		}
		$str .="
			return false;
		}

		function submitbutton(button) {
			if (button==\"cancel\") {
				document.location = '".JRoute::_('index.php?option=com_fabrik&task=viewTable&cid='.$tableId). "';
			}
			if (button == \"cancelShowForm\") {
				return false;
			}
		}
";
		FabrikHelperHTML::addScriptDeclaration($str);
		$pluginManager = $model->getPluginManager();
		$pluginManager->runPlugins('onAfterJSLoad', $model);
		FabrikHelperHTML::mootools();
	}

	protected function _loadTmplBottom(&$form)
	{
		$app = JFactory::getApplication();
		$menuItem = $app->getMenu('site')->getActive();
		$Itemid	= $menuItem ? $menuItem->id : 0;
		$model = $this->getModel();
		$listModel = $model->getListModel();
		$canDelete = $listModel->canDelete($model->_data);
		$params = $model->getParams();
		$task = 'form.process';
		$reffer = JRequest::getVar('HTTP_REFERER', '', 'server');
		// $$$rob - if returning from a failed validation then we should use the fabrik_referrer post var
		$reffer =str_replace('&', '&amp;',  JRequest::getVar('fabrik_referrer', $reffer));

		$this_rowid = is_array($model->_rowId)? implode('|', $model->_rowId) : $model->_rowId;
		$aHiddenFields = "<input type=\"hidden\" name=\"listid\" value=\"" . $listModel->getId() . "\" />\n".
		"<input type=\"hidden\" name=\"rowid\" value=\"" . $this_rowid . "\" />\n".
		"<input type=\"hidden\" name=\"Itemid\" value=\"" . $Itemid . "\" />\n".
		"<input type=\"hidden\" name=\"option\" value=\"com_fabrik\" />\n".
    "<input type=\"hidden\" name=\"task\" value=\"$task\" />\n".
    "<input type=\"hidden\" name=\"isMambot\" value=\"$this->isMambot\" />\n".
		"<input type=\"hidden\" name=\"formid\" value=\"" . $model->get('id') . "\" />\n".
		"<input type=\"hidden\" name=\"returntoform\" value=\"0\" />\n".
		"<input type=\"hidden\" name=\"fabrik_referrer\" value=\"" . $reffer . "\" />\n".
		"<input type=\"hidden\" name=\"fabrik_ajax\" value=\"" . (int)$model->isAjax() . "\" />\n";

		$aHiddenFields .= "<input type=\"hidden\" name=\"_packageId\" value=\"$model->packageId\" />\n";

		if ($usekey = JRequest::getVar('usekey')) {

			// $$$rob v's been set from -1 to the actual row id - so ignore usekyey not sure if we should comment this out
			// see http://fabrikar.com/forums/showthread.php?t=10297&page=5

			$aHiddenFields .= "<input type=\"hidden\" name=\"usekey\" value=\"" . $usekey . "\" />\n";
			if (empty($model->_data)) {
				$aHiddenFields .= "<input type=\"hidden\" name=\"usekey_newrecord\" value=\"1\" />\n";
			}
		}
		// $$$ hugh - testing a fix for pagination issue when submitting a 'search form'.
		// If this is a search form, we need to clear 'limitstart', otherwise ... say we
		// were last on page 4 of the (unfiltered) target table, and the search yields less than 4 pages,
		// we end up with a blank table 'cos the wrong LIMIT's are applied to the query
		$save_insessions = $params->get('save_insession', '');
		if (is_array($save_insessions)) {
			foreach ($save_insessions as $save_insession) {
				if ($save_insession == '1') {
					$aHiddenFields .= "<input type=\"hidden\" name=\"limitstart\" value=\"0\" />\n";
					break;
				}
			}
		}
		$aHiddenFields .= JHTML::_('form.token');

		$form->resetButton = $params->get('reset_button', 0) && $this->editable == "1" ?	"<input type=\"reset\" class=\"button\" name=\"Reset\" value=\"" . $params->get('reset_button_label') . "\" />\n" : '';
		$form->copyButton = $params->get('copy_button', 0) && $this->editable && $model->_rowId != '' ?	"<input type=\"submit\" class=\"button\" name=\"Copy\" value=\"" . $params->get('copy_button_label') . "\" />\n" : '';
		$form->applyButton = $params->get('apply_button', 0) && $this->editable ? "<input type=\"button\" class=\"button\" name=\"apply\" value=\"" . $params->get('apply_button_label') . "\" />\n" : '';
		$form->deleteButton = $params->get('delete_button', 0) && $canDelete && $this->editable && $this_rowid != 0 ? "<input type=\"submit\" value=\"" . $params->get('delete_button_label', 'Delete') . "\" class=\"button\" name=\"delete\" />" : '';
		$gobackaction = $model->isAjax() ? '': "onclick=\"history.back();\"";
		$form->gobackButton = $params->get('goback_button', 0) == "1" ?	"<input type=\"button\" class=\"button\" name=\"Goback\" $gobackaction value=\"" . $params->get('goback_button_label') . "\" />\n" : '';
		if ($model->_editable) {
			$button = $model->isAjax() ? "button" : "submit";
			$form->submitButton = '';
			$form->submitButton .= "<input type=\"$button\" class=\"button\" name=\"submit\" value=\"" . $form->submit_button_label ."\" />\n ";
		} else {
			$form->submitButton = '';
		}
		if ($this->isMultiPage) {
			$form->submitButton .= "<input type=\"button\" class=\"fabrikPagePrevious button\" name=\"fabrikPagePrevious\" value=\"" . JText::_('COM_FABRIK_PREVIOUS') ."\" />\n";
			$form->submitButton .= "<input type=\"button\" class=\"fabrikPageNext button\" name=\"fabrikPageNext\" value=\"" . JText::_('COM_FABRIK_NEXT') ."\" />\n";
		}
		$format = $model->isAjax() ? 'raw' : 'html';
		$aHiddenFields .= "<input type=\"hidden\" name=\"format\" value=\"$format\" />";

		$groups = $model->getGroupsHiarachy();
		foreach ($groups as $groupModel) {
			$group 	=& $groupModel->getGroup();
			$c 			= $groupModel->_repeatTotal;
			//used for validations
			$aHiddenFields .= "<input type=\"hidden\" name=\"fabrik_repeat_group[$group->id]\" value=\"" . $c . "\" id=\"fabrik_repeat_group_" . $group->id . "_counter\" />";
		}

		$this->_cryptQueryString($aHiddenFields);
		$this->_cryptViewOnlyElements($aHiddenFields);
		$this->hiddenFields = $aHiddenFields;
	}

	/** $$$rob store all fabrik querystring vars as encrypted hidden fields
	 * this is used when you have a table with a "Tables with database join elements linking to this table" link to a form.
	 * and when the form's pk element (found in the link) is set to read only
	 * OR
	 * if you are filtering from an url?
	 */

	protected function _cryptQueryString(&$aHiddenFields)
	{
		jimport('joomla.utilities.simplecrypt');
		jimport('joomla.utilities.utility');
		$crypt = new JSimpleCrypt();
		$formModel = $this->getModel();
		$get = JRequest::get('get');
		foreach ($get as $key => $input) {
			// 	$$$ rob test if passing in _raw value via qs -used in fabsubs
			if (!$formModel->hasElement($key)) {
				$key = FabrikString::rtrimword($key, '_raw');
			}
			if ($formModel->hasElement($key)) {
				$elementModel = $formModel->getElement($key);
				if (!$elementModel->canUse()) {
					$input = (is_array($input) && array_key_exists('value', $input)) ? $input['value'] : $input;
					// $$$ hugh - need to check if $value is an array, 'cos if it isn't, like when presetting
					// a new form element with &table___element=foo, getValue was chomping it down to just first character
					// see http://fabrikar.com/forums/showthread.php?p=82726#post82726
					if (is_array($input)) {
						$input = JArrayHelper::getValue($input, 'raw', $input);
					}
					// $$$ hugh - the aptly named SimpleCrypt encrypt is going to barf and toss a warning if we try
					// and encrypt a null or empty string
					if (empty($input)) {
						$input = '';
					}
					else {
						$input = $crypt->encrypt($input);
					}
					$aHiddenFields .= "<input type=\"hidden\" name=\"fabrik_vars[querystring][$key]\" value=\"" . $input . "\" />\n";
				}
			}
		}
	}


	protected function _cryptViewOnlyElements(&$aHiddenFields)
	{
		jimport('joomla.utilities.simplecrypt');
		jimport('joomla.utilities.utility');
		$crypt = new JSimpleCrypt();
		$formModel = $this->getModel();
		$fields = array();
		foreach ($this->get('readOnlyVals') as $key => $input) {
			$repeatGroup = $input['repeatgroup'];
			$isJoin = $input['join'];
			$input = $input['data'];
			// $$$ rob not sure this is correct now as I modified the readOnlyVals structure to contain info about if its in a group
			// and it now contains the repeated group data
			$input = (is_array($input) && array_key_exists('value', $input)) ? $input['value'] : $input;

			if ($repeatGroup) {
				$ar = array();
				$input = (array)$input;
				foreach ($input as $i) {
					if (is_array($i)) {
						//elements with sub options in repeat group
						$i = json_encode($i);
					}
					$ar[] = $i;
				}
				$input = $isJoin ? $ar : json_encode($ar);
			} else {
				if (is_array($input)) {
					//elements with sub options not in repeat group
					$input = json_encode($input);
				}
			}
			if (is_array($input)) {
				for ($x =0; $x < count($input); $x++) {
					if (trim($input[$x]) !== '') {
						$input[$x] = $crypt->encrypt($input[$x]);
					}
				}

			} else {
				if (trim($input) !== '') {
					$input = $crypt->encrypt($input);
				}
			}

			$key = FabrikString::rtrimword($key, "[]");
			// $$$ rob - no dont do below as it will strip out join names join[x][fullname] => join
			//$key = preg_replace("/\[(.*)\]/", '', $key);
			if (!array_key_exists($key, $fields)) {
				$fields[$key] = $input;
			} else {
				$fields[$key] = (array)$fields[$key];
				$fields[$key][] = $input;
			}
		}

		foreach ($fields as $key => $input) {
			if (is_array($input)) {
				for ($c = 0; $c < count($input); $c ++) {
					$i = $input[$c];
					$aHiddenFields .= "<input type=\"hidden\" name=\"fabrik_vars[querystring][$key][$c]\" value=\"" . $i . "\" />\n";
				}
			} else {
				$aHiddenFields .= "<input type=\"hidden\" name=\"fabrik_vars[querystring][$key]\" value=\"" . $input . "\" />\n";
			}
		}
	}

	/**
	 * load up the cck view
	 * @return unknown_type
	 */

	protected function cck()
	{
		if (JRequest::getVar('task') === 'cck') {
			$model = $this->getModel();
			$params = $model->getParams();
			$row = $model->getForm();
			JHTML::script('admincck.js', 'administrator/components/com_fabrik/views/', true);
			$document = JFactory::getDocument();
			$opts = new stdClass();
			$opts->livesite = JURI::base();
			$opts->ename = JRequest::getVar('e_name');
			$opts->catid = JRequest::getInt('catid');
			$opts->section = JRequest::getInt('section');
			$opts->formid = $row->id;

			$tmpl = ($row->form_template == '') ? "default" : $row->form_template;
			$tmpl = JRequest::getVar('cck_layout', $tmpl);

			$opts->tmplList = FabrikHelperAdminHTML::templateList('form', 'fabrik_cck_template', $tmpl);

			$views = array();
			$views[] = JHTML::_('select.option', 'form');
			$views[] = JHTML::_('select.option', 'details');
			$selView = JRequest::getVar('cck_view');
			$opts->viewList = JHTML::_('select.radiolist', $views, 'fabrik_cck_view', 'class="inputbox"', 'value', 'text', $selView);

			$opts = json_encode($opts);

			$document->addScriptDeclaration(
		"head.ready(function() {
		new adminCCK($opts);
		});"
			);
		}
	}

}
?>