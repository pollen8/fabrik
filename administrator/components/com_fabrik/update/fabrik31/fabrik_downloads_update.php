<?php
/**
 * @package		Joomla.Site
 * @copyright	Copyright (C) 2005 - Fabrikar.com All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Download the XML update files
 * ===================================================
 *
 * form: /public_html/update/fabrik31
 * to  : \administrator\components\com_fabrik\update\fabrik\
 *
 *
 * Run ant task build_local.xml
 * ===================================================
 *
 * FTP files
 * ===================================================
 * from: fabrik_build\output\pkg_fabrik_sink\packages\
 * and   fabrik_build\output\pkg_fabrik_x.x.zip
 *
 * to:   /public_html/media/downloads
 *
 * ====================================================
 *
 * FTP  the XML update files
 * ====================================================
 *
 * form: fabrik_build\output\admin\update\fabrik
 * to  : /public_html/update/fabrik31
 *
 * Update this scripts variables
 * =====================================================
 *
 * $prevVersion = '3.3.1';
 * $version = '3.3.2';
 * $joomla_version = '34';
 *
 * Finally Run this script!!!
 * =====================================================
 */

// Set flag that this is a parent file.
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);

if (file_exists(dirname(__FILE__) . '/defines.php')) {
	include_once dirname(__FILE__) . '/defines.php';
}

if (!defined('_JDEFINES')) {
	define('JPATH_BASE', dirname(__FILE__));
	require_once JPATH_BASE.'/includes/defines.php';
}

require_once JPATH_BASE.'/includes/framework.php';

// Mark afterLoad in the profiler.
JDEBUG ? $_PROFILER->mark('afterLoad') : null;

// Instantiate the application.
$app = JFactory::getApplication('site');


class FabrikTableDownload extends JTable
{

	/**
	 * Constructor
	 *
	 * @param   object  &$db  database object
	 */

	public function __construct(&$db)
	{
		parent::__construct('downloads', 'id', $db);
	}

}

$prevVersion = '3.2.1';
$version = '3.3.2';
$joomla_version = '34';
$now = JFactory::getDate()->toSql();
$db = JFactory::getDbo();
$query = $db->getQuery(true);

// Unpublish
$query->update('downloads')->set('published = 0')->where('joomla_version = ' . $db->quote($joomla_version));
$db->setQuery($query);
$db->query();

$query->clear();
$query->select('*')->from('downloads')->where('version = ' . $db->quote($prevVersion));
$db->setQuery($query);
$old = $db->loadObjectList();
echo "<pre>";print_r($old);
foreach ($old as $orig)
{
	unset($orig->id);
	$item = JTable::getInstance('Download', 'FabrikTable');
	$orig->version = $version;
	$orig->download = str_replace($prevVersion, $version, $orig->download);
	$orig->create_date = $now;
	$orig->hits = 0;
	$orig->published = 1;
	$orig->joomla_version = $joomla_version;
	// $orig->acl = 1; // all downloads NO! as some are exclusive downloads
	$item->bind($orig);
	$item->store();
}
