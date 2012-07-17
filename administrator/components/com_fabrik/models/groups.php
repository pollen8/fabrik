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

class FabrikModelGroups extends FabModelList
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
				'g.id', 'g.name', 'g.label', 'f.label', 'g.published'
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
				'g.*'
				)
		);
		$query->from('#__{package}_groups AS g');

		// Join over the users for the checked out user.
		$query->select('u.name AS editor, fg.form_id AS form_id, f.label AS flabel');
		$query->join('LEFT', '#__users AS u ON checked_out = u.id');
		$query->join('LEFT', '#__{package}_formgroup AS fg ON g.id = fg.group_id');
		$query->join('LEFT', '#__{package}_forms AS f ON fg.form_id = f.id');
		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
		if ($orderCol == 'ordering' || $orderCol == 'category_title') {
			$orderCol = 'category_title '.$orderDirn.', ordering';
		}
		$query->order($db->getEscaped($orderCol.' '.$orderDirn));

		$this->filterByFormQuery($query, 'fg');
		return $query;
	}

	/**
	 * Returns an object list
	 *
	 * @param	string	The query
	 * @param	int		Offset
	 * @param	int		The number of records
	 * @return	array
	 * @since	1.5
	 */

	protected function _getList($query, $limitstart=0, $limit=0)
	{
		$db = $this->getDbo();
		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('g.published = '.(int) $published);
		} elseif ($published === '') {
			$query->where('(g.published IN (0, 1))');
		}

		//Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->quote('%'.$db->getEscaped($search, true).'%');
			$query->where('(g.name LIKE '.$search.' OR g.label LIKE '.$search.')');
		}
		$this->_db->setQuery($query, $limitstart, $limit);
		$result = $this->_db->loadObjectList();

		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		$query->select('COUNT(id) AS count, group_id');
		$query->from('#__{package}_elements');
		$query->group('group_id');

		$db->setQuery($query);
		$elementcount = $db->loadObjectList('group_id');
		for ($i=0; $i < count($result); $i++) {
			$k = $result[$i]->id;
			$result[$i]->_elementCount = @$elementcount[$k]->count;
		}
		return $result;
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
	public function getTable($type = 'Group', $prefix = 'FabrikTable', $config = array())
	{
		$config['dbo'] = FabriKWorker::getDbo();
		return FabTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the parameters.
		$params = JComponentHelper::getParams('com_fabrik');
		$this->setState('params', $params);

		$published = $app->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		//Load the form state
		$package = $app->getUserStateFromRequest($this->context.'.filter.form', 'filter_form', '');
		$this->setState('filter.form', $package);

		// List state information.
		parent::populateState('name', 'asc');
	}
}