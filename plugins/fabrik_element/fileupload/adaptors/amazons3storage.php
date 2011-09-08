<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once(JPATH_ROOT.DS.'plugins'.DS.'fabrik_element'.DS.'fileupload'.DS.'adaptor.php');

class amazons3storage extends storageAdaptor{


	var $ssl = false;
	var $domain = 's3.amazonaws.com/'; //must have trailing slash

	function __construct($params)
	{
		require_once(COM_FABRIK_FRONTEND.DS.'libs'.DS.'amazons3'.DS.'S3.php');
		$this->ssl = $params->get('fileupload_ssl', false);
		$this->s3 = new S3($params->get('fileupload_aws_accesskey'), $params->get('fileupload_aws_secretkey'), $this->ssl);
		parent::__construct($params);
	}

	function getBucketName()
	{
		$params = $this->getParams();
		return $params->get('fileupload_aws_bucketname', 'robclayburnsfabrik');
	}

	function getLocation()
	{
		$loc = $this->params->get('fileupload_aws_location');
		return $loc == '' ? false : $loc;
	}

	/**
	 * does a file exist
	 * @param $filepath
	 * @return unknown_type
	 */

	function exists($filepath)
	{
		if (!$this->bucketExists()) {
			return false;
		}
		$bucket = $this->getBucketName();
		$filepath = str_replace("%20", " ", $filepath);
		$filepath = $this->urlToPath($filepath);
		$response = $this->s3->getObject($bucket, $filepath);
		return $response === false ? false : true;
	}

	function urlToPath($filepath)
	{
		$prefix = $this->ssl ? 'https://' : 'http://';
		$bucket = $this->getBucketName();
		$filepath = $this->removePrependedURL($filepath);
		if (strstr($filepath, $prefix)) {
			//we've got the full url to the image - remove the bucket name etc to get file name
			$filepath = str_replace($prefix, '', $filepath);
			$filepath = str_replace($bucket.'.'.$this->domain, '', $filepath);
		}
		return $filepath;
	}

	private function removePrependedURL($filepath)
	{
		if (substr($filepath, 0, strlen(COM_FABRIK_BASE)) == COM_FABRIK_BASE) {
			$filepath = Fabrikstring::ltrimword($filepath, COM_FABRIK_BASE);
		}
		return $filepath;
	}

	private function bucketExists()
	{
		$bucket = $this->getBucketName();
		$buckets = $this->s3->listBuckets();
		if (!is_array($buckets)) {
			return false;
		}
		if (!in_array($bucket, $buckets)) {
			return false;
		}
		return true;
	}

	function upload($tmpFile, $filepath)
	{
		$filepath = str_replace("\\", '/', $filepath);
		$bucket = $this->getBucketName();
		if (!$this->bucketExists())
		{
			$this->s3->putBucket( $bucket, S3::ACL_PUBLIC_READ, $this->getLocation());
		}
		// $$$ rob avoid urls like http://bucket.s3.amazonaws.com//home/users/path/to/file/Chrysanthemum.jpg
		$filepath = ltrim($filepath, '/');
		//move the file
		if ($this->s3->putObjectFile( $tmpFile, $bucket, $filepath, S3::ACL_PUBLIC_READ)) {

			$this->uploadedFilePath = $this->getS3BaseURL() . str_replace(" ", "%20", $filepath);
			return true;
		}else{
			return false;
		}
	}

	private function getS3BaseURL()
	{
		$prefix = $this->ssl ? 'https://' : 'http://';
		$bucket = $this->getBucketName();
		return $prefix.$bucket.'.'.$this->domain;
	}

	function write($file, $buffer)
	{
		$file = $this->urlToPath($file);
		$file = str_replace("%20", " ", $file);
		$file = str_replace("\\", '/', $file);
		$bucket = $this->getBucketName();
		if ($this->s3->putObject($buffer, $bucket, $file, S3::ACL_PUBLIC_READ)) {
			return true;
		}else{
			return false;
		}
	}

