<?php
/**
 * sh404SEF support for com_fabrik component.
 * Author : peamak
 * contact : tom@spierckel.net
 *
 * {shSourceVersionTag: Version beta - 2009-12-03}
 *
 * This is a sh404SEF native plugin file for Fabrik component (http://fabrikar.com)
 *
 */
defined('_JEXEC' ) or die('Direct Access to this location is not allowed.');
jimport('joomla.application.component.model');

// ------------------  standard plugin initialize function - don't change ---------------------------
global $sh_LANG, $sefConfig;
$shLangName = '';
$shLangIso = '';
$title = array();
$shItemidString = '';
$dosef = shInitializePlugin($lang, $shLangName, $shLangIso, $option);
if ($dosef == false) return;
// ------------------  standard plugin initialize function - don't change ---------------------------

// ------------------  load language file - adjust as needed ----------------------------------------
//
/// $$$rob dont load if joomfish installed?
/// $$$peamak: that's actually the opposite: load only if joomfish is installed;)
/// as per: http://fabrikar.com/forums/showthread.php?p=66613#post66613
if (defined('JOOMFISH_PATH')) {
	if (isset($sh_LANG)) {
		$shLangIso = shLoadPluginLanguage('com_fabrik', $shLangIso, '_COM_SEF_SH_CREATE_NEW');
	}
}
// ------------------  load language file - adjust as needed ----------------------------------------


//Fetch the table's name
if (!function_exists('shFetchTableName')) {
	function shFetchTableName($listid) {
		if (empty($listid)) return null;
		$database = FabrikWorker::getDbo();
		$sqltable = 'SELECT label, id FROM #__{package}_lists WHERE id = \''.$listid.'\'';
		$database->setQuery($sqltable);
		if (empty($shLangName))
		$tableName = $database->loadResult('label', false);
		return isset($tableName) ? $tableName : '';

	}
}

//Fetch the form's name
if (!function_exists('shFetchFormName')) {
	function shFetchFormName($fabrik) {
		if (empty($fabrik)) return null;
		$database = FabrikWorker::getDbo();
		$sqlform = 'SELECT label, id FROM #__{package}_forms WHERE id = \''.$fabrik.'\'';
		$database->setQuery($sqlform);
		if (empty($shLangName))
		$formName = $database->loadResult('label', false);
		return isset($formName) ? $formName : '';
	}
}

// Fetch record's name
if (!function_exists('shFetchRowName')) {
 function shFetchRowName($rowid, $listid) {
	 if (empty($rowid) || $rowid == '-1') return null;
	  $database = FabrikWorker::getDbo();
	  $listModel = JModel::getInstance('View', 'FabrikFEModel');
	  $listModel->setId($listid);
	  $table = $listModel->getTable();
	  $db_primary_key = $table->db_primary_key;
	  $db_primary_key = explode('.', $db_primary_key);
	  $db_table = $db_primary_key[0];
	  $db_pk = $db_primary_key[1];
	  $sqlrow = 'SELECT title_sef, '.$db_pk.' FROM '.$db_table.' WHERE '.$db_pk.' = \''.$rowid.'\'';
	  $database->setQuery($sqlrow);
	  if (empty($shLangName))
	  $rowLabel = $database->loadResult('title_sef', false);
	  if (!empty($rowLabel)) {
		  shRemoveFromGETVarsList('rowid');
	  }
	  return isset($rowLabel) ? $rowLabel : '';
  }
}


$task = isset($task) ? @$task : null;
$Itemid = isset($Itemid) ? @$Itemid : null;
$listid = isset($listid) ? @$listid : null;
$view = isset($view) ? @$view : null;
$fabrik = isset($fabrik) ? @$fabrik : null;
$rowid = isset($rowid) ? @$rowid : null;
/*---------------------------------------------
 This part is only if you use some custom buttons appending "&my_table___my_element=value2" to your URL.
 In my exemple I have four categories (value1, value2, ...).
 ---------------------------------------------*/
