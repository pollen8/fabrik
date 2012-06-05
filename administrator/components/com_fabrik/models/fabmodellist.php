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
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('id AS value, label AS text');
		$query->from('#__{package}_forms')->where('published <> -2');
		$query->order('label ASC');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		return $rows;
	}
	
	/**
	 * get an array of objects to populate the package/apps dropdown list
	 * @since 3.0.5
	 * @return	array	value/text objects
	 */
	public function getPackageOptions()
	{
		// Initialise variables. Always use J db here no matter what package we are using
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		// Select the required fields from the table.
		$query->select('DISTINCT component_name AS value, label AS text');
		$query->from('#__fabrik_packages')->where('external_ref = 1');
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
			$query->where('fg.form_id = '.(int) $formid);
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
			$query->where($table.'.form_id = '. (int) $form);
		}
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
		$app = JFactory::getApplication('administrator');
		//Load the package state
		$package = $app->getUserStateFromRequest('com_fabrik.package', 'package', '');
		$this->setState('com_fabrik.package', $package);
		// List state information.
		parent::populateState($ordering, $direction);
	}
}