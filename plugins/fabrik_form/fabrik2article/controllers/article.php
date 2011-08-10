<?php
// ensure a valid entry point
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.controller');

if (!defined('FABRIK2ARTICLE_PLUGIN')) {
	define('FABRIK2ARTICLE_PLUGIN', JPATH_ROOT.DS.'plugins'.DS.'fabrik_form'.DS.'fabrik2article');
}

class FabrikControllerArticle extends JController {

    var $_formModel = null;
    var $_params = null;
    var $_alternateTemplatePath = null;

    function __construct() {
        parent::__construct();

        $this->addModelPath(FABRIK2ARTICLE_PLUGIN.DS.'models');
        $this->addViewPath(FABRIK2ARTICLE_PLUGIN.DS.'views');
    }

    function setFormModel(&$model) {
        $this->_formModel = $model;
    }

    function &getFormModel() {
        return $this->_formModel;
    }

    function setParameters(&$plugParams) {
        $this->_params = $plugParams;
    }

    function &getParameters() {
        return $this->_params;
    }

    function getAlternateTemplatePath() {
        return $this->_alternateTemplatePath;
    }

    function setAlternateTemplatePath($path) {
        $this->_alternateTemplatePath = $path;
    }

    function execute() {
        $model =& $this->getModel('Article');
        $parVal = $this->_params->get('fabrik2article_article_id_element');
        if (empty($parVal)) {
            JError::raiseWarning('ERROR_CODE', JText::_(PARAM_ARTICLE_ID_NOT_SET));
        } else {
            $model->setArticleIdElement($parVal);
        }
        $parVal = $this->_params->get('fabrik2article_article_title_element');
        if (empty($parVal)) {
            JError::raiseWarning('ERROR_CODE', JText::_(PARAM_ARTICLE_TITLE_NOT_SET));
        } else {
            $model->setArticleTitleElement($parVal);
        }
		
        $model->setArticlePublishElement($this->_params->get('fabrik2article_article_publish_element', ''));

        $model->setCategoryId($this->_params->get('fabrik2article_category', 0));
        $model->setFormModel($this->_formModel);

        $view =& $this->getView('Article');
        $view->setModel($model, true);
		$view->addSkipElement($model->getArticlePublishElement());
        $view->setTemplate($this->_params->get('fabrik2article_template', 'default'));
        //$view->setAlternateTemplatePath($this->_alternateTemplatePath);
		

        JRequest::setVar('view', 'details');
        $model->setArticleText($view->display());
        $model->save();
    }

    function getView($name = '') {
        return parent::getView($name, 'html');
    }
}