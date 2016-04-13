<?php
defined('JPATH_BASE') or die;
use Fabrik\Helpers\Text;

$d = $displayData;

echo Text::_('PLG_FORM_COMMENT_NEW_COMMENT_ADDED') . "<br /><a href=\"{$d->row->url}\">" . Text::_('PLG_FORM_COMMENT_VIEW_COMMENT') . "</a>";