<?php
/**
 * Fabrik upload helper
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Fabrik upload helper
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @since       3.0
 */

class FabrikUploader extends JObject
{

	/**
	 * Form model
	 *
	 * @var  object
	 */
	protected $form = null;

	/**
	 * Move uploaded file error
	 *
	 * @var  bool
	 */
	public $moveError = false;

	/**
	 * Upload
	 *
	 * @param   object  $formModel  form model
	 *
	 * @return  void
	 */

	public function __construct($formModel)
	{
		$this->form = $formModel;
	}

	/**
	 * Perform upload of files
	 *
	 * @return  bool true if error occured
	 */

	public function upload()
	{
		$groups = $this->form->getGroupsHiarachy();
		foreach ($groups as $groupModel)
		{
			$elementModels = $groupModel->getPublishedElements();
			foreach ($elementModels as $elementModel)
			{
				if ($elementModel->isUpload())
				{
					$elementModel->processUpload();
				}
			}
		}
	}

	/**
	 * Moves a file from one location to another
	 *
	 * @param   $pathFrom   string  file to move
	 * @param   $pathTo     string  location to move file to
	 * @param   $overwrite  bool  should we overwrite existing files
	 *
	 * @return  bool  do we overwrite any existing files found at pathTo?
	 */

	function move($pathFrom, $pathTo, $overwrite = true)
	{
		if (file_exists($pathTo))
		{
			if ($overwrite)
			{
				unlink($pathTo);
				$ok = rename($pathFrom, $pathTo);
			}
			else
			{
				$ok = false;
			}
		}
		else
		{
			$ok = rename($pathFrom, $pathTo);
		}
		return $ok;
	}

	/**
	 * Make a recursive folder structure
	 *
	 * @param   $folderPath  string   path to folder - eg /images/stories
	 * @param   $mode        hex      folder permissions
	 *
	 * @return  void
	 */

	function _makeRecursiveFolders($folderPath, $mode = 0755)
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
	 * Iterates through $_FILE data to see if any files have been uploaded
	 *
	 * @return  bool  true if files uploaded
	 */

	function check()
	{
		if (isset($_FILES) and !empty($_FILES))
		{
			foreach ($_FILES as $f)
			{
				if ($f['name'] != '')
				{
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Checks if the file can be uploaded
	 *
	 * @param   $file     array   File information
	 * @param   &$err     string  An error message to be returned
	 * @param   &$params  object  params
	 *
	 * @return  bool
	 */

	public static function canUpload($file, &$err, &$params)
	{
		if (empty($file['name']))
		{
			$err = 'Please input a file for upload';
			return false;
		}

		if (!is_uploaded_file($file['tmp_name']))
		{
			// Handle potential malicous attack
			$err = JText::_('File has not been uploaded');
			return false;
			;
		}

		jimport('joomla.filesystem.file');
		$format = JString::strtolower(JFile::getExt($file['name']));

		$allowable = explode(',', JString::strtolower($params->get('ul_file_types')));

		$format = FabrikString::ltrimword($format, '.');
		$format2 = ".$format";
		if (!in_array($format, $allowable) && !in_array($format2, $allowable))
		{
			$err = 'WARNFILETYPE';
			return false;
		}

		$maxSize = (int)$params->get('upload_maxsize', 0);
		if ($maxSize > 0 && (int)$file['size'] > $maxSize)
		{
			$err = 'WARNFILETOOLARGE';
			return false;
		}
		$ignored = array();
		$user = JFactory::getUser();
		$imginfo = null;
		if ($params->get('restrict_uploads', 1))
		{
			$images = explode(',', $params->get('image_extensions'));
			if (in_array($format, $images))
			{
				// If its an image run it through getimagesize
				if (($imginfo = getimagesize($file['tmp_name'])) === FALSE)
				{
					$err = 'WARNINVALIDIMG';
					return false;
				}
			}
			elseif (!in_array($format, $ignored))
			{
				// If its not an image...and we're not ignoring it
				/*$allowed_mime = explode(',', $upload_mime);
				$illegal_mime = explode(',', $upload_mime_illegal);
				if (function_exists('finfo_open') && $params->get('check_mime',1)) {
				    // We have fileinfo
				    $finfo = finfo_open(FILEINFO_MIME);
				    $type = finfo_file($finfo, $file['tmp_name']);
				    if (strlen($type) && !in_array($type, $allowed_mime) && in_array($type, $illegal_mime)) {
				        $err = 'WARNINVALIDMIME';
				        return false;
				    }
				    finfo_close($finfo);
				} elseif (function_exists('mime_content_type') && $params->get('check_mime',1)) {
				    // we have mime magic
				    $type = mime_content_type($file['tmp_name']);
				    if (strlen($type) && !in_array($type, $allowed_mime) && in_array($type, $illegal_mime)) {
				        $err = 'WARNINVALIDMIME';
				        return false;
				    }
				}*/
			}
		}

		$xss_check = JFile::read($file['tmp_name'], false, 256);
		$html_tags = array('abbr', 'acronym', 'address', 'applet', 'area', 'audioscope', 'base', 'basefont', 'bdo', 'bgsound', 'big', 'blackface',
			'blink', 'blockquote', 'body', 'bq', 'br', 'button', 'caption', 'center', 'cite', 'code', 'col', 'colgroup', 'comment', 'custom', 'dd',
			'del', 'dfn', 'dir', 'div', 'dl', 'dt', 'em', 'embed', 'fieldset', 'fn', 'font', 'form', 'frame', 'frameset', 'h1', 'h2', 'h3', 'h4',
			'h5', 'h6', 'head', 'hr', 'html', 'iframe', 'ilayer', 'img', 'input', 'ins', 'isindex', 'keygen', 'kbd', 'label', 'layer', 'legend',
			'li', 'limittext', 'link', 'listing', 'map', 'marquee', 'menu', 'meta', 'multicol', 'nobr', 'noembed', 'noframes', 'noscript',
			'nosmartquotes', 'object', 'ol', 'optgroup', 'option', 'param', 'plaintext', 'pre', 'rt', 'ruby', 's', 'samp', 'script', 'select',
			'server', 'shadow', 'sidebar', 'small', 'spacer', 'span', 'strike', 'strong', 'style', 'sub', 'sup', 'table', 'tbody', 'td', 'textarea',
			'tfoot', 'th', 'thead', 'title', 'tr', 'tt', 'ul', 'var', 'wbr', 'xml', 'xmp', '!DOCTYPE', '!--');
		foreach ($html_tags as $tag)
		{
			// A tag is '<tagname ', so we need to add < and a space or '<tagname>'
			if (JString::stristr($xss_check, '<' . $tag . ' ') || JString::stristr($xss_check, '<' . $tag . '>'))
			{
				$err = 'WARNIEXSS';
				return false;
			}
		}
		return true;
	}

	/**
	 * Recursive file name incrementation untill no file with exsiting name
	 * exists
	 *
	 * @param   $origFileName  string  intial file name
	 * @param   $newFileName   string  this recursions file name
	 * @param   $version       int     file version
	 *
	 * @return  string  new file name
	 */

	function incrementFileName($origFileName, $newFileName, $version)
	{
		if (JFile::exists($newFileName))
		{
			$bits = explode('.', $newFileName);
			$ext = array_pop($bits);
			$f = implode('.', $bits);
			$f = JString::rtrim($f, $version - 1);
			$newFileName = $f . $version . "." . $ext;
			$version++;
			$newFileName = FabrikUploader::incrementFileName($origFileName, $newFileName, $version);
		}
		return $newFileName;
	}

}
