<?php

/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

$this->filepath = "foo";
$formModel = $model->getFormModel();
foreach ($row as $name => $value) {
	if (preg_match('#_raw$#', $name)) {
		continue;
	}
	$elementModel =& $formModel->getElement($name);
	if (empty($elementModel)) {
		// echo "ooops: $name<br />\n";
		continue;
	}
	$element = $elementModel->getElement();
	$label = $element->label;
	$fval = $elementModel->renderTableData($val, $row);
	echo "$name : $label : $value : $fval<br />\n";
}

?>