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
 * Renders the form's database name or a field to create one
 *
 * @package 	Joomla.Framework
 * @subpackage	Fabrik
 * @since		1.5
 */

class JFormFieldFormDatabaseName extends JFormFieldText
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'FormDatabaseName';

	protected function getInput()
	{
		if ($this->form->getValue('record_in_database')) {
			$db = FabrikWorker::getDbo();
			$query = $db->getQuery(true);
			$id = (int)$this->form->getValue('id');
			$query->select('db_table_name')->from('#__{package}_lists')->where('form_id = '.$id);
			$db->setQuery($query);
			$this->element['readonly'] == true;
			$this->element['class'] = 'readonly';
			$this->value = $db->loadResult();
		}
		return parent::getInput();
	}

}
