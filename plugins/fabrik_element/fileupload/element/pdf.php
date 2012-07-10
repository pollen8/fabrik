<?php
/**
* Plugin element to render pdf files
* @package fabrikar
* @author Hugh Messenger
* @copyright (C) hugh Messenger
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once JPATH_SITE . '/plugins/fabrik_element/fileupload/models/pdf.php';
$render = new pdfRender;
