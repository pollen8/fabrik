<?php
/**
 * Renders a repeating drop down list of forms
 *
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php';

// Needed for when you make a menu item link to a form.
require_once JPATH_SITE . '/components/com_fabrik/helpers/parent.php';
require_once JPATH_SITE . '/components/com_fabrik/helpers/string.php';

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Renders a repeating drop down list of forms
 *
 * @package     Joomla
 * @subpackage  Form
 * @since       1.6
 */

class JFormFieldFormList extends JFormFieldList
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var $_name = 'Formlist';

	/**
	 * Get list options
	 *
	 * @return  array
	 */

	protected function getOptions()
	{
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->select('id AS value, label AS ' . $db->quote('text') . ', published');
		$query->from('#__{package}_forms');
		if (!$this->element['showtrashed'])
		{
			$query->where('published <> -2');
		}
		$query->order('published DESC, label ASC');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		foreach ($rows as &$row)
		{
			switch ($row->published)
			{
				case '0':
					$row->text .= ' [' . JText::_('JUNPUBLISHED') . ']';
					break;
				case '-2':
					$row->text .= ' [' . JText::_('JTRASHED') . ']';
					break;
			}
		}
		$o = new stdClass;
		$o->value = '';
		$o->text = '';
		array_unshift($rows, $o);

		return $rows;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 */

	protected function getInput()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$option = $input->get('option');
		if (!in_array($option, array('com_modules', 'com_menus', 'com_advancedmodules')))
		{
			$db = FabrikWorker::getDbo(true);
			$query = $db->getQuery(true);
			$query->select('form_id')->from('#__{package}_formgroup')->where('group_id = ' . (int) $this->form->getValue('id'));
			$db->setQuery($query);
			$this->value = $db->loadResult();
			$this->form->setValue('form', null, $this->value);
		}
		return parent::getInput();
	}

}
