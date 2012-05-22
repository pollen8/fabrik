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

class fabrikModelListofevents extends FabrikFEModelVisualization {

	function getRows()
	{
		$params = $this->getParams();
		$ids = (array)$params->get('listofevents_table'); // ID of the list
		$fromDates = (array)$params->get('listofevents_from_date'); // Full element name
		$toDates = (array)$params->get('listofevents_to_date');
		$exceptDates = (array)$params->get('listofevents_except_dates');
		$eventNames = (array)$params->get('listofevents_name_event');
		$venueNames = (array)$params->get('listofevents_venue_name');
		$venuesTable = (array)$params->get('listofevents_venue_table_name'); // ID of the venues list
		$formatDate = (array)$params->get('listofevents_format_date'); // Date format to display in the list
		$displayOptions = (array)$params->get('listofevents_display_options'); // Display options : 0 - all events, 1 - upcoming events, 2 - past events
		$displayOrder = (array)$params->get('listofevents_display_order'); // Order dates options : 0 - all events in ascending chronological order, 1 - all events in descending chronological order

		$this->rows = array();
		for ($x = 0; $x < count($ids); $x++) {
			$asfields = array();
			$fields= array();
			$listModel = JModel::getInstance('List', 'FabrikFEModel');			
			$listModel->setId($ids[$x]); 
			$item = $listModel->getTable();      			
			$formModel = $listModel->getFormModel();      
			$formModel->getForm();
			$db = $listModel->getDb();
			$query = $db->getQuery(true);

			$fromDate = $formModel->getElement($fromDates[$x]);
			$fromDate->getAsField_html($asfields, $fields, array('alias'=>'fromdate'));
			
			$toDate = $formModel->getElement($toDates[$x]);
			$toDate->getAsField_html($asfields, $fields, array('alias'=>'todate'));

			$exceptDate = $formModel->getElement($exceptDates[$x]);
			$exceptDate->getAsField_html($asfields, $fields, array('alias'=>'exceptdate'));
			
			$eventName = $formModel->getElement($eventNames[$x]); 			
			$eventName->getAsField_html($asfields, $fields, array('alias'=>'event'));
			
			$venueName = $formModel->getElement($venueNames[$x]);   
			$venueName->getAsField_html($asfields, $fields, array('alias'=>'venue'));      

			$query->select("'$item->label' AS type, ".$item->db_primary_key.' AS pk, '.implode(',', $asfields))->from($db->nameQuote($item->db_table_name));
			$query = $listModel->_buildQueryJoin($query);
			$query = $listModel->_buildQueryWhere(true, $query);
			$query->order( str_replace('___', '.', $fromDates[$x]) . ' ASC'  );
			$db->setQuery($query, 0 );
			$rows = $db->loadObjectList(); 			
			
			$toDay = JFactory::getDate();
			$toDay->setTime( 0,0,0);
			
			// 5.3 only
			if (class_exists('DateInterval')) {
				$toDay = new DateTime($toDay);
				
				if ($rows === null) {
					echo JText::_('PLG_VIZ_LISTOFEVENTS_NO_DATES_TO_DISPLAY');
				} else {
					$i = 0;
					$newRow = array();
					foreach ($rows as &$row) {
						$fromDateChecked = new DateTime( $row->fromdate );      
						// If there is no enddate, the show is only one day and the "To" date is equal to the "From" date
						( $row->todate === '0000-00-00 00:00:00' ) ? $toDateChecked = $fromDateChecked : $toDateChecked = new DateTime( $row->todate );
						$exceptDatesChecked = explode( ',', $row->exceptdate );
						
						switch( $displayOptions[0] ) {
							case '0' : // Display all events
								while( $fromDateChecked <= $toDateChecked ) {
									$newRow[$i] = fabrikModelListofevents::expandDates( $row, $fromDateChecked, $toDateChecked, $exceptDatesChecked, $formatDate, $venuesTable );
									$fromDateChecked->add(new DateInterval('P1D'));
									$i++;
								}
								break;
							
							case '1' : // Display only upcoming events
								if( $fromDateChecked < $toDay ) { // If the starting date < today it should not appear in the list
									$fromDateChecked->add(new DateInterval('P1D'));
								} else {
									while( $fromDateChecked <= $toDateChecked ) {
										$newRow[$i] = fabrikModelListofevents::expandDates( $row, $fromDateChecked, $toDateChecked, $exceptDatesChecked, $formatDate, $venuesTable );
										$fromDateChecked->add(new DateInterval('P1D'));
										$i++;
									}
								}
								break;
							
							case '2' : // Display only the past events
								if( $fromDateChecked < $toDay ) {
									while( $fromDateChecked <= $toDateChecked && $fromDateChecked < $toDay ) {
										$newRow[$i] = fabrikModelListofevents::expandDates( $row, $fromDateChecked, $toDateChecked, $exceptDatesChecked, $formatDate, $venuesTable );
										$fromDateChecked->add(new DateInterval('P1D'));
										$i++;
									}
									break;
								}
								break;		
						}
					}
				}
			} else {
				$toDayUnix = $toDay->toUnix();
				if ($rows === null) {
					JError::raiseNotice(400, $db->getErrorMsg());
				} else {
					$i = 0;
					$newRow = array();
					foreach ($rows as &$row) {
						$fromDateChecked = JFactory::getDate( $row->fromdate );
						$fromDateCheckedUnix = $fromDateChecked->toUnix();      
						// If there is no enddate, the show is only one day and the "To" date is equal to the "From" date
						( $row->todate === '0000-00-00 00:00:00' ) ? $toDateChecked = $fromDateChecked : $toDateChecked = JFactory::getDate( $row->todate );
						$toDateCheckedUnix = $toDateChecked->toUnix();
						$exceptDatesChecked = explode( ',', $row->exceptdate );
						
						switch( $displayOptions[0] ) {
							case '0' : // Display all events
								while( $fromDateCheckedUnix <= $toDateCheckedUnix ) {
									$newRow[$i] = fabrikModelListofevents::expandDates( $row, $fromDateChecked, $toDateChecked, $exceptDatesChecked, $formatDate, $venuesTable );
									$fromDateCheckedUnix += 24*60*60;
									$fromDateChecked = JFactory::getDate( $fromDateCheckedUnix );
									$i++;
								}
								break;
							
							case '1' : // Display only upcoming events
								if( $fromDateCheckedUnix < $toDayUnix ) { // If the starting date < today it should not appear in the list
									$fromDateCheckedUnix += 24*60*60;
								} else {
									while( $fromDateCheckedUnix <= $toDateCheckedUnix ) {
										$newRow[$i] = fabrikModelListofevents::expandDates( $row, $fromDateChecked, $toDateChecked, $exceptDatesChecked, $formatDate, $venuesTable );
										$fromDateCheckedUnix += 24*60*60;
										$fromDateChecked = JFactory::getDate( $fromDateCheckedUnix );
										$i++;
									}
								}
								break;
							
							case '2' : // Display only the past events
								if( $fromDateCheckedUnix < $toDayUnix ) {
									while( $fromDateCheckedUnix <= $toDateCheckedUnix && $fromDateCheckedUnix < $toDayUnix ) {
										$newRow[$i] = fabrikModelListofevents::expandDates( $row, $fromDateChecked, $toDateChecked, $exceptDatesChecked, $formatDate, $venuesTable );
										$fromDateCheckedUnix += 24*60*60;
										$fromDateChecked = JFactory::getDate( $fromDateCheckedUnix );
										$i++;
									}
									break;
								}
								break;		
						}
					}
				}
			}
		}
		( $displayOrder[0] == 0 ) ? $asc = true : $asc = false;
		$this->rows = fabrikModelListofevents::sortByOneKey( $newRow, 'timestamp', $asc );				
		return $this->rows;
	}
	
