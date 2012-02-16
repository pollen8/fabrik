<?php
/**
 * @package Joomla.Administrator
 * @subpackage Fabrik
 * @since		1.6
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

require_once('fabmodellist.php');

class FabrikModelLists extends FabModelList
{

	/**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */

	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'l.id', 'label', 'db_table_name', 'published'
				);
		}
		parent::__construct($config);
	}


	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */

	protected function getListQuery()
	{
		// Initialise variables.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
		$this->getState(
				'list.select',
				'l.*'
				)
				);
				$query->from('#__{package}_lists AS l');

				// Filter by published state
				$published = $this->getState('filter.published');
				if (is_numeric($published)) {
					$query->where('l.published = '.(int)$published);
				} else if ($published === '') {
					$query->where('(l.published IN (0, 1))');
				}

				//Filter by search in title
				$search = $this->getState('filter.search');
				if (!empty($search)) {
					$search = $db->Quote('%'.$db->getEscaped($search, true).'%');
					$query->where('(l.db_table_name LIKE '.$search.' OR l.label LIKE '.$search.')');
				}

				// Add the list ordering clause.
				$orderCol	= $this->state->get('list.ordering');
				$orderDirn	= $this->state->get('list.direction');
				if ($orderCol == 'ordering' || $orderCol == 'category_title') {
					$orderCol = 'category_title '.$orderDirn.', ordering';
				}
				if (trim($orderCol) !== '') {
					$query->order($db->getEscaped($orderCol.' '.$orderDirn));
				}

				return $query;
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 * @return	string		A store id.
	 * @since	1.6
	 */

	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.access');
		$id	.= ':'.$this->getState('filter.state');
		$id	.= ':'.$this->getState('filter.category_id');
		$id .= ':'.$this->getState('filter.language');
		return parent::getStoreId($id);
	}

	public function getTableGroups()
	{
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$query->select('DISTINCT(l.id) AS id, fg.group_id AS group_id');
		$query->from('#__{package}_lists AS l');
		$query->join('LEFT', '#__{package}_formgroup AS fg ON l.form_id = fg.form_id');
		$db->setQuery($query);
		$rows = $db->loadObjectList('id');
		return $rows;
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */

	public function getTable($type = 'View', $prefix = 'FabrikTable', $config = array())
	{
		$config['dbo'] = FabriKWorker::getDbo();
		return FabTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */

	protected function populateState()
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		//Load the published state
		$published = $app->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_fabrik');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('label', 'asc');
	}

	/**
	 * get an array of database table names used in fabrik lists
	 * @return array database table names
	 */

	public function getDbTableNames()
	{
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->select('db_table_name')->from('#__{package}_lists')->where('id IN('.implode(',', $cid).')');
		$db->setQuery($query);
		return $db->loadResultArray();
	}
}