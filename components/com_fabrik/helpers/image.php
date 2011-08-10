<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

/* MOS Intruder Alerts */
defined('_JEXEC') or die();

/**
 *	image manipulation class
 * @author Rob Clayburn <rob@pollen-8.co.uk>
 * @version $Revision: 1.0 $
 * @since 1.0
 * @package pollen8
 * @access public
 * @copyright Pollen 8 design Ltd
 */

class imageHelper
{

	/** @var object image manipulation lib, sepecific to library */
	var $_lib = null;

	function getLibs()
	{
		$libs = array();
		$gds = imageHelper::_testGD();
		foreach ($gds as $key=>$val) {
			$libs[] = JHTML::_('select.option',$key, $val);
		}
		$im = imageHelper::_testImagemagick();
		foreach ($im as $key=>$val) {
			$libs[] = JHTML::_('select.option',$key, $val);
		}
		return $libs;
	}

	/**
	 * load in the correct image library
	 *
	 * @param string image lib to load
	 * @return object image lib
	 */

	function loadLib($lib)
	{
		$class = "image" . $lib;
		if (class_exists($class)) {
			return new $class();
		} else {
			return JError::raiseError(500, "can't load class: $class");
		}
	}

	function _testGD()
	{
		$gd = array();
		$GDfuncList = get_extension_funcs('gd');
		ob_start();
		@phpinfo(INFO_MODULES);
		$output=ob_get_contents();
		ob_end_clean();
		$matches[1]='';
		if (preg_match("/GD Version[ \t]*(<[^>]+>[ \t]*)+([^<>]+)/s", $output, $matches)) {
			$gdversion = $matches[2];
		}
		if (function_exists('imagecreatetruecolor') && function_exists('imagecreatefromjpeg')) {
			$gd['gd2'] = "GD: " . $gdversion;
		} elseif (function_exists('imagecreatefromjpeg')) {
			$gd['gd1'] = "GD: " . $gdversion;
		}
		return $gd;
	}

	function _testImagemagick()
	{
		if (function_exists("NewMagickWand")) {
			$im["IM"] = "Magick wand";
		} else {
			$status = '';
			$output = array();
			@exec('convert -version', $output, $status);
			$im = array();
			if (!$status) {
				if (preg_match("/imagemagick[ \t]+([0-9\.]+)/i",$output[0],$matches))
				$im["IM"] = $matches[0];
			}
			unset($output, $status);
		}
		return $im;
	}
}

class image
{
	var $_thumbPath = null;

	/**@var object storage class file/amazons3 etc*/
	var $storage = null;

	/**
	 * set the filesystem storage manager
	 * @param object $storage
	 */

	function setStorage(&$storage)
	{
		$this->storage =& $storage;
	}

	function GetImgType($filename)
	{
		$info = getimagesize( $filename);
		switch ($info[2]) {
			case 1:
				return "gif";
				break;
			case 2:
				return "jpg";
				break;
			case 3:
				return "png";
				break;
			default:
				return false;
		}
	}

	function resize($maxWidth, $maxHeight, $origFile, $destFile)
	{
		echo "this should be overwritten in the library class";
	}
}

class imageGD extends image
{

	function imageFromFile($file)
	{
		$ext = strtolower(end(explode('.', $file)));
		if ($ext == 'jpg' || $ext == 'jpeg') {
			$img = @imagecreatefromjpeg($file);
			$header = "image/jpeg";
		} else if ($ext == 'png') {
			$img = @imagecreatefrompng($file);
			$header = "image/png";
			/* Only if your version of GD includes GIF support*/
		} else if ($ext == 'gif') {
			if (function_exists( imagecreatefromgif )) {
				$img = @imagecreatefromgif( $file );
				$header = "image/gif";
			} else {
				$img = JError::raiseWarning(21,"imagecreate from gif not available");
			}
		}
		return array($img, $header);
	}

