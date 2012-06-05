<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Plugin List Field class for Fabrik.
 *
 * @package		Joomla.Framework
 * @subpackage	com_fabrik
 * @since		1.6
 */
class JFormFieldPluginList extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'PluginList';

	/**
	 * Method to get the field options.
	 *
	 * @return	array	The field option objects.
	 * @since	1.6
	 */
	public function getOptions()
	{
		$group = (string) $this->element['plugin'];
		$key = $this->element['key'];
		$key = ($key == 'visualization.plugin') ? "CONCAT('visualization.',element) " : 'element';
		// Initialize variables.
		$options = array();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($key . ' AS value, element AS text');
		$query->from('#__extensions AS p');
		$query->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));
		$query->where($db->quoteName('enabled') . ' = 1 AND state != -1');
		$query->where($db->quoteName('folder') . ' = ' . $db->quote($group));
		$query->order('text');

		// Get the options.
		$db->setQuery($query);
		$options = $db->loadObjectList();
		// Check for a database error.
		if ($db->getErrorNum())
		{
			JError::raiseWarning(500, $db->getErrorMsg());
		}
		array_unshift($options, JHtml::_('select.option', '', JText::_('COM_FABRIK_PLEASE_SELECT')));
		return $options;
	}
}
?>