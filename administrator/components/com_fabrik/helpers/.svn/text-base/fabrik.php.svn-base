<?php

/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Fabrik Component Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Fabrik
 * @since 1.5
 */
class FabrikHelper
{

	/**
	 * prepare the date for saving
	 * DATES SHOULD BE SAVED AS UTC
	 * @param string publish down date
	 */

	function prepareSaveDate(&$strdate)
	{
		$config =& JFactory::getConfig();
		$tzoffset = $config->getValue('config.offset');
		$db =& FabrikWorker::getDbo();
		// Handle never unpublish date
		if (trim($strdate) == JText::_('Never') || trim($strdate) == '' || trim($strdate) == $db->getNullDate())
		{
			$strdate = $db->getNullDate();
		}
		else
		{
			if (strlen(trim($strdate )) <= 10) {
				$strdate .= ' 00:00:00';
			}
			$date =& JFactory::getDate($strdate, $tzoffset);
			$strdate = $date->toMySQL();
		}
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param	int		The category ID.
	 *
	 * @return	JObject
	 * @since	1.6
	 */
	public static function getActions($categoryId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($categoryId)) {
			$assetName = 'com_fabrik';
		} else {
			$assetName = 'com_fabrik.category.'.(int)$categoryId;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}

/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 *
	 * @return	void
	 * @since	1.6
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('COM_FABRIK_SUBMENU_LISTS'),
			'index.php?option=com_fabrik&view=lists',
			$vName == 'lists'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_FABRIK_SUBMENU_FORMS'),
			'index.php?option=com_fabrik&view=forms',
			$vName == 'forms'
		);

		JSubMenuHelper::addEntry(
			JText::_('COM_FABRIK_SUBMENU_GROUPS'),
			'index.php?option=com_fabrik&view=groups',
			$vName == 'groups'
		);

		JSubMenuHelper::addEntry(
			JText::_('COM_FABRIK_SUBMENU_ELEMENTS'),
			'index.php?option=com_fabrik&view=elements',
			$vName == 'elements'
		);

		JSubMenuHelper::addEntry(
			JText::_('COM_FABRIK_SUBMENU_VISUALIZATIONS'),
			'index.php?option=com_fabrik&view=visualizations',
			$vName == 'visualizations'
		);

		JSubMenuHelper::addEntry(
			JText::_('COM_FABRIK_SUBMENU_PACKAGES'),
			'index.php?option=com_fabrik&view=packages',
			$vName == 'packages'
		);

		JSubMenuHelper::addEntry(
			JText::_('COM_FABRIK_SUBMENU_CONNECTIONS'),
			'index.php?option=com_fabrik&view=connections',
			$vName == 'connections'
		);

		JSubMenuHelper::addEntry(
			JText::_('COM_FABRIK_SUBMENU_CRONS'),
			'index.php?option=com_fabrik&view=crons',
			$vName == 'crons'
		);

	}
}
?>