	/**

	 * Enter description here ...
	 * @param string $source
	 * @param string $dest
	 * @param double $degrees
	 * @return array(image object, rotated images width, rotated images height)
	 */
	public function rotate($source, $dest = '', $degrees = 0)
	{
		$source = imagecreatefromjpeg($source);
		// Rotates the image
		$rotate = imagerotate($source, $degrees, 0);

		if ($dest != '') {
			ob_start();
			imagejpeg($rotate, "", 100);
			$rotate = ob_get_contents();
			ob_end_clean();
			JFile::write($dest, $rotate);
			list($width, $height) = getimagesize($dest);
		}
		return array($rotate, $width, $height);
	}

	function scale($file, $dest = '', $percentage = 100, $destX = 0, $destY = 0)
	{
		list($image, $header) = $this->imageFromFile($file);

		jimport('joomla.filesystem.file');

		list($width, $height) = getimagesize($file);

		$new_width = $width * ((float)$percentage/100);
		$new_height = $height * ((float)$percentage/100);
		$image_p = imagecreatetruecolor($new_width, $new_height);
		imagecopyresampled($image_p, $image, $destX, $destY, 0, 0, $new_width, $new_height, $width, $height);

		if ($dest != '') {
			ob_start();
			imagejpeg($image_p, "", 100);
			$image_p = ob_get_contents();
			ob_end_clean();
			JFile::write($dest, $image_p);
		}
		return $image_p;
	}

	/**
	 * resize an image to a specific width/height using standard php gd graphics lib
	 * @param int maximum image Width (px)
	 * @param int maximum image Height (px)
	 * @param string current images folder pathe (must have trailing end slash)
	 * @param string destination folder path for resized image (must have trailing end slash)
	 * @param string file name of the image to resize
	 * @param bol save the resized image
	 */

	function resize($maxWidth, $maxHeight, $origFile, $destFile)
	{
		/* check if the file exists*/
		if (!$this->storage->exists($origFile)) {
			return JError::raiseError(500, "no file found for $origFile");
		}
		/* Load image*/
		list($img, $header) = $this->imageFromFile($origFile);
		if (JError::isError($img)){
			return $img;
		}
		$ext = strtolower(end(explode('.', $origFile)));
		/*if ($ext == 'jpg' || $ext == 'jpeg') {
			$img = @imagecreatefromjpeg($origFile);
			$header = "image/jpeg";
			} else if ($ext == 'png') {
			$img = @imagecreatefrompng($origFile);
			$header = "image/png";
			// Only if your version of GD includes GIF support
			} else if ($ext == 'gif') {
			if (function_exists(imagecreatefromgif)) {
			$img = @imagecreatefromgif( $origFile);
			$header = "image/gif";
			} else {
			return JError::raiseWarning(21,"imagecreate from gif not available");
			}
			}*/
		/* If an image was successfully loaded, test the image for size*/
		if ($img) {
			/* Get image size and scale ratio*/
			$width = imagesx($img);
			$height = imagesy($img);
			$scale = min($maxWidth / $width, $maxHeight / $height);
			/* If the image is larger than the max shrink it*/
			if ($scale < 1) {
				$new_width = floor($scale * $width);
				$new_height = floor($scale * $height);
				/* Create a new temporary image*/
				$tmp_img = imagecreatetruecolor($new_width, $new_height);
				/* Copy and resize old image into new image*/
				imagecopyresampled($tmp_img, $img, 0, 0, 0, 0,
				$new_width, $new_height, $width, $height);
				imagedestroy($img);
				$img = $tmp_img;
			}
		}
		/* Create error image if necessary*/
		if (!$img) {
			return JError::raiseWarning(21, "resize: no image created for $origFile, extension = $ext, destination = $destFile  ");
		}
		/* save the file */
		$this->writeImg($img, $destFile, $header);

		$this->_thumbPath = $destFile;
	}

	/**
	 *
	 * Crop an image to specific dimensions
	 * @param string path to image to crop from
	 * @param string path to cropped file
	 * @param int x coord on $origFile to start crop from
	 * @param int y coord on $origFile to start crop from
	 * @param int cropped image width
	 * @param int cropped image height
	 * @param int destination x coord of destination point
	 * @param int destination y coord of destination point
	 */

