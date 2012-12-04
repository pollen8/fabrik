<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.display
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Plugin element to render plain text/HTML
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.display
 */

class plgFabrik_ElementDisplay extends plgFabrik_Element
{

	/**
	 * Db table field type
	 *
	 * @var  string
	 */
	protected $fieldDesc = 'TEXT';

	/**
	 * Does the element's data get recorded in the db
	 *
	 * @var bol
	 */
	var $_recordInDatabase = false;

	/**
	 * Set/get if element should record its data in the dabase
	 *
	 * @deprecated - not used
	 *
	 * @return bool
	 */

	function setIsRecordedInDatabase()
	{
		$this->_recordInDatabase = false;
	}

	/**
	 * Get the element's HTML label
	 *
	 * @param   int     $repeatCounter  group repeat counter
	 * @param   string  $tmpl           form template
	 *
	 * @return  string  label
	 */

	public function getLabel($repeatCounter = 0, $tmpl = '')
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
		unset($this->_default);
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
	 * Helper method to get the default value used in getValue()
	 * Unlike other elements where readonly effects what is displayed, the display element is always
	 * read only, so get the default value.
	 *
	 * @param   array  $data   form data
	 * @param   array  $opts   options
	 *
	 * @since  3.0.7
	 *
	 * @return  mixed	value
	 */
	protected function getDefaultOnACL($data, $opts)
	{

		return JArrayHelper::getValue($opts, 'use_default', true) == false ? '' : $this->getDefaultValue($data);
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
		$value = $this->getDefaultOnACL($data, $opts);
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
