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

class PlgFabrik_ElementDisplay extends PlgFabrik_Element
{

	protected $fieldDesc = 'TEXT';

	/** @var bool override default value as we don't want to record this in database*/
	protected $recordInDatabase = false;

	/**
	 * Set/get if element should record its data in the dabase
	 * 
	 * @deprecated - not used
	 * 
	 * @return bool
	 */

	function setIsRecordedInDatabase()
	{
		$this->recordInDatabase = false;
	}

	/**
	 * Get the element's HTML label
	 *  
	 * @param   int     $repeatCounter  group repeat counter
	 * @param   string  $tmpl           form template
	 * 
	 * @return  string  label
	 */

	public function getLabel($repeatCounter, $tmpl = '')
	{
		$params = $this->getParams();
		$element = $this->getElement();
		if (!$params->get('display_showlabel', true))
		{
			$element->label = $this->getValue(array());
		}
		return parent::getLabel($repeatCounter, $tmpl);
	}

	/**
	 * Shows the data formatted for the list view
	 * 
	 * @param   string  $data      elements data
	 * @param   object  &$thisRow  all the data in the lists current row
	 * 
	 * @return  string	formatted value
	 */

	public function renderListData($data, &$thisRow)
	{
		unset($this->default);
		$value = $this->getValue(JArrayHelper::fromObject($thisRow));
		return parent::renderListData($value, $thisRow);
	}

	/**
	 * Draws the html form element
	 * 
	 * @param   array  $data           to preopulate element with
	 * @param   int    $repeatCounter  repeat group counter
	 * 
	 * @return  string	elements html
	 */

	public function render($data, $repeatCounter = 0)
	{
		$params = $this->getParams();
		$id = $this->getHTMLId($repeatCounter);
		$value = $params->get('display_showlabel', true) ? $this->getValue($data, $repeatCounter) : '';
		return '<div class="fabrikSubElementContainer" id="' . $id . '">' . $value . '</div>';
	}

	/**
	 * Determines the value for the element in the form view
	 * 
	 * @param   array  $data           form data
	 * @param   int    $repeatCounter  when repeating joinded groups we need to know what part of the array to access
	 * @param   array  $opts           options
	 * 
	 * @return  string	value
	 */

	public function getValue($data, $repeatCounter = 0, $opts = array())
	{
		$element = $this->getElement();
		$params = $this->getParams();
		// $$$rob - if no search form data submitted for the search element then the default
		// selection was being applied instead
		$value = JArrayHelper::getValue($opts, 'use_default', true) == false ? '' : $this->getDefaultValue($data);
		if ($value === '')
		{
			//query string for joined data
			$value = JArrayHelper::getValue($data, $value);
		}
		$formModel = $this->getFormModel();
		//stops this getting called from form validation code as it messes up repeated/join group validations
		if (array_key_exists('runplugins', $opts) && $opts['runplugins'] == 1)
		{
			FabrikWorker::getPluginManager()->runPlugins('onGetElementDefault', $formModel, 'form', $this);
		}
		return $value;
	}

}
?>