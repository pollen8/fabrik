<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access

/*
 * To get the main image:
 * $product->get_product_images_html('main');
 *
 * To get cross sell products:
 * $product->get_product_cross_sells_html();
 *
 * To get up sell products:
 * $product->get_product_upsells_html();
 */
defined('_JEXEC') or die;

$product = $displayData->product;
echo $product->get_product_images_html('main');
echo $product->get_product_html();
