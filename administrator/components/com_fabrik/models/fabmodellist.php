<?php
/**
* @package Joomla.Administrator
* @subpackage Fabrik
* @since		1.6
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');


class FabModelList extends JModelList
{
	/**
	 * Constructor.
	 * Ensure that we use the fabrik db model for the dbo
	 * @param	array	An optional associative array of configuration settings.
	 */

	public function __construct($config = array())
	{
		$config['dbo'] = FabriKWorker::getDbo(true);
		parent::__construct($config);
	}

	/**
	 * get an array of objects to populate the form filter dropdown
	 * @return array option objects
	 */

	function getFormOptions()
	{
		// Initialise variables.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('id AS value, label AS text');
		$query->from('#__{package}_forms')->where('published <> -2');
		$query->order('label ASC');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		return $rows;
	}

	public function getGroupOptions()
	{
		// Initialise variables.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$formid = $this->getState('filter.form');
		// Select the required fields from the table.
		$query->select('g.id AS value, g.name AS text');
		$query->from('#__{package}_groups AS g');

		$query->where('published <> -2');
		if ($formid !== '') {
			$query->join('INNER', '#__{package}_formgroup AS fg ON fg.group_id = g.id');
			$query->where('fg.form_id = '.(int)$formid);
		}
		$query->order('g.name ASC');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		return $rows;
	}

	/**
	 * build the part of the list query that deals with filtering by form
	 * @param object $query
	 * @param string $table
	 */

	function filterByFormQuery(&$query, $table)
	{
		$form = $this->getState('filter.form');
		if (!empty($form)) {
			$query->where($table.'.form_id = '. (int)$form);
		}
	}
}