	public function crop($origFile, $destFile, $srcX, $srcY, $dstW, $dstH, $dstX = 0, $dstY = 0)
	{
		list($origImg, $header) = $this->imageFromFile($origFile);
		$destImg = imagecreatetruecolor($dstW, $dstH);
		$srcW = imagesx($destImg);
		$srcH = imagesy($destImg);
		imagecopyresampled($destImg, $origImg, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
		$this->writeImg($destImg, $destFile, $header);
	}

	/**
	 * write an image to the server
	 * @param object $img
	 * @param string $destFile
	 * @param string image type $header
	 */

	function writeImg($img, $destFile, $header)
	{
		if ($header == "image/jpeg") {
			ob_start();
			imagejpeg( $img, "", 100);
			$image = ob_get_contents();
			ob_end_clean();
			$this->storage->write($destFile, $image);
		} else {
			if ($header == "image/png") {
				ob_start();
				imagepng($img, "", 0);
				$image = ob_get_contents();
				ob_end_clean();
				$this->storage->write($destFile, $image);
			} else {
				if (function_exists("imagegif")) {
					ob_start();
					imagegif($img, "", 100);
					$image = ob_get_contents();
					ob_end_clean();
					$this->storage->write($destFile, $image);
				} else {
					/* try using imagemagick to convert gif to png:*/
					$image_file = imgkConvertImage($image_file,$baseDir,$destDir, ".png");
				}
			}
		}
	}
}

class imageGD2 extends imageGD
{

	/**
	 * resize an image to a specific width/height using standard php gd graphics lib
	 * @param int maximum image Width (px)
	 * @param int maximum image Height (px)
	 * @param string current images folder pathe (must have trailing end slash)
	 * @param string destination folder path for resized image (must have trailing end slash)
	 * @param string file name of the image to resize
	 * @param bol save the resized image
	 * @return object? image
	 *
	 */
	function resize($maxWidth, $maxHeight, $origFile, $destFile)
	{

		/* check if the file exists*/
		if (!$this->storage->exists($origFile)) {
			return JError::raiseError(500, "no file found for $origFile");
		}

		/* Load image*/
		$img = null;
		$ext = $this->GetImgType($origFile);
		if(!$ext) {
			return;
		}
		ini_set('display_errors', true);
		$memory = ini_get('memory_limit');
		$intmemory = FabrikString::rtrimword($memory, 'M');
		if ($intmemory < 50) {
			ini_set('memory_limit', '50M');
		}
		if ($ext == 'jpg' || $ext == 'jpeg') {
			$img = imagecreatefromjpeg($origFile);
			$header = "image/jpeg";
		} else if ($ext == 'png') {
			$img = imagecreatefrompng($origFile);
			$header = "image/png";
			/* Only if your version of GD includes GIF support*/
		} else if ($ext == 'gif') {
			if (function_exists(imagecreatefromgif)) {
				$img = imagecreatefromgif($origFile);
				$header = "image/gif";
			} else {
				JError::raiseWarning(21, "imagecreate from gif not available");
			}
		}
		/* If an image was successfully loaded, test the image for size*/
		if ($img) {
			/* Get image size and scale ratio*/
			$width = imagesx($img);
			$height = imagesy($img);

			$scale = min($maxWidth / $width, $maxHeight / $height);
			/* If the image is larger than the max shrink it*/
			if ($scale < 1) {
				$new_width = floor($scale * $width);
				$new_height = floor($scale * $height);
				/* Create a new temporary image*/
				$tmp_img = imagecreatetruecolor($new_width, $new_height);
				/* Copy and resize old image into new image*/
				imagecopyresampled($tmp_img, $img, 0, 0, 0, 0,
				$new_width, $new_height, $width, $height);
				imagedestroy($img);
				$img = $tmp_img;
			}

		}
		if (!$img) {
			JError::raiseWarning(21, "no image created for $origFile, extension = $ext , destination = $destFile ");
		}

		/* save the file
		 * wite them out to output buffer first so that we can use JFile to write them
		 to the server (potential using J ftp layer)  */
		if ($header == "image/jpeg") {

			ob_start();
			imagejpeg($img, "", 100);
			$image = ob_get_contents();
			ob_end_clean();
			$this->storage->write($destFile, $image);
		} else {
			if ($header == "image/png") {
				ob_start();
				imagepng($img, "", 0);
				$image = ob_get_contents();
				ob_end_clean();
				$this->storage->write($destFile, $image);
			} else {
				if (function_exists("imagegif")) {
					ob_start();
					imagegif($img, "", 100);
					$image = ob_get_contents();
					ob_end_clean();
					$this->storage->write($destFile, $image);
				} else {
					/* try using imagemagick to convert gif to png:*/
					$image_file = imgkConvertImage($image_file, $baseDir, $destDir, ".png");
				}
			}
		}
		$this->_thumbPath = $destFile;
		ini_set('memory_limit', $memory);
	}


}

class imageIM extends image
{

