<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2010 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/


// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_fabrik'.DS.'helpers'.DS.'element.php');

/**
 * Renders an eval element
 *
 * @author 		rob clayburn
 * @package 	fabrikar
 * @subpackage		Parameter
 * @since		1.5
 */

class JFormFieldAutoCreateGroup extends JFormFieldRadio
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'AutoCreateGroup';

	function getInput()
	{
		$this->value = $this->form->getValue('id') == 0 ? 1 : 0;
		return parent::getInput();
	}
}
?>