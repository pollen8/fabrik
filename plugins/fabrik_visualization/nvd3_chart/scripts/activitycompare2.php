<?php

$db = JFactory::getDbo();
$query = $db->getQuery(true);

$query->select('SUM(electronisch_dance * relevantie_live_dance) AS dance, SUM(electronisch_underground * relevantie_live_dance) AS underground, SUM(live_pop * relevantie_live_dance) AS pop');
$query->select('SUM(live_rock * relevantie_live_dance) AS rock, SUM(urban * relevantie_live_dance) AS urban, SUM(overig * relevantie_live_dance) AS overig')->from('fab_userinfo');//->group('doelgroep_student');
$query->order('id ASC');
$db->setQuery($query);
$data = array();
$row = $db->loadAssoc();
$this->data = $row;

$data = array();

foreach ($row as $key => $val)
{
	$o = new stdClass;
	$o->label = $key;
	$o->value = $val;
	$data[] = $o;

}



$this->data = new stdClass;
$this->data->key = 'todo2';
$this->data->values = $data;

$this->data = array($this->data);


