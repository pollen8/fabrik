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

$yes_label = (empty($d->yes_label)) ? FText::_('JYES') : FText::_($d->yes_label) ;
$no_label = (empty($d->no_label)) ? FText::_('JNO') : FText::_($d->no_label) ;
$yes_image = (empty($d->yes_image)) ? 'checkmark' : $d->yes_image ;
$no_image = (empty($d->no_image)) ? 'remove' : $d->no_image ;
if(!empty($d->image_height)) $properties['style'] = FText::_('height:'.$d->image_height.'px!important');

if ($d->format == 'pdf' || strpos($yes_image,'.')>0 ) :
	$opts['forceImage'] = true;
	FabrikHelperHTML::addPath(COM_FABRIK_BASE . 'plugins/fabrik_element/yesno/images/', 'image', 'list', false);
endif;

if ($data == '1') :
	$icon = $j3 && $format != 'pdf' ? $yes_image : '1.png';
	$properties['alt'] = $yes_label;
else :
	$icon = $j3 && $format != 'pdf' ? $no_image : '0.png';
	$properties['alt'] = $no_label;
endif;
echo FabrikHelperHTML::image($icon, 'list', $tmpl, $properties, false, $opts);
