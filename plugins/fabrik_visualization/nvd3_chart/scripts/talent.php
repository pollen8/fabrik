<?php

$db = JFactory::getDbo();
$query = $db->getQuery(true);

$query->select('COUNT( * ) AS value, relevantie_playground AS label')->from('fab_userinfo')->group('relevantie_playground');
$db->setQuery($query);
$rows = $db->loadObjectList('label');
$total = 0;
foreach ($rows as $row)
{
	$total += $row->value;
}

$data = array();

$o = new stdClass();
$o->label = 'Yes';
$o->value = $rows[1]->value;
$data[] = $o;

$o = new stdClass();
$o->label = 'No';
$o->value = (string) ($total - $rows[1]->value);
$data[] = $o;


$this->data = new stdClass;
$this->data->key = 'todo2';
$this->data->values = $data;

$this->data = array($this->data);
