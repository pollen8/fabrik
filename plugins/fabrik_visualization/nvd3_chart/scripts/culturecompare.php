<?php

$db = JFactory::getDbo();
$query = $db->getQuery(true);


$query->select('SUM(relevantie_live_dance) AS \'Live and dance\', SUM(relevantie_culture_pub) AS \'Culture pub\'')->from('fab_userinfo');
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


