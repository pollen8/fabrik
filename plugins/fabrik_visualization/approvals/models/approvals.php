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

class fabrikModelApprovals extends FabrikModelVisualization {

	/** @var object form model for standard add event form **/
	var $_formModel = null;

	/** @var array filters from url*/
	var $filters = array();


	/**
	 * constructor
	 */

	function __construct()
	{
		parent::__construct();
	}

	function getHTML()
	{
		$params =& $this->getPluginParams();
		$ids = $params->get('approvals_table', array(), '_default', 'array');
		$approveEls = $params->get('approvals_approve_element', array(), '_default', 'array');
		$str = array();
		for ($x = 0; $x < count($ids); $x++) {
			$str[] = "{fabrik view=table id=".$ids[$x]." ". $approveEls[$x]."=0 resetfilters=1}";
		}
		return implode("\n", $str);
	}


/*	function getParams()
	{
		if (!isset($this->_params)) {
			$v =& $this->getVisualization();
			$this->_params = new fabrikParams($v->attribs, JPATH_SITE . '/administrator/components/com_fabrik/xml/connection.xml', 'component');
		}
		return $this->_params;
	}

	function getVizName()
	{
		if(is_null($this->vizName)) {
			$item =& $this->_row;
			$this->vizName = "oCalendar{$item->id}";
		}
		return $this->vizName;
	}*/
}
?>