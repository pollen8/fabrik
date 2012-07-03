<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       1.6
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
 * @package  Fabrik
 * @since    3.0
 */

class JFormFieldFormList extends JFormFieldList
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $name = 'Formlist';

	/**
	 * Method to get the field options.
	 *
	 * @return  array	The field option objects.
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
	 * @return  string	The field input markup.
	 */

	protected function getInput()
	{
		$option = JRequest::getCmd('option');
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
