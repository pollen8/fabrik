<?php
/**
 * Installer manifest class
 *
 * @package com_fabrik
 * @author Rob Clayburn
 * @license GNU/GPL http://www.gnu.org/licenses/gpl.html
 * @copyright 2009 Developer Name
 */
class Com_FabrikInstallerScript
{

	protected $drivers = array('mysql_fab.php', 'mysqli_fab.php');

	/**
	 * run when the component is installed
	 * @param object $parent installer object
	 * @return bool
	 */

	function install($parent)
	{
		return true;
	}

	/**
	 * Check if there is a connection already installed if not create one
	 * by copying over the site's default connection
	 * @return bool
	 */

	protected function setConnection()
	{
		$db = JFactory::getDbo();
		$app = JFactory::getApplication();
		$row = new stdClass();
		$row->host = $app->getCfg('host');
		$row->user = $app->getCfg('user');
		$row->password = $app->getCfg('password');
		$row->database = $app->getCfg('db');
		$row->description = 'site database';
		$row->published = 1;
		$row->default = 1;
		$res = $db->insertObject('#__fabrik_connections', $row, 'id');
		return $res;
	}

	/**
	 * test to ensure that the main component params have a default setup
	 * @return bool
	 */

	protected function setDefaultProperties()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('extension_id, params')->from('#__extensions')->where('name = '.$db->Quote('fabrik'))->where('type = '.$db->Quote('component'));
		$db->setQuery($query);
		$row = $db->loadObject();
		$opts = new stdClass();
		$opts->fbConf_wysiwyg_label = 0;
		$opts->fbConf_alter_existing_db_cols = 0;
		$opts->spoofcheck_on_formsubmission = 0;

		if ($row && ($row->params == '{}' || $row->params == '')) {
			$json = $row->params;
			$query = $db->getQuery(true);
			$query->update('#__extensions')->set('params = '.$db->Quote($json))->where('extension_id = '.(int)$row->extension_id);
			$db->setQuery($query);
			if (!$db->query()) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Move over files into Joomla libraries folder
	 * @param object installer
	 * @param bool upgrade
	 * @return bool
	 */

	protected function moveFiles(&$installer, $upgrade = false)
	{

		jimport('joomla.filesystem.file');

		$componentFrontend  = 'components'.DS.'com_fabrik';

		$dest = 'libraries'.DS.'joomla'.DS.'document'.DS.'fabrikfeed';
		if (!JFolder::exists($dest)) {
			JFolder::create($dest);
		}

		$moveRes = JFolder::copy($componentFrontend.DS.'fabrikfeed', $dest, JPATH_SITE, true, true);
		if ($moveRes !== true) {
			echo "<p style=\"color:red\">failed to moved ".$componentFrontend.DS.'fabrikfeed'. ' to '.$dest.'</p>';
			return false;
		}

		$dest = 'libraries'.DS.'joomla'.DS.'database'.DS.'database';

		$driverInstallLoc = $componentFrontend.DS.'dbdriver'.DS;
		$moveRes = JFolder::copy($driverInstallLoc, $dest, JPATH_SITE, true, true);
		if ($moveRes !== true) {
			echo "<p style=\"color:red\">failed to moved ".$driverInstallLoc. ' to '.$dest.'</p>';
			return false;
		}
		return true;
	}

	/**
	 * Run when the component is unistalled.
	 * @param object $parent installer object
	 */

	function uninstall($parent)
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		$dest = JPATH_SITE.DS.'libraries'.DS.'joomla'.DS.'document'.DS.'fabrikfeed';
		if (JFolder::exists($dest)) {
			if (!JFolder::delete($dest)) {
				return false;
			}
		}

		$dest = JPATH_SITE.DS.'libraries'.DS.'joomla'.DS.'database'.DS.'database';
		foreach ($this->drivers as $driver) {
			if (JFile::exists($dest.DS.$driver)) {
				JFile::delete($dest.DS.$driver);
			}
		}
	}

	/**
	* god knows why but install component, uninstall component and install again and component_id is set to 0 for the menu items grrrrr
	*/
	protected function fixmMenuComponentId()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('extension_id')->from('#__extensions')->where('element =  "com_fabrik"');
		$db->setQuery($query);
		$id = $db->loadResult();
		$query->clear();
		$query->update('#__menu')->set('component_id = '.$id)->where('path LIKE "fabrik%"');
		$db->setQuery($query)->query();
		}

	/**
	 * Run when the component is updated
	 * @param object $parent installer object
	 */

	function update($parent)
	{
		/*if (!$this->moveFiles($parent, true)) {
			return false;
		} else {
			echo "<p style=\"color:green\">Libray files moved</p>";
		}*/
		return true;
	}

	/**
	 * Run before installation or upgrade run
	 * @param string $type discover_install (Install unregistered extensions that have been discovered.)
	 *  or install (standard install)
	 *  or update (update)
	 * @param object $parent installer object
	 */

	function preflight($type, $parent)
	{

	}

	/**
	 * Run after installation or upgrade run
	 * @param string $type discover_install (Install unregistered extensions that have been discovered.)
	 *  or install (standard install)
	 *  or update (update)
	 * @param object $parent installer object
	 */

	function postflight($type, $parent)
	{
		$this->fixmMenuComponentId();
		if ($type !== 'update') {
			if (!$this->setConnection()) {
				return false;
			}
		}
		echo "<p style=\"color:green\">Default connection created</p>";

		if (!$this->moveFiles($parent)) {
			return false;
		} else {
			echo "<p style=\"color:green\">Libray files moved</p>";
		}

		if ($type !== 'update') {
			if (!$this->setDefaultProperties()) {
				echo "<p>couldnt set default properties</p>";
				return false;
			}

		}
		echo "<p>Installation finished</p>";
			echo '<p><a target="_top" href="index.php?option=com_fabrik&amp;task=home.installSampleData">Click
here to install sample data</a></p>
	  ';

			$db = JFactory::getDbo();
			$db->setQuery("UPDATE #__extensions SET enabled = 1 WHERE type = 'plugin' AND (folder LIKE 'fabrik_%' OR (folder='system' AND element = 'fabrik'))");
			$db->query();
			// An example of setting a redirect to a new location after the install is completed
			//$parent->getParent()->set('redirect_url', 'http://www.google.com');

			$upgrade = JModel::getInstance('Upgrade', 'FabrikModel');
	}



}
