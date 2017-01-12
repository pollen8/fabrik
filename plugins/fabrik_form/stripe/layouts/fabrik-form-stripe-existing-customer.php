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


if (class_exists('NumberFormatter'))
{
	$formatter = new NumberFormatter($d->langTag, NumberFormatter::CURRENCY);
	$d->amount = $formatter->formatCurrency($d->amount, $d->currencyCode);
}

$d->bottomText = str_ireplace('{stripe_amount}', $d->amount, $d->bottomText);
$d->bottomText = str_ireplace('{stripe_last4}', $d->card->last4, $d->bottomText);
$d->bottomText = str_ireplace('{stripe_item}', $d->item, $d->bottomText);

echo $d->bottomText;

?>
<!--
<div class="fabrikStripeButtonContainer">
	<button class="fabrikStripeChange">
		<span>Update CC</span>
	</button>
</div>
-->
