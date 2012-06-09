<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once(JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php');
require_once(JPATH_SITE . '/components/com_fabrik/helpers/parent.php');

/**
 * Renders a list of created visualizations
 *
 * @package 	Joomla
 * @subpackage	Articles
 * @since		1.5
 */

class JFormFieldVisaulizationlist extends JFormFieldList
{
	/**
	 * Element name
	 * @access	protected
	 * @var		string
	 */

	var	$_name = 'Visaulizationlist';

	function getOptions()
	{
		$a = array(JHTML::_('select.option', '', JText::_('COM_FABRIK_PLEASE_SELECT')));
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->select('id AS value, label AS text')->from('#__{package}_visualizations')->where('published =1')->order('text');
		$db->setQuery($query);
		$elementstypes = $db->loadObjectList();
		$elementstypes = array_merge($a, $elementstypes);
		return $elementstypes;
	}
}