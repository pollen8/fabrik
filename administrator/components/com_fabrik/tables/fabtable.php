<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Pollen 8 Design Ltd. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class FabTable extends JTable
{

	/**
	 * Static method to get an instance of a JTable class if it can be found in
	 * the table include paths.  To add include paths for searching for JTable
	 * classes @see JTable::addIncludePath().
	 *
	 * @param	string	The type (name) of the JTable class to get an instance of.
	 * @param	string	An optional prefix for the table class name.
	 * @param	array	An optional array of configuration values for the JTable object.
	 * @return	mixed	A JTable object if found or boolean false if one could not be found.
	 * @since	1.5
	 * @link	http://docs.joomla.org/JTable/getInstance
	 */

	public static function getInstance($type, $prefix = 'JTable', $config = array())
	{
		$config['dbo'] = FabriKWorker::getDbo();
		return parent::getInstance($type, $prefix, $config);
	}

}
?>