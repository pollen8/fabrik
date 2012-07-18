<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/tables/fabtable.php';

/**
 * Group Fabrik Table
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       3.0
 */

class FabrikTableGroup extends FabTable
{

	/*
	 *
	 */

	var $join_id = null;

	/**
	 * Constructor
	 *
	 * @param   object  &$db  database object
	 */

	public function __construct(&$db)
	{
		parent::__construct('#__{package}_groups', 'id', $db);
	}

	/**
	 * Overloaded check function
	 *
	 * @return  bool
	 */

	public function check()
	{
		if (trim($this->name) == '')
		{
			$this->_error = JText::_("YOUR GROUP MUST CONTAIN A NAME");
			return false;
		}
		return true;
	}

	public function load($keys = null, $reset = true)
	{
		if (empty($keys))
		{
			// If empty, use the value of the current key
			$keyName = $this->_tbl_key;
			$keyValue = $this->$keyName;

			// If empty primary key there's is no need to load anything
			if (empty($keyValue))
			{
				return true;
			}
			$keys = array($keyName => $keyValue);
		}
		elseif (!is_array($keys))
		{
			// Load by primary key.
			$keys = array($this->_tbl_key => $keys);
		}

		if ($reset)
		{
			$this->reset();
		}

		$db = $this->getDBO();
		$query = $db->getQuery(true);
		$query->select('#__{package}_groups.*, #__{package}_joins.id AS join_id')->from($this->_tbl)
			->join('LEFT', '#__{package}_joins ON #__{package}_groups.id = #__{package}_joins.group_id');

		foreach ($keys as $field => $value)
		{
			$query->where($db->quoteName('#__{package}_groups') . '.' . $db->quoteName($field) . ' = ' . $db->quote($value));
		}
		$query->where(" (( element_id = 0 OR is_join = 0) OR element_id IS NULL)");
		$db->setQuery($query);
		$row = $db->loadAssoc();

		// Check for a database error.
		if ($db->getErrorNum())
		{
			$e = new JException($this->_db->getErrorMsg());
			$this->setError($e);
			return false;
		}

		// Check that we have a result.
		if (empty($row))
		{
			$e = new JException(JText::_('JLIB_DATABASE_ERROR_EMPTY_ROW_RETURNED'));
			$this->setError($e);
			return false;
		}

		// Bind the object with the row and return.
		return $this->bind($row);
	}

	function store($updateNulls = false)
	{
		unset($this->join_id);
		return parent::store($updateNulls);
	}
}
