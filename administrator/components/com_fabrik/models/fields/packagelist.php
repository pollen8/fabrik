<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

require_once(JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php');

/**
 * Renders a repeating drop down list of packages
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

class JFormFieldPackageList extends JFormFieldList

{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Packagelist';

	function getOptions()
	{
		$db	= FabrikWorker::getDbo();
		$query = $db->getQuery(true);
		$query->select("id AS value, CONCAT(label, '(', version , ')') AS " . FabrikString::safeColName(text));
		$query->from('#__{package}_packages');
		$query->order('value DESC');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$o = new stdClass();
		$o->value = 0;
		$o->text = JText::_('COM_FABRIK_NO_PACKAGE');
		array_unshift($rows, $o);
		return $rows;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */

	protected function getInput()
	{
		if ($this->element['active'] == 1)
		{
			 $this->element['readonly'] = '';
		}
		else
		{
			$this->element['readonly'] = 'true';
		}
		return parent::getInput();
	}

}