	var $imageMagickDir = '/usr/local/bin/';

	function imageIM()
	{

	}

	/**
	 * resize an image to a specific width/height using imagemagick
	 * you cant set the quality of the resized image
	 * @param int maximum image Width (px)
	 * @param int maximum image Height (px)
	 * @param string full path of image to resize
	 * @param string full file path to save resized image to
	 * @return string output from image magick command
	 */

	function resize($maxWidth, $maxHeight, $origFile, $destFile)
	{
		$ext = $this->GetImgType($origFile);
		if (!$ext) {
			//false so not an image type so cant resize
			// $$$ hugh - testing making thumbs for PDF's, so need a little tweak here
			$originfo = pathinfo($origFile);
			if (strtolower($originfo['extension']) != 'pdf') {
				return;
			}
		}
		ini_set('display_errors', true);
		//see if the imagick image lib is installed
		if (class_exists('Imagick')) {

			// $$$ hugh - having a go at handling PDF thumbnails, which should work as long as the server
			// has ghostscript (GS) installed.  Don't have a generic test for GS being available, so
			// it'll just fail if no GS.

			$originfo = pathinfo($origFile);
			if (strtolower($originfo['extension']) == 'pdf') {
				$pdf_thumb_type = 'png'; // could use jpg or whatever
				// OK, it's a PDF, so first we need to add the page number we want to the source filename
				$pdf_file = $origFile . '[0]';
				// Now check to see if the destination filename needs changing - existing code will probably
				// just have used the sourcefile extension for the thumb file.
				$destinfo = pathinfo($destFile);
				if (strtolower($destinfo['extension']) == 'pdf') {
					// rebuild $destFile with valid image extension
					// NOTE - changed $destFile arg to pass by reference OOOPS can't do that!
					$destFile = $destinfo['dirname'] . DS . $destinfo['filename'] . '.' . $pdf_thumb_type;
				}
				// Now just load it, set format, resize, save and garbage collect.
				// Hopefully IM will call the right delagate (ghostscript) to load the PDF.
				$im = new Imagick( $pdf_file);
				$im->setImageFormat( $pdf_thumb_type);
				$im->thumbnailImage( $maxWidth, $maxHeight, true);
				$im->writeImage( $destFile);
				$im->destroy();
			}
			else {
				$im = new Imagick();

				/* Read the image file */
				$im->readImage($origFile);

				/* Thumbnail the image ( width 100, preserve dimensions ) */
				$im->thumbnailImage($maxWidth, $maxHeight, true);

				/* Write the thumbail to disk */
				$im->writeImage($destFile);

				/* Free resources associated to the Imagick object */
				$im->destroy();
			}
			$this->_thumbPath = $destFile;
		} else {
			$resource = NewMagickWand();

			if (!MagickReadImage($resource, $origFile)) {
				echo "ERROR!";
				print_r(MagickGetException($resource));
			}else{
			}
			$resource = MagickTransformImage($resource, '0x0', $maxWidth . 'x' . $maxWidth);
			$this->_thumbPath = $destFile;
			MagickWriteImage($resource, $destFile);
		}
	}
}
?>