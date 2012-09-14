<?php
/**
 * Renders a list of Fabrik visualizations
 *
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       1.6
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php';
require_once JPATH_SITE . '/components/com_fabrik/helpers/parent.php';

/**
 * Renders a list of Fabrik visualizations
 *
 * @package     Joomla
 * @subpackage  Form
 * @since       1.6
 */

class JFormFieldVisaulizationlist extends JFormFieldList
{
	/**
	 * Element name
	 * @access	protected
	 * @var		string
	 */

	var $_name = 'Visaulizationlist';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */

	protected function getOptions()
	{
		$a = array(JHTML::_('select.option', '', JText::_('COM_FABRIK_PLEASE_SELECT')));
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->select('id AS value, label AS text')->from('#__{package}_visualizations')->where('published = 1')->order('text');
		$db->setQuery($query);
		$elementstypes = $db->loadObjectList();
		$elementstypes = array_merge($a, $elementstypes);
		return $elementstypes;
	}
}
