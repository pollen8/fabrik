<?php

$db = JFactory::getDbo();
$query = $db->getQuery(true);

$query->select('functie_supports_relevantie')->from('fab_userinfo');
$db->setQuery($query);
$rows = $db->loadColumn();
$total = 0;
$nototal = 0;
foreach ($rows as $row)
{
	$row = json_decode($row);
	foreach ($row as &$v) {
		if ($v == 1 || $v == 2) {
			$v = 100;
		} else if ($v == 3)
		{
			$v += 50;
		} else if ($v == 0)
		{
			$nototal -= 100;
		}
	}
	$total += array_sum($row) / count($row);
}
$query->clear();
$query->select('SUM(relevantie_playground) * 100  AS Playground');
$query->select('SUM(functies_events_relvantie) * 100  AS Events')->from('fab_userinfo');
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

	if ($key === 'Playground')
	{
		// Add in No playgroud
		$query->clear();
		$query->select('(COUNT(relevantie_playground) * -100) AS NoPlaygroud' )->from('fab_userinfo')->where('relevantie_playground = 0');
		$db->setQuery($query);
		$o = new stdClass;
		$o->label = 'No Playgroud';
		$o->value =  $db->loadResult();
		$data[] = $o;
	}

	if ($key === 'Events')
	{
		// Add in No events
		$query->clear();
		$query->select('(COUNT(functies_events_relvantie) * -100) AS NoPlaygroud' )->from('fab_userinfo')->where('functies_events_relvantie = 0');
		$db->setQuery($query);
		$o = new stdClass;
		$o->label = 'No Events';
		$o->value =  $db->loadResult();
		$data[] = $o;
	}
}


// Add in supports
$o = new stdClass;
$o->label = 'Supports';
$o->value = $total;
$data[] = $o;

// Add in no supports

$o = new stdClass;
$o->label = 'No Supports';
$o->value = $nototal;
$data[] = $o;

$this->data = new stdClass;
$this->data->key = 'todo2';
$this->data->values = $data;

$this->data = array($this->data);


