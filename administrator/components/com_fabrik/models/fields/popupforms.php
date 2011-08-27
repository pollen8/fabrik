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
 * Renders a list releated forms that a db join element can be populated from
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

class JFormFieldPopupforms extends JFormFieldList
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Connections';


	function getOptions()
	{
		// Initialize variables.
		$options = array();

		$db	= FabrikWorker::getDbo();
		$query	= $db->getQuery(true);
		$query->select('f.id AS value, f.label AS text, l.id AS listid');
		$query->from('#__fabrik_forms AS f');
		$query->join('LEFT', '#__fabrik_lists As l ON f.id = l.form_id');
		$query->where('f.published = 1 AND l.db_table_name =' . $db->Quote($this->form->getValue('params.join_db_name')));
		$query->order('f.label');

		// Get the options.
		$db->setQuery($query);

		$options = $db->loadObjectList('value');

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->getErrorMsg());
		}
		if (empty($options)) {
			$options[] = JHTML::_('select.option', '', JText::_('COM_FABRIK_NO_POPUP_FORMS_AVAILABLE'));
		} else {
			array_unshift($options, JHtml::_('select.option', '', JText::_('COM_FABRIK_PLEASE_SELECT')));
		}

		return $options;
	}


}