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

  /**
   * main setup routine for displaying the form/detail view
   * @param string template
   */

  function display($tpl = null)
  {
		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
    $config	= JFactory::getConfig();
    $w = new FabrikWorker();
    $model = $this->getModel();
    $model->_editable = false;

    //Get the active menu item
    $usersConfig = JComponentHelper::getParams('com_fabrik');

    $form = $model->getForm();
    $data = $model->render();
    list($this->plugintop, $this->pluginbottom) = $this->get('FormPluginHTML');

    if (!$model->canPublish()) {
      if (!$app->isAdmin()) {
        echo JText::_('COM_FABRIK_FORM_NOT_PUBLISHED');
        return false;
      }
    }

    $access = $model->checkAccessFromListSettings();
    if ($access == 0) {
      echo JText::_('JERROR_ALERTNOAUTHOR');
      return false;
    }
    if ($access == 1 && $model->_editable == '1') {
      $model->_editable = 0;
    }
    if (is_object($model->_table)) {
      $joins = $model->_table->getJoins();
      $model->getJoinGroupIds($joins);
    }
    //as J1.7 doesnt have a pdf view we cant setName on raw doc type
		//$document->setName($w->parseMessageForPlaceHolder($this->get('PageTitle'), JArrayHelper::fromObject($data)));
		//$document->_engine->DefOrientation = 'L';

		$params = $model->getParams();
    $params->def('icons', $app->getCfg('icons'));
    $pop = JRequest::getVar('tmpl') == 'component' ? 1 : 0;
    $params->set('popup', $pop);
    $this->form_template = JRequest::getVar('layout', $form->form_template);

    $this->editable = $model->_editable;

    $form->label = $this->get('label');
    //$document->_engine->SetTitle( $form->label);
		$form->intro = $this->get('Intro');
		$form->action = $this->get('Action');

    $form->js 		= "";

		$form->formid = "form_".$model->getId();
		$form->name 	= "form_".$model->getId();

    $form->encType = $model->getFormEncType();;

    if (count($model->_arErrors) > 0) {
      $form->error = $form->error;
    } else {
      $form->error = '';
    }
    $this->showEmail = $params->get('email', 0);

    $this->assignRef('groups', $this->get('GroupView'));

    $this->assignRef('params', $params);

    $form->startTag = '<div class="fabrikForm fabrikDetails" id="detail_'.$model->getId().'">';
    $form->endTag  = '</div>';
    //force front end templates
    $this->_basePath = COM_FABRIK_FRONTEND . DS . 'views';

    $t = $params->get('pdf_template');
    if ($t == '') {
    	$t = ($model->_editable)?  $form->form_template : $form->view_only_template;
    }
    $form->form_template = JRequest::getVar('layout', $t);
    $tmpl = JRequest::getVar('layout', $form->form_template);
    $this->_includeTemplateCSSFile( $tmpl);

    $this->message = '';
    $this->_addButtons();
    $form->error = '';
    $this->hiddenFields = '';
    $form->resetButton = '';
    $form->submitButton = '';
    $form->copyButton = '';
    $form->gobackButton = '';
    $form->applyButton = '';
    $form->deleteButton = '';

    $this->assignRef('form', $form);

    $table = new stdClass();
		$table->id = $form->record_in_database ? $model->getListModel()->getTable()->id : 0;
		$this->assignRef('table', $table);

    if ($model->sessionModel) {
      $this->message = $model->sessionModel->status;
      if ($model->sessionModel->statusid == _FABRIKFORMSESSION_LOADED_FROM_COOKIE) {
        $this->message .= " <a href='#' class='clearSession'>" . JText::_('COM_FABRIK_CLEAR') . "</a>";
      }
    }
		$this->addTemplatePath($this->_basePath.DS.$this->_name.DS.'tmpl'.DS.$tmpl);
		$this->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_fabrik'.DS.'form'.DS.$tmpl);
    parent::display();
    return;
  }

  /**
   * include the template css files
   *
   * @param string template name
   */
  function _includeTemplateCSSFile( $formTemplate )
  {
    $config		= JFactory::getConfig();
    $document = JFactory::getDocument();
    $ab_css_file = JPATH_SITE.DS."components".DS."com_fabrik".DS."views".DS."form".DS."tmpl".DS."$formTemplate".DS."template.css";
    $live_css_file = COM_FABRIK_LIVESITE  . "components/com_fabrik/views/form/tmpl/$formTemplate/template.css";
    if (file_exists($ab_css_file)) {
      $document->addStyleSheet($live_css_file);
    }
  }

  /**
   * add buttons to the view e.g. print, pdf
   */

  function _addButtons()
  {
    $model		=& $this->getModel();
    $params 	=& $model->getParams();
    $this->showEmail = $params->get('email', 0);

    if (JRequest::getVar('tmpl') != 'component') {
      if ($this->showEmail) {
        $this->emailLink = '';
      }

      $this->showPrint = $params->get('print', 0);
      if ($this->showPrint) {
        $this->printLink = '';
      }

      $this->showPDF = $params->get('pdf', 0);
      if ($this->showPDF) {
        $this->pdfLink = '';
      }
    } else {
      $this->showPDF = $this->showPrint = false;
    }
  }

}
?>