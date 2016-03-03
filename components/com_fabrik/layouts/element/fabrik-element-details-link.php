<?php

/**
 * Allows overriding the details link for an element.  Default is just to echo the already built
 * link.  For details of what gets passed in $displayData, see the linkHref() method in main front end list model
 */
defined('JPATH_BASE') or die;

$d = $displayData;

echo $d->link;