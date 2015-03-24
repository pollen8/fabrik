<?php

defined('JPATH_BASE') or die;

$d = $displayData;
$attribs = 'class="input-small fabrikinput inputbox ' . $d['advancedClass'] . ' ' . $d['errorCSS'] . '"';
?>

<div class="fabrikSubElementContainer" id="<?php echo $d['id']; ?>">
<?php
	// $d['name'] already suffixed with [] as element hasSubElements = true
	if ($data['format'] != 'i:s')
	{
	$str[] = JHTML::_('select.genericlist', $d['hours'], preg_replace('#(\[\])$#', '[0]', $d['name']), $attribs, 'value', 'text', $d['hourValue']) . ' '
	. $data['sep'];
	}

	$str[] = JHTML::_('select.genericlist', $d['mins'], preg_replace('#(\[\])$#', '[1]', $d['name']), $attribs, 'value', 'text', $d['minValue']);

	if ($data['format'] != 'H:i')
	{
	echo $data['sep'] . ' '
	. JHTML::_('select.genericlist', $d['secs'], preg_replace('#(\[\])$#', '[2]', $d['name']), $attribs, 'value', 'text', $d['secValue']);
	}
?>
</div>