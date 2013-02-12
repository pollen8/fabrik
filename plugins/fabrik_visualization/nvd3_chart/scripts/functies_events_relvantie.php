<?php

// Radio button sum

$db = JFactory::getDbo();
$query = $db->getQuery(true);

// Get labels
$query->select('params')->from('i2qtd_fabrik_elements')->where('id = 40');
$db->setQuery($query);
$params = $db->loadResult();
$params = json_decode($params);
$sub_options = ($params->sub_options);
$sub_values = ($sub_options->sub_values);

$sub_labels = ($sub_options->sub_labels);
$labels = array_combine($sub_values, $sub_labels);

$query->clear();
$query->select('COUNT( * ) AS value, functies_events_relvantie')->from('fab_userinfo')->group('functies_events_relvantie');
$db->setQuery($query);
$rows = $db->loadObjectList('functies_events_relvantie');
$total = 0;

$data = array();
foreach ($rows as $key => $obj)
{
	$o = new stdClass;
	$o->label = $labels[$key];
	$o->value = $obj->value;
	$data[$key] = $o;


}
$this->data = new stdClass;
$this->data->key = 'todo2';
$this->data->values = array();



foreach ($data as $data)
{
	$this->data->values[] = $data;
}
$this->data = array($this->data);
