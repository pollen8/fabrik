<?php
/**
 * Plugin element to render plain text
 * @package fabrikar
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class plgFabrik_ElementDisplay extends plgFabrik_Element
{

	protected  $fieldDesc = 'TEXT';

	function setIsRecordedInDatabase()
	{
		$this->_recordInDatabase = false;
	}

	/**
	 * write out the label for the form element
	 * @param object form
	 * @param bol encase label in <label> tag
	 * @param string id of element related to the label
	 */

	function getLabel($repeatCounter = 0, $tmpl = '')
	{
		$params = $this->getParams();
		if ($params->get('display_showlabel', true)) {
			return parent::getLabel($repeatCounter, $tmpl);
		}
		$bLabel = $this->get('hasLabel');

		$element = $this->getElement();
		$element->label = $this->getValue(array());
		$elementHTMLId = $this->getHTMLId();
		if ($element->hidden) {
			return '';
		}
		$task = JRequest::getVar('task', '', 'default');
		$view = JRequest::getVar('view', '', 'form');
		if ($view == 'form' && ! ( $this->canUse() || $this->canView())) {
			return '';
		}
		$params = $this->getParams();
		$elementid = "fb_el_" . $elementHTMLId;
		$this->_form->loadValidationRuleClasses();
		$str = '';

		$rollOver = JText::_($params->get('hover_text_title')) . "::" . JText::_($params->get('rollover'));
		$rollOver = htmlspecialchars($rollOver, ENT_QUOTES);

		if ($this->canView()) {
			$str .= "<div class=\"fabrikLabel fabrikPluginElementDisplayLabel";
			$validations = $this->getValidations();
			if ($this->_editable) {
				foreach ($validations as $validation) {
			  $vid = $validation->_pluginName;
			  if (array_key_exists($vid, $this->_form->_validationRuleClasses)) {
			  	if ($this->_form->_validationRuleClasses[$vid] != '') {
					  $str .= " " . $this->_form->_validationRuleClasses[$vid];
			  	}
			  }
				}
			}
			if ($rollOver != '::') {
				$str .= " fabrikHover";
			}
			$str .= "\" id=\"$elementid" . "_text\">";
			if ($bLabel) {
				$str .= "<label for=\"$elementHTMLId\">";
			}


			$str .= ($rollOver != '::') ? "<span class='hasTip' title='$rollOver'>{$element->label}</span>" : $element->label;
			if ($bLabel) {
				$str .= "</label>";
			}
			$str .= "</div>\n";
		}
		return $str;
	}

	/**
	 * draws the form element
	 * @param array data
	 * @param int repeat group counter
	 * @return string returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		$params = $this->getParams();
		if (!$params->get('display_showlabel', true)) {
			return '';
		}
		$id = $this->getHTMLId($repeatCounter);
		$value = $this->getValue($data, $repeatCounter);
		return "<div class=\"fabrikSubElementContainer\" id=\"$id\">$value</div>";
	}

	/**
	 * draws the form element
	 * @param array data
	 * @param int repeat group counter
	 * @param array options
	 * @return string default value
	 */

	function getValue($data, $repeatCounter = 0, $opts = array() )
	{
		$element = $this->getElement();
		$params = $this->getParams();
		// $$$rob - if no search form data submitted for the search element then the default
		// selection was being applied instead
		if (array_key_exists('use_default', $opts) && $opts['use_default'] == false) {
			$value = '';
		} else {
			$value = $this->getDefaultValue($data);
		}
		if ($value === '') {
			//query string for joined data
			$value = JArrayHelper::getValue($data, $value);
		}
		$formModel = $this->getForm();
		//stops this getting called from form validation code as it messes up repeated/join group validations
		if (array_key_exists('runplugins', $opts) && $opts['runplugins'] == 1) {
			FabrikWorker::getPluginManager()->runPlugins('onGetElementDefault', $formModel, 'form', $this);
		}
		return $value;
	}

}
?>