//$my_table___my_element = isset($my_table___my_element) ? @$my_table___my_element : null;

$shSampleName = shGetComponentPrefix($option);
$shSampleName = empty($shSampleName) ?
getMenuTitle($option, $task, $Itemid, null, $shLangName) : $shSampleName;
$shSampleName = (empty($shSampleName) || $shSampleName == '/') ? 'SampleCom':$shSampleName;


//Show the table's name
if (isset($listid)) {
	$title[] = shFetchTableName($listid);
}

//Show the form's name
if (isset($fabrik)) {
	$title[] = shFetchFormName($fabrik);
	shRemoveFromGETVarsList('listid');
}

/* Fetch record's name.
 * Create an element called 'title_sef' in your table which should
 * return a string with no special characters or spaces or accents... One could use a
 * Fabrik Calc element which takes the value of another element on the table and replaces
 * all the unwanted characters with '_'.
*/
if (isset($rowid)) {
 	$title[] = shFetchRowName($rowid, $listid);
 }

//Again, this is for your custom links using "&my_table___my_element=value2"
/*$sh_LANG['fr']['value1'] = 'valeur-un';
 $sh_LANG['en']['value1'] = 'value-one';
 $sh_LANG['fr']['value2'] = 'valeur-deux';
 $sh_LANG['en']['value2'] = 'value-two';
 $sh_LANG['fr']['value3'] = 'valeur-trois';
 $sh_LANG['en']['value3'] = 'value-three';
 $sh_LANG['fr']['value4'] = 'valeur-quatre';
 $sh_LANG['en']['value4'] = 'value-four';

 if (isset($my_table___my_element)) {
 $title[] = $sh_LANG[$shLangIso][$my_table___my_element];
 }*/

//Here you can change 'word' in "$title[] = 'word';" / Not in "case 'word':"
if (isset($view)) {
	switch ($view)
	{
		case 'form':
		if ((empty($rowid)) && (empty($listid))) {
			$title[] = 'form';
		} else if (empty($rowid)) {
			$title[] = 'new';
		} else {
			$title[] = 'edit';
		}
	  break;
	  case 'details':
	   $title[] = 'details';
	   break;
	}
}



//$filter = JRequest::getVar('element_test___country_id', array(), 'request', 'array');
/*if (!empty($filter)) {
	global $shGETVars;
	echo "<pre>";print_r($shGETVars);
$title[] = 'test-filter-found';
//shRemoveFromGETVarsList('element_test___country_id');
echo "<pre>";print_r($shGETVars);
print_r($title);exit;
}*/
shRemoveFromGETVarsList('option');
//Again, this is for your custom links using "&my_table___my_element=value2"
//shRemoveFromGETVarsList('mytable___myelement');
shRemoveFromGETVarsList('calculations');
shRemoveFromGETVarsList('formid');
shRemoveFromGETVarsList('listid');
shRemoveFromGETVarsList('c');
shRemoveFromGETVarsList('view');
shRemoveFromGETVarsList('Itemid');
shRemoveFromGETVarsList('lang');
shRemoveFromGETVarsList('resetfilters');
shRemoveFromGETVarsList('calculations');
shRemoveFromGETVarsList('random');

// For new entries in forms and if a title is set for rowids, don't show '?rowid='
if ((empty($rowid)) || (isset($title[$rowid]))) {
shRemoveFromGETVarsList('rowid');
}

// ------------------  standard plugin finalize function - don't change ---------------------------
if ($dosef) {
	$string = shFinalizePlugin($string, $title, $shAppendString, $shItemidString,
	(isset($limit) ? @$limit : null), (isset($limitstart) ? @$limitstart : null),
	(isset($shLangName) ? @$shLangName : null));
}
// ------------------  standard plugin finalize function - don't change ---------------------------

?>
