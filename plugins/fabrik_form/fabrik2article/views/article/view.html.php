<?php
// ensure a valid entry point
defined('_JEXEC') or die('Restricted Access');

jimport('joomla.application.component.view');

class FabrikViewArticle extends fabrikViewForm {

	var $_template = 'default.php';
	var $_alternateTemplatePath = null;
	var $_skipElements = array();

	function __construct($config = array()) {
		parent::__construct($config);
		$this->isMambot = true;
	}

	function addSkipElement($element_db_name) {
		$element_db_name = str_replace('.', '___',$element_db_name);
		$element_db_name .= '_ro';
		$this->_skipElements[] = $element_db_name;
	}

	function isSkipElement($element_ro_name) {
		return in_array($element_ro_name,$this->_skipElements);
	}

    function getAlternateTemplatePath() {
        return $this->_alternateTemplatePath;
    }

    function setAlternateTemplatePath($path) {
        $this->_alternateTemplatePath = $path;
    }

    function _setPath($type, $path) {
        parent::_setPath($type, $path);
        if (!empty($this->_alternateTemplatePath)) {
            $this->addTemplatePath($this->_alternateTemplatePath);
        }
    }

    function setTemplate($template) {
        $this->_template = $template;
        $layout = preg_replace('/\.php$/i', '', $this->_template);
		$model		=& $this->getModel();
		$form 	=& $model->getForm();
		$form->view_only_template = $template;
        //$this->setLayout($layout);
    }

    function getTemplate() {
        return $this->_template;
    }

	function display() {
		$output = parent::display();
		$output = '{fabrik view=form_css id=' . $this->form->id . '}' . $output;
		return $output;
	}
}