<?php
/**
 * @package J2Store
 * @copyright Copyright (c)2014-17 Ramesh Elamathi / J2Store.org
 * @license GNU GPL v3 or later
 */

// No direct access
defined('_JEXEC') or die;

$product = $displayData->product;
echo  $product->get_product_html();
echo  $product->get_product_images_html('main');
?>

