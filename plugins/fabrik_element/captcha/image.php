<?php
/**
 * Trivial image serving script, to work round IE caching static CAPTCHA IMG's
 * @package     Joomla
 * @subpackage  Fabrik
 * @author Hugh Messenger
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

define( '_JEXEC', 1);

$jpath = dirname(__FILE__);
$jpath = str_replace('/plugins/fabrik_element/captcha', '', $jpath);
$jpath = str_replace('\plugins\fabrik_element\captcha', '', $jpath);
define('JPATH_BASE', $jpath);

define('DS', DIRECTORY_SEPARATOR);

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';
$app 				=& JFactory::getApplication('site');
$app->initialise();
$session = JFactory::getSession();
$code = $session->get('com_fabrik.element.captach.security_code', false);

if (!($code))
{
	exit;
}

// ***** e-kinst
// Felixkat - Removed width and height.  Now specified by font size.

// $width = $session->get('com_fabrik.element.captach.width', 100);
// $height = $session->get('com_fabrik.element.captach.height', 50);

$fontsize = $session->get('com_fabrik.element.captach.fontsize', 30);
$angle = $session->get('com_fabrik.element.captach.angle', 0);
$padding  = $session->get('com_fabrik.element.captach.padding', 10);
$font = $session->get('com_fabrik.element.captach.font', 'monofont.ttf');
$b_color = $session->get('com_fabrik.element.captach.bg_color', '255+255+255');
$bc = explode('+', $b_color);
$n_color = $session->get('com_fabrik.element.captach.noise_color', '0+0+255');
$nc = explode('+', $n_color);
$t_color = $session->get('com_fabrik.element.captach.text_color', '0+0+255');
$tc = explode('+', $t_color);

/* create textbox and add text */
$fontPath = JPATH_SITE . '/plugins/fabrik_element/captcha/' . $font;

$the_box = calculateTextBox($code, $fontPath, $fontsize, $angle);

$imgWidth    = $the_box["width"] + $padding;
$imgHeight    = $the_box["height"] + $padding;

$image = imagecreate($imgWidth, $imgHeight) or die ('Cannot initialize new GD image stream');

$background_color = imagecolorallocate($image, $bc[0], $bc[1], $bc[2]);
$text_color = imagecolorallocate($image, $tc[0], $tc[1], $tc[2]);
$noise_color = imagecolorallocate($image, $nc[0], $nc[1], $nc[2]);

/* generate random dots in background */
for ($i = 0; $i < ($imgWidth * $imgHeight) / 3; $i++)
{
	imagefilledellipse($image, mt_rand(0, $imgWidth), mt_rand(0, $imgHeight), 1, 1, $noise_color);
}

/*  generate random lines in background */
for ($i = 0; $i < ($imgWidth * $imgHeight) / 150; $i++)
{
	imageline($image, mt_rand(0, $imgWidth), mt_rand(0, $imgHeight), mt_rand(0, $imgWidth), mt_rand(0, $imgHeight), $noise_color);
}

imagettftext(
	$image,
	$fontsize,
	$angle,
	$the_box["left"] + ($imgWidth / 2) - ($the_box["width"] / 2),
	$the_box["top"] + ($imgHeight / 2) - ($the_box["height"] / 2),
	$text_color,
	$fontPath,
	$code
) or die('Error in imagettftext function');

// $$$ hugh - @TODO - add some session identifier to the image name (maybe using the hash we use in the formsession stuff)


ob_start();
imagejpeg($image);
$img = ob_get_contents();

/**
Felixkat - Clean has been replaced with flush due to a image truncating issue
Haven't been able to pinpoint the exact issue yet, possibly PHP version related
http://fabrikar.com/forums/showthread.php?p=147606#post147606
*/
// ob_end_clean();
ob_end_flush();
imagedestroy($image);

if (!empty($img))
{
	// ... set no-cache (and friends) headers ...
	// Some time in the past
	header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");
	header('Accept-Ranges: bytes');

	// For some weird reason if we do this in 5.2.x the image gets truncated
	// http://fabrikar.com/forums/showthread.php?t=26941&page=5
	if (version_compare(PHP_VERSION, '5.3.0') < 0)
	{
		header('Content-Length: ' . JString::strlen($img));
	}
	header('Content-Type: image/jpeg');

	// ... serve up the image ...
	echo $img;

	// ... and we're done.
	exit();
}

/**
 *  simple function that calculates the *exact* bounding box (single pixel precision).
 *  The function returns an associative array with these keys:
 *  left, top:  coordinates you will pass to imagettftext
 *  width, height: dimension of the image you have to create
 *
 * @param unknown_type $code
 * @param unknown_type $fontPath
 * @param unknown_type $fontsize
 * @param unknown_type $angle
 */
function calculateTextBox($code, $fontPath, $fontsize, $angle)
{
	$rect = imagettfbbox($fontsize, $angle, $fontPath, $code);
	$minX = min(array($rect[0], $rect[2], $rect[4], $rect[6]));
	$maxX = max(array($rect[0], $rect[2], $rect[4], $rect[6]));
	$minY = min(array($rect[1], $rect[3], $rect[5], $rect[7]));
	$maxY = max(array($rect[1], $rect[3], $rect[5], $rect[7]));

	return array
	(
		"left"   => abs($minX) - 1,
		"top"    => abs($minY) - 1,
		"width"  => $maxX - $minX,
		"height" => $maxY - $minY,
		"box"    => $rect
	);
}
