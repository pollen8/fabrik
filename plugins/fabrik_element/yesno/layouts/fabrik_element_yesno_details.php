<?php
/**
 * Layout: Yes/No field list view
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2014 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.2
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$data = $displayData['value'];
$tmpl = $displayData['tmpl'];
$j3 = FabrikWorker::j3();

$app = JFactory::getApplication();
$format = $app->input->get('format', '');

$opts = array();
$properties = array();

if ($format == 'pdf')
{
	$opts['forceImage'] = true;
	FabrikHelperHTML::addPath(COM_FABRIK_BASE . 'plugins/fabrik_element/yesno/images/', 'image', 'list', false);
}

if ($data == '1')
{
	$icon = $j3 && $format != 'pdf' ? 'checkmark.png' : '1.png';
	$properties['alt'] = FText::_('JYES');

	echo FabrikHelperHTML::image($icon, 'list', $tmpl, $properties, false, $opts);
}
else
{
	$icon = $j3 && $format != 'pdf' ? 'remove.png' : '0.png';
	$properties['alt'] = FText::_('JNO');
	
	echo FabrikHelperHTML::image($icon, 'list', $tmpl, $properties, false, $opts);
}
