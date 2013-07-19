<?php
/**
 * Render a tag cloud
 *
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access
defined('_JEXEC') or die('Direct Access to this location is not allowed.');

// Include the syndicate functions only once
require_once dirname(__FILE__) . '/helper.php';
$cloud = modTagCloudHelper::getCloud($params);
require JModuleHelper::getLayoutPath('mod_tagcloud');
