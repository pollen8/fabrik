<?php
/**
* Plugin element to render fields
* @package fabrikar
* @author Rob Clayburn
* @copyright (C) Rob Clayburn
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once(JPATH_SITE.DS."plugins".DS."fabrik_element".DS."fileupload".DS."models".DS."image.php");

$render = new imageRender();
?>