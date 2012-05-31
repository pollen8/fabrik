<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_fabrik'.DS.'helpers'.DS.'element.php');

/**
 * Renders a list of groups
 *
 * @author 		Rob Clayburn
 * @package 	Joomla
 * @subpackage		Fabrik
 * @since		1.5
 */

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldGroupList extends JFormFieldList
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Grouplist';


	function getOptions()
	{

		if ($this->value == '') {
			$app =& JFactory::getApplication();
			$this->value = $app->getUserStateFromRequest('com_fabrik.elements.filter.group', 'filter_groupId', $this->value);
		}

		// Initialize variables.
		$options = array();

		$db		= FabrikWorker::getDbo(true);
		$query	= $db->getQuery(true);

		$query->select('id AS value, name AS text');
		$query->from('#__{package}_groups AS g');
		$query->where('published <> -2');
		$query->order('name');

		// Get the options.
		$db->setQuery($query);

		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->getErrorMsg());
		}

		array_unshift($options, JHtml::_('select.option', '', JText::_('COM_FABRIK_PLEASE_SELECT')));

		return $options;
	}

}