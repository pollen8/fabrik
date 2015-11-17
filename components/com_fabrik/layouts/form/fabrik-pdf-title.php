<?php
/**
 * PDF document title for form view
 *
 */

defined('JPATH_BASE') or die;

$d = $displayData;

// Set the download file name based on the document title and rowid

echo $d->doc->getTitle() . '-' . $d->model->getRowId();
