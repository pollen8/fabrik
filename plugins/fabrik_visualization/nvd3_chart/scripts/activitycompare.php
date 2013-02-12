<?php

$db = JFactory::getDbo();
$query = $db->getQuery(true);

// Get labels
$query->select('params')->from('i2qtd_fabrik_elements')->where('id = 16');
$db->setQuery($query);
$params = $db->loadResult();
$params = json_decode($params);
$sub_options = ($params->sub_options);
$sub_values = ($sub_options->sub_values);

$sub_labels = ($sub_options->sub_labels);
$labels = array_combine($sub_values, $sub_labels);

$query->clear();
$query->select('id, (electronisch_dance -50) * relevantie_live_dance AS dance, (electronisch_underground -50) * relevantie_live_dance AS underground, (live_pop - 50) * relevantie_live_dance AS pop, (live_rock -50)* relevantie_live_dance AS rock, (urban - 50) * relevantie_live_dance AS urban, (overig -50) * relevantie_live_dance AS overig')->from('fab_userinfo');//->group('doelgroep_student');
$query->order('id ASC');
$db->setQuery($query);
$data = array();
$rows = $db->loadObjectList('id');




$this->data = array();

 $keys = array('dance', 'underground', 'pop', 'rock', 'urban', 'overig');
foreach ($keys as $key)
{
	$o = new stdClass;
	$o->key = $key;
	$o->values = array();

	foreach ($rows as $id => $row)
	{
		$o->values[] = array($id, ($row->$key / 100));
	}
	$this->data[] = $o;
}
//echo "<pre>";print_r($this->data);echo "</pre>";