	/**
	 * does a folder exist
	 * @param $folder
	 * @return unknown_type
	 */

	function folderExists($path)
	{
		//not applicable
	}

	/**
	 * create a folder
	 * @param $path
	 * @return unknown_type
	 */

	function createFolder($path)
	{
		//not applicable
		return true;
	}

	function clean($path)
	{
		$prefix = $this->ssl ? 'https://' : 'http://';
		if (strstr($path, $prefix)) {
			//if we are cleaning up a full url then check that fabrik hasnt unwittingly prepended the JPATH_SITE to the start of the url
			$path = $this->removePrependedURL( $path);
			$part = Fabrikstring::ltrimword($path, $prefix);
			$path = $prefix . JPath::clean($part);
		} else {
			$path =  JPath::clean($path);
		}
		$path = str_replace("\\", '/', $path);
		return $path;
	}

	function cleanName($filename, $repeatGroupCounter)
	{
		// $$$peamak: add random filename
		$params = $this->getParams();
		if ($params->get('random_filename') == 1) {
	 				$length = $params->get('length_random_filename');
					$key = "";
					$possible = "0123456789bcdfghjkmnpqrstvwxyzBCDFGHJKLMNPQRTVWXYZ";
					$i = 0;
						while ($i < $length) {
							$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
							$key .= $char;
							$i++;
						}
				$file_e = JFile::getExt($filename);
				$file_f = preg_replace('/.'.$file_e.'$/', '', $filename);
				$filename = $file_f.'_'.$key.'.'.$file_e;

		}
		return $filename;
	}

	function delete($filepath)
	{
		$filepath = $this->urlToPath($filepath);
		$this->s3->deleteObject($this->getBucketName(), $filepath);
	}



	function setPermissions($filepath)
	{
		//not applicable
	}

	function getFileUrl($file)
	{
		return $file;
	}

	/**
	 * get the thumbnail file for the file given
	 *
	 * @param string $file
	 * @return string thumbnail
	 */

	function _getThumb($file)
	{
		$params = $this->getParams();
		$w = new FabrikWorker();

		$ulDir = COM_FABRIK_BASE.$params->get('ul_directory');
		$ulDir = $this->clean($ulDir);
		$ulDir = $w->parseMessageForPlaceHolder($ulDir);

		$thumbdir = $this->clean(COM_FABRIK_BASE.$params->get('thumb_dir'));
		$thumbdir = $w->parseMessageForPlaceHolder($thumbdir);

		$file = $w->parseMessageForPlaceHolder($file);
		$file = str_replace($ulDir, $thumbdir, $file);

		$f = basename($file);
		$dir = dirname($file);
		$file = $dir . '/' . $params->get('thumb_prefix') .  $f;
		return $file;
	}

	/**
	 * get the cropped file for the file given
	 *
	 * @param string $file
	 * @return string cropped image
	 */

	function _getCropped( $file )
	{
		$params = $this->getParams();
		$w = new FabrikWorker();

		$ulDir = COM_FABRIK_BASE.$params->get('ul_directory');
		$ulDir = $this->clean($ulDir);
		$ulDir = $w->parseMessageForPlaceHolder($ulDir);

		$thumbdir = $this->clean(COM_FABRIK_BASE.$params->get('fileupload_crop_dir'));
		$thumbdir = $w->parseMessageForPlaceHolder($thumbdir );

		$file = $w->parseMessageForPlaceHolder($file);
		$file = str_replace($ulDir, $thumbdir, $file);

		$f = basename($file);
		$dir = dirname($file);
		$file = $dir . '/' . $f;
		return $file;
	}

	/**
	 * convert a full server path into a full url
	 */
	function pathToURL($path)
	{
		return $path;
	}

	/**
	 * @access public
	 * @param string path to folder - eg /images/stories
	 */

	function makeRecursiveFolders($folderPath, $mode = 0755)
	{
		//not applicable
		return;
	}
}

?>