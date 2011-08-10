<?php
/*
 * @package Joomla.Administrator
 * @subpackage Fabrik
 * @since		1.6
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Package controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_fabrik
 * @since		1.6
 */
class FabrikControllerPackage extends JControllerForm
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_PACKAGE';

	/**
	 * Constructor
	 */

	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	function dolist()
	{
		$db = FabrikWorker::getDbo();
		$query = $db->getQuery(true);
		$list = JRequest::getCmd('list', 'form');
		$selected = JRequest::getVar('selected');
		$query->select('id, label')->from('#__fabrik_'.$list.'s');
		if ($selected != '') {
			$query->where('id NOT IN ('.$selected.')');
		}
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		echo "<ul id=\"$list-additems\">";
		if (empty($rows)) {
			echo "<li>".JText::sprintf('COM_FABRIK_NO_FREE_ITEMS_FOUND')."</li>";
		} else {
			foreach ($rows as $row) {
				echo "<li><a href=\"#\" id=\"$row->id\">$row->label</a>";
			}
		}
		echo "</ul>";

		$script = "$('$list-additems').getElements('a').addEvent('click', function(e){
			window.fireEvent('fabrik.package.item.selected', [e]);
		});";
		FabrikHelperHTML::addScriptDeclaration($script);
	}

}
