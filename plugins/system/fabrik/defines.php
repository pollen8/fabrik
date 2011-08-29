<?php

// any of these defines can be overwritten by copying this file to
// components/com_fabrik/user_defines.php

// no direct access
defined('_JEXEC') or die('Restricted access');

define("COM_FABRIK_BASE",  str_replace(DS.'administrator', '', JPATH_BASE).DS);
define("COM_FABRIK_FRONTEND",  COM_FABRIK_BASE.'components'.DS.'com_fabrik');
define("COM_FABRIK_LIVESITE",  str_replace('/administrator', '', JURI::base()));

define("FABRIKFILTER_TEXT", 0);
define("FABRIKFILTER_EVAL", 1);
define("FABRIKFILTER_QUERY", 2);
define("FABRKFILTER_NOQUOTES", 3);

/** @var delimiter used to define seperator in csv export */
define("COM_FABRIK_CSV_DELIMITER", ",");
define("COM_FABRIK_EXCEL_CSV_DELIMITER", ";");

/** @var string separator used in repeat elements/groups IS USED IN F3 */
define ("GROUPSPLITTER", "//..*..//");

//override JHTML
//JHTML::addIncludePath(JPATH_SITE.'/components/com_fabrik/helpers/');

//Register the element class with the loader
JLoader::register('JElement', JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_fabrik'.DS.'element.php');

if (JRequest::getCmd('option') != 'com_menus') {
	JLoader::import('components.com_fabrik.classes.formfield', JPATH_SITE.DS.'administrator', 'administrator.');
	JLoader::import('components.com_fabrik.classes.form', JPATH_SITE.DS.'administrator', 'administrator.');
}

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_fabrik'.DS.'tables'.DS.'fabtable.php');
require_once(COM_FABRIK_FRONTEND.DS.'models'.DS.'fabrik.php');
require_once(COM_FABRIK_FRONTEND.DS.'helpers'.DS.'arrayhelper.php');
require_once(COM_FABRIK_FRONTEND.DS.'helpers'.DS.'html.php');
require_once(COM_FABRIK_FRONTEND.DS.'helpers'.DS.'params.php');
require_once(COM_FABRIK_FRONTEND.DS.'models'.DS.'parent.php');
require_once(COM_FABRIK_FRONTEND.DS.'helpers'.DS.'parent.php');
require_once(COM_FABRIK_FRONTEND.DS.'models'.DS.'plugin.php');
require_once(COM_FABRIK_FRONTEND.DS.'models'.DS.'element.php');
require_once(COM_FABRIK_FRONTEND.DS.'models'.DS.'elementlist.php');

$app = JFactory::getApplication();
if ($app->isAdmin()) {
	require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_fabrik'.DS.'helpers'.DS.'fabrik.php');
}
?>