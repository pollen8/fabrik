<?php
/**
 * Fabrik Form Template: Bootstrap CSS
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

header('Content-type: text/css');
$c    = (int) $_REQUEST['c'];
$view = isset($_REQUEST['view']) ? $_REQUEST['view'] : 'form';
echo "

.fabrikGroup {
clear: left;
}
";

echo <<<EOT

/* BEGIN - Your CSS styling starts here */

label.fabrikgrid_PayPal_Express {
	background: url(https://www.paypalobjects.com/webstatic/mktg/logo/AM_mc_vs_dc_ae.jpg) no-repeat;
	display: block;
	height: 100px;
	width: 200px;
	background-size: contain;
	    margin-left: 20px;
}
label.fabrikgrid_PayPal_Express.radio  input[type="radio"] {
    margin-left: -40px;
    margin-top: 30px;
}


label.fabrikgrid_PayPal_Express span {
display: none;
}

label.fabrikgrid_PayPal_Express {
	background: url(https://www.paypalobjects.com/webstatic/mktg/logo/AM_mc_vs_dc_ae.jpg) no-repeat;
	display: block;
	height: 100px;
	width: 200px;
	background-size: contain;
	    margin-left: 20px;
}

label.fabrikgrid_AuthorizeNet_AIM {
    background: url(../../../../../../plugins/fabrik_form/payments/images/credit.png) no-repeat;
    display: block;
    height: 45px;
    min-width: 160px;
    background-size: 40px;
    margin-left: 20px;
    margin-top: 30px;
    padding-left: 0px;
}


label.fabrikgrid_AuthorizeNet_AIM span{
margin-left: 50px;
}

.fabrikgrid_radio  {
	height: 30px;
}

#group20 {
  margin-bottom: 35px;
}


/* END - Your CSS styling ends here */

EOT;

?>


