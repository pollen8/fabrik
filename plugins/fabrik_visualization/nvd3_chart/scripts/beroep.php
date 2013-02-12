<?php

$db = JFactory::getDbo();
$query = $db->getQuery(true);

// Get labels
$query->select('params')->from('i2qtd_fabrik_elements')->where('id = 14');
$db->setQuery($query);
$params = $db->loadResult();
$params = json_decode($params);
$sub_options = ($params->sub_options);
$sub_values = ($sub_options->sub_values);

$sub_labels = ($sub_options->sub_labels);
$labels = array_combine($sub_values, $sub_labels);

$query->clear();
$query->select('COUNT( * ) AS value, `doelgroep_bezoeker_events` AS label')->from('fab_userinfo')->group('doelgroep_bezoeker_events');
$db->setQuery($query);
$data = array();
$rows = $db->loadObjectList();
$total = 0;
foreach ($rows as $row)
{
	$total += $row->value;
}

foreach ($rows as $row)
{
	$row->value = ($row->value / $total) * 100;
	$row->label = strip_tags($labels[$row->label]);
}


$this->data = new stdClass;
$this->data->key = 'todo2';
$this->data->values = $rows;

/* foreach ($data as $data)
{
	$this->data->values[] = $data;
} */
$this->data = array($this->data);