	private function expandDates( $row, $fromDateChecked, $toDateChecked, $exceptDatesChecked, $formatDate, $venuesTable )
	{
		if( !in_array( $fromDateChecked->format('d'), $exceptDatesChecked )) {
			$newRow['fromdate'] = $fromDateChecked->format( $formatDate[0] );
			$urlEvent = JRoute::_( 'index.php?option=com_content&view=article&id=' . $row->event_raw . '&Itemid=248');
			$newRow['event'] = '<a href="' . $urlEvent . '" title="'. $row->event . '">' . $row->event . '</a>';
			
			// Get joined form ID for the venues
			$database = FabrikWorker::getDbo();
			$sqlform = 'SELECT id FROM #__{package}_forms WHERE id = \''.$venuesTable[0].'\'';
			$database->setQuery($sqlform);
			$idFormVenue = $database->loadResult('id', false);

			// $urlVenue = JRoute::_( 'index.php?option=com_fabrik&view=details&formid=' . $idFormVenue . '&rowid=' . $row->venue_raw . '&tmpl=component' );
			$newRow['venue'] = '<a href="#" id="idVenue" onclick="useIdVenue(\'' . $row->venue_raw . '\')">' . $row->venue . '</a>';
			//$newRow['venue'] = $row->venue;
			
			// 5.3 only
			if (class_exists('DateInterval')) {
				$newRow['timestamp'] = $fromDateChecked->getTimestamp();
			} else {
				$newRow['timestamp'] = $fromDateChecked->toUnix();
			}
			
			return $newRow;
		}
		return false;
	}
	
	private function sortByOneKey(array $array, $key, $asc = true) {
		$result = array();
        	$values = array();
		
		foreach ($array as $id => $value) {
		    $values[$id] = isset($value[$key]) ? $value[$key] : '';
		}
	
		if ($asc) {
		    asort($values);
		} else {
		    arsort($values);
		}
		
		foreach ($values as $key => $value) {
		    if( !empty ($array[$key] )) {
			$result[$key] = $array[$key];
		    }
		}
		return $result;
	}
	
	function setListIds()
	{
		if (!isset($this->listids)) {
			$params = $this->getParams();
			$this->listids = (array) $params->get('listofevents_table');
		}
	}
}
?>