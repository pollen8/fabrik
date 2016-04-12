<?php
/**
 * Layout: Yes/No field list view
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.2
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\Html;
use Fabrik\Helpers\Worker;

$d = $displayData;
$data = $d->value;
$tmpl = $d->tmpl;
$j3 = Worker::j3();

if ($data == '1') :
	$icon = $j3 ? 'checkmark.png' : '1.png';
	$opts = array('alt' => FText::_('JYES'));

	echo Html::image($icon, 'list', $tmpl, $opts);
else :
	$icon = $j3 ? 'remove.png' : '0.png';

	echo Html::image($icon, 'list', $tmpl, array('alt' => FText::_('JNO')));
endif;
