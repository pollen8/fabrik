<?php
/**
* @version		$Id:sql.php 6961 2007-03-15 16:06:53Z tcp $
* @package		Joomla.Framework
* @subpackage	Parameter
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_fabrik'.DS.'helpers'.DS.'element.php');

/**
 * Renders a SQL element
 *
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class JFormFieldSQL2 extends JFormFieldList
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'SQL';

	function getOptions()
	{
		$db	= FabrikWorker::getDbo();
		$db->setQuery($this->element['query']);
		//$this->name = explode('[', $this->name);
		//$this->name = str_replace(']', '', $this->name[2]);
		$key = $this->element['key_field'] ? $this->element['key_field'] : 'value';
		$val = $this->element['value_field'] ? $this->element['value_field'] : $this->name;
		if ($this->element['add_select']) {
		  $rows = array(JHTML::_( 'select.option', '', '- '.JText::_('Do not use').' -'));
		  $rows = array_merge($rows, (array)$db->loadObjectList());
		} else {
		   $rows = $db->loadObjectList();
		}
		return $rows;
	}
}
