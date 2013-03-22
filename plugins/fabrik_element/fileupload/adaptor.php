<?php
/**
 * Abstract Storage adaptor for Fabrik file upload element
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Abstract Storage adaptor for Fabrik file upload element
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       3.0
 */

abstract class FabrikStorageAdaptor
{

	/**
	 * Path or url to uploaded file
	 *
	 * @var string
	 */
	protected $uploadedFilePath = null;

	/**
	 * Constructor
	 *
	 * @param   JRegistry  &$params  options
	 */

	public function __construct(&$params)
	{
		$this->params = $params;
	}

	/**
	 * Get params
	 *
	 * @return  JRegistry
	 */

	public function &getParams()
	{
		return $this->params;
	}

	/**
	 * Get the uploaded file path
	 *
	 * @return  string
	 */
	public function getUploadedFilePath()
	{
		return $this->uploadedFilePath;
	}

	/**
	 * Does a file exist
	 *
	 * @param   string  $filepath  file path to test
	 *
	 * @return bool
	 */

	public abstract function exists($filepath);

	/**
	 * Does a folder exist
	 *
	 * @param   string  $path  folder path to test
	 *
	 * @return bool
	 */

	public abstract function folderExists($path);

	/**
	 * Create a folder
	 *
	 * @param   string  $path  folder path
	 *
	 * @return bool
	 */

	public abstract function createFolder($path);

	/**
	 * Write a file
	 *
	 * @param   string  $file    file name
	 * @param   string  $buffer  the buffer to write
	 *
	 * @return  void
	 */

	public abstract function write($file, $buffer);

	/**
	 * Read a file
	 *
	 * @param   string  $filepath  file path
	 *
	 * @return  mixed  Returns file contents or boolean False if failed
	 */

	public abstract function read($filepath);

	/**
	 * Clean the file path
	 *
	 * @param   string  $path  path to clean
	 *
	 * @return  string  cleaned path
	 */

	public abstract function clean($path);

	/**
	 * Clean a fle name
	 *
	 * @param   string  $filename       file name to clean
	 * @param   int     $repeatCounter  repeat group counter
	 *
	 * @return  string  cleaned name
	 */

	public abstract function cleanName($filename, $repeatCounter);

	/**
	 * Delete a file
	 *
	 * @param   string  $filepath  file to delete
	 *
	 * @return  void
	 */

	public abstract function delete($filepath);

	/**
	 * Moves an uploaded file to a destination folder
	 *
	 * @param   string  $tmpFile   The name of the php (temporary) uploaded file
	 * @param   string  $filepath  The path (including filename) to move the uploaded file to
	 *
	 * @return  boolean True on success
	 */

	public abstract function upload($tmpFile, $filepath);

	/**
	 * Set a file's permissions
	 *
	 * @param   string  $filepath  file to set permissions for
	 *
	 * @return  string
	 */

	public abstract function setPermissions($filepath);

	/**
	 * Convert a full url into a full server path
	 *
	 * @param   string  $url  URL
	 *
	 * @return string  path
	 */

	public function urlToPath($url)
	{
		return $url;
	}

	/**
	 * Do a final transform on the path name
	 *
	 * @param   string  &$filepath  path to parse
	 *
	 * @return  void
	 */

	public function finalFilePathParse(&$filepath)
	{

	}

	/**
	 * Convert a full server path into a full url
	 *
	 * @param   string  $path  server path
	 *
	 * @return  string  url
	 */

	public function pathToURL($path)
	{
		$path = str_replace(COM_FABRIK_BASE, '', $path);
		$path = FabrikString::ltrimiword($path, '/');
		$path = COM_FABRIK_LIVESITE . $path;
		$path = str_replace('\\', '/', $path);
		
		// Some servers do not like double slashes in the URL.
		$path = str_replace('\/\/', '/', $path);
		return $path;
	}

	/**
	 * Make recursive folders
	 *
	 * @param   string   $folderPath  path to folder - eg /images/stories
	 * @param   bitmask  $mode        permissions
	 *
	 *  @return  mixed JError|void
	 */

	public function makeRecursiveFolders($folderPath, $mode = 0755)
	{
		if (!JFolder::exists($folderPath))
		{
			if (!JFolder::create($folderPath, $mode))
			{
				return JError::raiseError(21, "Could not make dir $folderPath ");
			}
		}
	}

	/**
	 * Get the complete folder path, including the server root
	 *
	 * @param   string  $filepath  the file path
	 *
	 * @return  string
	 */

	public abstract function getFullPath($filepath);

	/**
	 * Allows storage model to modify pathname just before it is rendered.  For instance,
	 * if using Amazon S3 with 'Authenticated URL' option.
	 *
	 * @param   string  $filepath  path to file
	 *
	 * @return  string
	 */

	public function preRenderPath($filepath)
	{
		return $filepath;
	}

	/**
	 * When creating file paths, do we need to append them with JPATH_SITE
	 *
	 * @since  3.0.6.2
	 *
	 * @return  bool
	 */

	public function appendServerPath()
	{
		return true;
	}
}
