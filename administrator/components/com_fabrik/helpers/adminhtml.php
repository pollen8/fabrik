<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       1.6
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Content Component HTML Helper
 *
 * @package  Fabrik
 * @since    3.0
 */

class FabrikHelperAdminHTML
{

	/**
	 * get a list of directories
	 * 
	 * @param   string  $path      to read from
	 * @param   bool    $fullpath  return full paths or not
	 * 
	 * @return  null
	 */

	public function fabrikListDirs($path, $fullpath = false)
	{
		$arr = array();
		if (!@is_dir($path))
		{
			return $arr;
		}
		$handle = opendir($path);
		while ($file = readdir($handle))
		{
			$dir = JPath::clean($path . '/' . $file);
			$isDir = is_dir($dir);
			if (($file != ".") && ($file != "..") && ($file != ".svn"))
			{
				if ($isDir)
				{
					if ($fullpath)
					{
						$arr[] = trim(JPath::clean($path . '/' . $file));
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
