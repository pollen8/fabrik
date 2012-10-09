<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.googleometer
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once JPATH_SITE . '/components/com_fabrik/models/element.php';

/**
 * Plugin element to render a google o meter chart
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.googleometer
 * @since       3.0
 */

class plgFabrik_ElementGoogleometer extends plgFabrik_Element
{

	/** @var  string  db table field type */
	protected $fieldDesc = 'TINYINT(%s)';

	/** @var  string  db table field size */
	protected $fieldSize = '1';

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
		$app = JFactory::getApplication();
		$input = $app->input;
		$name = $this->getHTMLName($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$params = $this->getParams();
		$element = $this->getElement();
		$value = $this->getValue($data, $repeatCounter);
		$range = $this->getRange();
		$fullName = $this->getDataElementFullName();
		if ($input->get('task') === 'details')
		{
			$data = $data[$fullName];
			$str = $this->_renderListData($data, $range);
			return $str;
		}
		return '';
	}

	private function getDataElementFullName()
	{
		$dataelement = $this->getDataElement();
		$fullName = $dataelement->getFullName();
		return $fullName;
	}

	private function getDataElement()
	{
		$params = $this->getParams();
		$elementid = (int) $params->get('googleometer_element');
		$element = FabrikWorker::getPluginManager()->getPlugIn('', 'element');
		$element->setId($elementid);
		return $element;
	}

	private function getRange()
	{
		$listModel = $this->getlistModel();
		$fabrikdb = $listModel->getDb();
		$db = FabrikWorker::getDbo();
		$element = $this->getDataElement();
		$elementShortName = $element->getElement()->name;

		$fabrikdb->setQuery("SELECT MIN(`$elementShortName`) AS min, MAX(`$elementShortName`) AS max FROM " . $listModel->getTable()->db_table_name);
		$range = $fabrikdb->loadObject();
		$fullName = $element->getFullName();
		return $range;
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
		static $range;
		static $fullName;
		if (!isset($range))
		{
			$range = $this->getRange();
			$fullName = $this->getDataElementFullName();
		}
		$data = $thisRow->$fullName;
		$data = $this->_renderListData($data, $range);
		return parent::renderListData($data, $thisRow);
	}

	function _renderListData($data, $range)
	{
		$options = array();
		$params = $this->getParams();
		$options['chartsize'] = 'chs=' . $params->get('googleometer_width', 200) . 'x' . $params->get('googleometer_height', 125);
		$options['charttype'] = 'cht=gom';
		$options['value'] = 'chd=t:' . $data;
		$options['label'] = 'chl=' . $params->get('googleometer_label');
		$options['range'] = 'chds=' . $range->min . ',' . $range->max;
		$options = implode('&amp;', $options);
		$str = '<img alt="Google-o-meter" src="http://chart.apis.google.com/chart?' . $options . '"/>';
		return $str;
	}

}
?>