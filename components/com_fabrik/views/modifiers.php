<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
* @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * static class to provide access to output modifier functions
 */
class fabrikModifier {

	function truncate( $text, $opts = array() ) {
		return fabrikString::truncate( $data, $opts);
	}

}
?>