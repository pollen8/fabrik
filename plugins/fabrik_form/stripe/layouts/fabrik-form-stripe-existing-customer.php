<?php
/**
 * Stripe existing customer form bottom view
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.stripe
 * @copyright   Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access

defined('_JEXEC') or die;

$d = $displayData;


$formatter = new NumberFormatter($d->langTag, NumberFormatter::CURRENCY);
$d->amount = $formatter->formatCurrency($d->amount, $d->currencyCode);

echo FText::sprintf('PLG_FORM_EXISTING_CUSTOMER_PURCHASE', $d->amount, $d->card->last4);
