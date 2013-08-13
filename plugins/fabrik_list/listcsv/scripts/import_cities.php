<?php

/**
 * A simple example script which was used for testing the listcsv plugin during development.
 * It grabs the $formModel from the $listModel, and modifies the data being imported by
 * directly accessing the $formModel->-formData[] array, which will then be used by the
 * main CSV import code for writing the table data.
 */
defined('_JEXEC') or die();

$formModel =& $listModel->getForm();
if (strstr($formModel->formData['us_streets___date_time'],'1899')) {
   $formModel->formData['us_streets___date_time'] = str_replace('1899','1999',$formModel->formData['us_streets___date_time']);
}

$formModel->formData['us_streets___street_desc'] = "testing 1 2 3 testing";
$formModel->formData['us_streets___street_pic'] = "images/stories/fabrik/streets/foo.jpg"
