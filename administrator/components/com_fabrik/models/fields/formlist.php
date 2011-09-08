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

// needed for when you make a menu item link to a form.
require_once(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'helpers'.DS.'parent.php');
require_once(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'helpers'.DS.'string.php');
/**
 * Renders a repeating drop down list of forms
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

class JFormFieldFormList extends JFormFieldList

{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Formlist';

	function getOptions()
	{
		$db	= FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->select("id AS value, label AS ".FabrikString::safeColName('text'));
		$query->from("#__{package}_forms");
		$query->order("value DESC");
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$o = new stdClass();
		$o->value = '';
		$o->text = '';
		array_unshift($rows, $o);
		return $rows;
	}
	
	protected function getInput()
	{
		$db = FabrikWorker::getDbo();
		$query = $db->getQuery(true);
		$query->select('form_id')
		->from('#__{package}_formgroup')
		->where('group_id = '.(int)$this->form->getValue('id'));
		$db->setQuery($query);
		$this->value = $db->loadResult();
		return parent::getInput();
	}

}