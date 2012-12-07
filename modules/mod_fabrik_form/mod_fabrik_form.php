<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Ensure the package is set to fabrik
$prevUserState = $app->getUserState('com_fabrik.package');
$app->setUserState('com_fabrik.package', 'fabrik');

require JPATH_SITE . '/modules/mod_fabrik_form/mod_fabrik_form_boot.php';

// Set the package back to what it was before rendering the module
$app->setUserState('com_fabrik.package', $prevUserState);
