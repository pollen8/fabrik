<?php
/**
 * Create a list from an SQL query
 *
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php';

JFormHelper::loadFieldClass('list');

/**
 * Renders a SQL element
 *
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class JFormFieldSQL2 extends JFormFieldList
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var $_name = 'SQL';

	/**
	 * Get list options
	 *
	 * @return  array
	 */

	protected function getOptions()
	{
		$db = FabrikWorker::getDbo();

		$check = $this->element['checkexists'] ? (bool) $this->element['checkexists'] : false;
		if ($check)
		{
			$q = explode(" ", $this->element['query']);
			$i = array_search('FROM', $q);
			if (!$i)
			{
				$i = array_search('from', $q);
			}
			$i++;
			$tbl = $db->replacePrefix($q[$i]);
			$db->setQuery("SHOW TABLES");
			$rows = $db->loadResultArray();
			$found = in_array($tbl, $rows) ? true : false;
			if (!$found)
			{
				return array(JHTML::_('select.option', $tbl . ' not found', ''));
			}
		}
		$db->setQuery($this->element['query']);
		$key = $this->element['key_field'] ? $this->element['key_field'] : 'value';
		$val = $this->element['value_field'] ? $this->element['value_field'] : $this->name;
		if ($this->element['add_select'])
		{
			$rows = array(JHTML::_('select.option', ''));
			$rows = array_merge($rows, (array) $db->loadObjectList());
		}
		else
		{
			$rows = $db->loadObjectList();
		}
		return $rows;
	}
}
