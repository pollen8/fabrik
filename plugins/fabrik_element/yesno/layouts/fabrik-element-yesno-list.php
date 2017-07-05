<?php
/**
 * Layout: Yes/No field list view
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.2
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

$d = $displayData;
$data = $d->value;
$tmpl = $d->tmpl;
$format = $d->format;

$j3 = FabrikWorker::j3();

$opts = array();
$properties = array();

if ($d->format == 'pdf') :
	$opts['forceImage'] = true;
	FabrikHelperHTML::addPath(COM_FABRIK_BASE . 'plugins/fabrik_element/yesno/images/', 'image', 'list', false);
endif;

$yes_image = ($d->yes_image == '') ? 'checkmark.png' : $d->yes_image ;
$no_image = ($d->no_image == '') ? 'remove.png' : $d->no_image ;
if(!empty($d->image_height)) $properties['style'] = FText::_('height:'.$d->image_height.'px!important');

if ($data == '1') :
	$icon = $j3 && $format != 'pdf' ? $yes_image : '1.png';
	$properties['alt'] = FText::_('JYES');

	echo FabrikHelperHTML::image($icon, 'list', $tmpl, $properties, false, $opts);
else :
	$icon = $j3 && $format != 'pdf' ? $no_image : '0.png';    
	$properties['alt'] = FText::_('JNO');

	echo FabrikHelperHTML::image($icon, 'list', $tmpl, $properties, false, $opts);
endif;
