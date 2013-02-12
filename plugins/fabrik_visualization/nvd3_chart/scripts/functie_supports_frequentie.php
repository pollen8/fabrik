<?php

// Radio button sum

$db = JFactory::getDbo();
$query = $db->getQuery(true);

// Get labels
$query->select('params')->from('i2qtd_fabrik_elements')->where('id = 38');
$db->setQuery($query);
$params = $db->loadResult();
$params = json_decode($params);
$sub_options = ($params->sub_options);
$sub_values = ($sub_options->sub_values);

$sub_labels = ($sub_options->sub_labels);
$labels = array_combine($sub_values, $sub_labels);

$query->clear();
$query->select('COUNT( * ) AS value, functie_supports_frequentie ')->from('fab_userinfo')->group('functie_supports_frequentie');
$db->setQuery($query);
$rows = $db->loadObjectList('functie_supports_frequentie');
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
