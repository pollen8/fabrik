<?php

/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005-2011 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models'.DS.'visualization.php');

class fabrikModelApprovals extends FabrikFEModelVisualization {

	function getHTML()
	{
		$params =& $this->getParams();
		$ids = (array)$params->get('approvals_table');
		$approveEls = (array)$params->get('approvals_approve_element');
		$str = array();
		for ($x = 0; $x < count($ids); $x++) {
			$str[] = "{fabrik view=list id=".$ids[$x]." ". $approveEls[$x]."=0 resetfilters=1}";
		}
		return implode("\n", $str);
	}

}
?>