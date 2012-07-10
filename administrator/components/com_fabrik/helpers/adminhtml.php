<?php

/**
* @package Joomla
* @subpackage Fabrik.helpers
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/


// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Content Component HTML Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Fabrik.helpers
 * @since 1.5
 */
class FabrikHelperAdminHTML
{

	/**
	 * get a list of directories
	 * @param	string	path to read from
	 * @param	bool	return full paths or not
	 */

	function fabrikListDirs($path, $fullpath = false)
	{
		$arr = array();
		if (!@is_dir($path))
		{
			return $arr;
		}
		$handle = opendir($path);
		while ($file = readdir($handle))
		{
			$dir =  JPath::clean($path.'/'.$file);
			$isDir = is_dir($dir);
			if (($file != ".") && ($file != "..") && ($file != ".svn"))
			{
				if ($isDir)
				{
					if ($fullpath)
					{
						$arr[] = trim( JPath::clean($path . '/' . $file));
					}
					else
					{
						$arr[] = trim($file);
					}
				}
			}
		}
		closedir($handle);
		asort($arr);
		return $arr;
	}

}
?>