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
		$element = $this->getElement();
		if (!$params->get('display_showlabel', true)) {
			$element->label = $this->getValue(array());
		}
		return parent::getLabel($repeatCounter, $tmpl);
	}
	
	/**
	 * render the elements list value
	 * @param unknown_type $data
	 * @param unknown_type $oAllRowsData
	 */
	function renderListData($data, $oAllRowsData)
	{
		unset($this->_default);
		$value = $this->getValue(JArrayHelper::fromObject($oAllRowsData));
		return parent::renderListData($value, $oAllRowsData);
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
		$id = $this->getHTMLId($repeatCounter);
		$value =  $params->get('display_showlabel', true) ? $this->getValue($data, $repeatCounter) : '';
		return '<div class="fabrikSubElementContainer" id="'.$id.'">'.$value.'</div>';
	}

	/**
	 * gets the value or default value 
	 * @param array data
	 * @param int repeat group counter
	 * @param array options
	 * @return string default value
	 */

	function getValue($data, $repeatCounter = 0, $opts = array())
	{
		$element = $this->getElement();
		$params = $this->getParams();
		// $$$rob - if no search form data submitted for the search element then the default
		// selection was being applied instead
		//echo "<pre>";print_r($data);
		$value =  JArrayHelper::getValue($opts, 'use_default', true) == false ? '' : $this->getDefaultValue($data);
		//echo $value;exit;
		if ($value === '') {
			//query string for joined data
			$value = JArrayHelper::getValue($data, $value);
		}
		$formModel = $this->getFormModel();
		//stops this getting called from form validation code as it messes up repeated/join group validations
		if (array_key_exists('runplugins', $opts) && $opts['runplugins'] == 1) {
			FabrikWorker::getPluginManager()->runPlugins('onGetElementDefault', $formModel, 'form', $this);
		}
		return $value;
	}

}
?>