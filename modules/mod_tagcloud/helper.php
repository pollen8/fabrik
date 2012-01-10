<?php
/**
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2008 Pollen 8 Design Ltd
 * @license		GNU/GPL, see LICENSE.php
 */

/** ensure this file is being included by a parent file */
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

class modTagcloudHelper
{
	function getCloud(&$params)
	{

		$moduleclass_sfx 	= $params->get( 'moduleclass_sfx', '' );

		$table						= $params->get( 'table', '' );
		$column			 			= $params->get( 'column', '' );
		$filter			 			= $params->get( 'filter', '' );
		$url			 				= $params->get( 'url', '' );

		$splitter		 			= $params->get( 'splitter', '' );
		$alphabetically	 	= $params->get( 'alphabetically', '' );
		$min	 						= intval( $params->get( 'min', 1 ));
		$max			 				= intval( $params->get( 'max', 20 ));
		$seperator 				= $params->get( 'seperator', '' );
		$document =& JFactory::getDocument();
		$db =& JFactory::getDBO();
		$query = "SELECT $column FROM $table";
		if ($filter != '') {
			$query .= " WHERE $filter";
		}
		$db->setQuery( $query );
		$rows = $db->loadResultArray();

		$oCloud = new tagCloud( $rows, $url, $min, $max, $seperator, $splitter );
		return $oCloud->render( $alphabetically );
	}
}


class tagCloud{

	var $rows = array();
	var $countedRows = array();
	var $url = '';
	var $cloud = '';
	var $splitter = ',';
	var $min = '1'; // min number of matches at which tag is shown
	var $maxRecords = 20;
	
	/**
	 * constructor
	 * @param array $rows
	 * "param string url
	 * @param int $min
	 * @return tagCloud
	 */
	function tagCloud( $rows, $url,  $min = 1, $maxRecords = 20, $seperator = ' ... ', $splitter = ',' ){
		$this->rows = $rows;
		$this->url = $url;
		$this->min = $min;
		$this->maxRecords  = $maxRecords;
		$this->splitter = $splitter;
		$this->seperator = ' ' . $seperator . ' ';
		foreach( $this->rows as $row ){
			$bits = explode( $this->splitter, $row );
			foreach($bits as $bit){
				$bit = trim($bit);
				if($bit != ''){
					if(array_key_exists( $bit, $this->countedRows)){
						$this->countedRows[$bit] = $this->countedRows[$bit] + 1;
					}else{
						$this->countedRows[$bit] = 1;
					}
				}
			}
		}

	}

	function render( $order = 0 ){

		arsort($this->countedRows);
		//remove any that are less than min
		foreach( $this->countedRows as $key=>$val){
			if($val < $this->min ){
				unset( $this->countedRows[$key] );
			}
		}
		//trim to the top records
		$this->countedRows = array_slice( $this->countedRows, 0, $this->maxRecords);
		switch( $order ){
			case 0:
			default:
				//order size - asc
				asort($this->countedRows);
				break;
			case 1:
				//oder size dec
				arsort($this->countedRows);
				break;
			case 2:
				//order alphabetically - asc
				ksort($this->countedRows);
				break;
			case 3:
				//order alphabetically - desc
				krsort($this->countedRows);
				break;
		}

		$cloud = array();
		foreach($this->countedRows as $bit=>$count){
			$cloud[] = "<a href='" . $this->url . $bit . "'><span class='cloud_" . $count . "'>". $bit . "</span></a>" . $this->seperator;
		}
		return $cloud;
	}
}