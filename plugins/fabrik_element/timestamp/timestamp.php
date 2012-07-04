<?php
/**
 * Slightly modified fabriktimestamp element (see lines 49-52)
 * By Nathan Cook 4/22/2010
 *
 * Plugin element to render fields
 * @package fabrikar
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once JPATH_SITE . '/components/com_fabrik/models/element.php';

class PlgFabrik_ElementTimestamp extends PlgFabrik_Element
{

	protected $recordInDatabase = false;

	function getLabel($repeatCounter, $tmpl = '')
	{
		return '';
	}

	function setIsRecordedInDatabase()
	{
		$this->recordInDatabase = false;
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
		$name = $this->getHTMLName($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$date = JFactory::getDate();
		$config = JFactory::getConfig();
		$tz = new DateTimeZone($config->get('offset'));
		$date->setTimezone($tz);
		$params = $this->getParams();
		$gmt_or_local = $params->get('gmt_or_local');
		$gmt_or_local += 0;
		return '<input name="' . $name . '" id="' . $id . '" type="hidden" value="' . $date->toSql($gmt_or_local) . '" />';
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
		$params = $this->getParams();
		$data = JHTML::_('date', $data, JText::_($params->get('timestamp_format', 'DATE_FORMAT_LC2')));
		return parent::renderListData($data, $thisRow);
	}

	/**
	 * Get database field description
	 * 
	 * @return  string  db field type
	 */

	public function getFieldDescription()
	{
		$params = $this->getParams();
		if ($params->get('encrypt', false))
		{
			return 'BLOB';
		}
		if ($params->get('timestamp_update_on_edit'))
		{
			return "TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
		}
		else
		{
			return "TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP";
		}
	}

	function isHidden()
	{
		return true;
	}

}
?>