<?php
/**
 * Fabrik Admin Package Model
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       1.6
 */

// No direct access.
defined('_JEXEC') or die;

require_once 'fabmodeladmin.php';

/**
 * Fabrik Admin Package Model
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       3.0
 */

class FabrikAdminModelPackage extends FabModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */

	protected $text_prefix = 'COM_FABRIK_PACKAGE';

	/**
	 * @var   array  list of protected table names
	 */
	protected $tables = array('#__fabrik_connections', '#__{package}_cron', '#__{package}_elements', '#__{package}_formgroup', '#__{package}_forms',
		'#__{package}_form_sessions', '#__{package}_groups', '#__{package}_joins', '#__{package}_jsactions', '#__{package}_lists',
		'#__{package}_log', '#__{package}_packages', '#__{package}_validations', '#__{package}_visualizations');

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable	A database object
	 *
	 * @since	1.6
	 */

	public function getTable($type = 'Package', $prefix = 'FabrikTable', $config = array())
	{
		$config['dbo'] = FabriKWorker::getDbo(true);
		return FabTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array  $data      Data for the form.
	 * @param   bool   $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed	A JForm object on success, false on failure
	 *
	 * @since	1.6
	 */

	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_fabrik.package', 'package', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since	1.6
	 */

	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_fabrik.edit.package.data', array());

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Save the pacakge
	 *
	 * @param   array  $data  jform data
	 *
	 * @return  bool
	 */

	public function save($data)
	{
		$canvas = $data['params']['canvas'];
		$canvas = json_decode($canvas);
		$o = new stdClass;
		if (is_null($canvas))
		{
			JError::raiseError(E_ERROR, 'malformed json package object');
		}
		$o->canvas = $canvas;
		$data['params'] = json_encode($o);
		$return = parent::save($data);
		$data['id'] = $this->getState($this->getName() . '.id');
		$packageId = $this->getState($this->getName() . '.id');
		$blocks = is_object($o->canvas) ? $o->canvas->blocks : array();
		foreach ($blocks as $fullkey => $ids)
		{
			$key = FabrikString::rtrimword($fullkey, 's');
			$tbl = JString::ucfirst($key);
			foreach ($ids as $id)
			{
				$item = $this->getTable($tbl);
				$item->load($id);
				if ($key == 'list')
				{
					// Also assign the form to the package
					$form = $this->getTable('Form');
					$form->load($item->form_id);

					if (!in_array($form->id, $blocks->form))
					{
						$o->canvas->blocks->form[] = $item->id;
					}
				}
			}
		}
		// Resave the data to update blocks
		$data['params'] = json_encode($o);
		$return = parent::save($data);
		return $return;
	}

	/**
	 * Export the package
	 *
	 * @param   array  $ids  package ids to export
	 *
	 * @return  void
	 */

	public function export($ids = array())
	{
		jimport('joomla.filesystem.archive');
		foreach ($ids as $id)
		{
			$row = $this->getTable();

			$row->load($id);
			$this->outputPath = JPATH_ROOT . '/tmp/' . $this->getComponentName($row) . '/';
			$json = $row->params;
			$row->params = json_decode($row->params);
			$row->blocks = $row->params->canvas->blocks;
			$componentZipPath = $this->outputPath . 'packages/com_' . $this->getComponentName($row) . '.zip';
			$pkgName = 'pkg_' . $this->getComponentName($row) . '.zip';
			$packageZipPath = $this->outputPath . $pkgName;
			if (JFile::exists($componentZipPath))
			{
				JFile::delete($componentZipPath);
			}
			$filenames = array();
			$row2 = clone ($row);
			$row2->params = $json;

			$filenames[] = $this->makeInstallSQL($row);
			$filenames[] = $this->makeUnistallSQL($row);
			$filenames[] = $this->makeXML($row);
			$filenames[] = $this->makeComponentManifestClass($row2);

			$this->copySkeleton($row, $filenames);
			$archive = JArchive::getAdapter('zip');

			$files = array();
			$this->addFiles($filenames, $files, $this->outputPath);

			$ok = $archive->create($componentZipPath, $files);
			if (!$ok)
			{
				JError::raiseError(500, 'Unable to create zip in ' . $componentZipPath);
			}
			// Copy that to root
			$ok = JFile::copy($componentZipPath, $this->outputPath . 'com_' . $this->getComponentName($row) . '.zip');

			// Now lets create the Joomla install package

			$plugins = $this->findPlugins($row);
			$this->zipPlugins($row, $plugins);
			$filenames = FArrayHelper::extract($plugins, 'fullfile');

			$filenames[] = $componentZipPath;

			// Have to add this LAST to filenames
			$filenames[] = $this->makePackageXML($row, $plugins);

			$files = array();
			$this->addFiles($filenames, $files, $this->outputPath);

			$ok = $archive->create($packageZipPath, $files);
			if (!$ok)
			{
				JError::raiseError(500, 'Unable to create zip in ' . $componentZipPath);
			}
			// $this->triggerDownload($pkgName, $packageZipPath);
			// $this->cleanUp($pkgName);
		}
	}

	/**
	 * Start the zip download
	 *
	 * @param   string  $filename  saved zip name
	 * @param   stirng  $filepath  server path for zip
	 *
	 * @return  void
	 */

	protected function triggerDownload($filename, $filepath)
	{
		$document = JFactory::getDocument();
		$size = filesize($filepath);

		$document->setMimeEncoding('application/zip');
		$str = JFile::read($filepath);

		// Set the response to indicate a file download
		JResponse::setHeader('Content-Type', 'application/force-download');

		// JResponse::setHeader('Content-Type', 'application/zip');
		JResponse::setHeader('Content-Length', $size);
		JResponse::setHeader('Content-Disposition', 'attachment; filename="' . basename($filepath) . '"');
		JResponse::setHeader('Content-Transfer-Encoding', 'binary');

		JResponse::setBody($str);
		echo JResponse::toString(false);
	}

	/**
	 * Remove unwanted tmp files
	 *
	 * @param   string  $pkgName  the package zip name (the file we dont want to delete)
	 *
	 * @return  void
	 */

	protected function cleanUp($pkgName)
	{
		$exclude = array(($pkgName));
		$files = JFolder::files($this->outputPath, '.', false, true, $exclude);
		foreach ($files as $file)
		{
			JFile::delete($file);
		}
		$folders = JFolder::folders($this->outputPath, '.', false, true);
		foreach ($folders as $folder)
		{
			JFolder::delete($folder);
		}
	}

	/**
	 * Create the component.manifest.class.php file which will populate the components
	 * forms/lists/elements etc
	 *
	 * @param   object  $row  package
	 *
	 * @return  string	path name to created file
	 */

	protected function makeComponentManifestClass($row)
	{
		$return = array();
		$return[] = "<?php ";
		$row->id = null;
		$row->external_ref = 1;
		$rows = array($row);
		$return[] = "class com_" . $row->component_name . "InstallerScript{";
		$return[] = "";
		$return[] = "\tfunction uninstall(\$parent)";
		$return[] = "\t\t{";
		$return[] = "\t\t\$m = \$parent->getParent()->manifest;";
		$return[] = "\t\t\$db = JFactory::getDbo();";
		$return[] = "\t\t\$query = \$db->getQuery(true);";
		$return[] = "\t\t\$query->delete('#__fabrik_packages')->where('external_ref <> \"\" AND component_name = ' .
		\$db->quote(\$m->name) . ' AND version = ' . \$db->quote(\$m->version));";
		$return[] = "\t\t\$db->setQuery(\$query);";
		$return[] = "\t\t\$db->query();";
		$return[] = "\t\t\$app = JFactory::getApplication('administrator');";
		$return[] = "\t\t\$app->setUserState('com_fabrik.package', '');";
		$return[] = "\t\t}";
		$return[] = "";
		$return[] = "\tfunction postflight(\$type, \$parent) {";
		$return[] = "\t\t" . "\$db = JFactory::getDbo();";
		$return[] = "\t\t" . $this->rowsToInsert('#__fabrik_packages', $rows, $return);
		$return[] = "\t\t" . "\$package_id = \$db->insertid();";
		$lookups = $this->getInstallItems($row);
		$listModel = JModelLegacy::getInstance('list', 'FabrikFEModel');
		$lists = $lookups->list;
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);

		foreach ($lookups->visualization as $vid)
		{
			$query->select('*')->from('#__{package}_visualizations')->where('id = ' . $vid);
			$db->setQuery($query);
			$viz = $db->loadObjectList();
			$this->rowsToInsert('#__' . $row->component_name . '_visualizations', $viz, $return);
		}

		foreach ($lists as $listid)
		{
			$query->clear();
			$query->select('*')->from('#__{package}_lists')->where('id = ' . (int) $listid);
			$db->setQuery($query);
			$list = $db->loadObjectList();
			$this->rowsToInsert('#__' . $row->component_name . '_lists', $list, $return);

			// Form
			$query->clear();
			$query->select('*')->from('#__{package}_forms')->where('id = ' . (int) $list[0]->form_id);
			$db->setQuery($query);
			$forms = $db->loadObjectList();
			$this->rowsToInsert('#__' . $row->component_name . '_forms', $forms, $return);

			// Form groups
			$query->clear();
			$query->select('*')->from('#__{package}_formgroup')->where('form_id = ' . (int) $list[0]->form_id);
			$db->setQuery($query);
			$formgroups = $db->loadObjectList();
			$this->rowsToInsert('#__' . $row->component_name . '_formgroup', $formgroups, $return);

			$groupids = array();
			foreach ($formgroups as $formgroup)
			{
				$groupids[] = $formgroup->group_id;
			}
			// Groups
			$query->clear();
			$query->select('*')->from('#__{package}_groups')->where('id IN (' . implode(',', $groupids) . ')');
			$db->setQuery($query);
			$groups = $db->loadObjectList();
			$this->rowsToInsert('#__' . $row->component_name . '_groups', $groups, $return);

			// Elements
			$query->clear();
			$query->select('*')->from('#__{package}_elements')->where('group_id IN (' . implode(',', $groupids) . ')');
			$db->setQuery($query);
			$elements = $db->loadObjectList();
			$elementids = array();
			foreach ($elements as $element)
			{
				$elementids[] = $element->id;
			}
			$this->rowsToInsert('#__' . $row->component_name . '_elements', $elements, $return);

			// Joins
			$query->clear();
			$query->select('*')->from('#__{package}_joins')
				->where('list_id IN (' . implode(',', $lists) . ') OR element_id IN (' . implode(',', $elementids) . ')');
			$db->setQuery($query);
			$joins = $db->loadObjectList();
			$this->rowsToInsert('#__' . $row->component_name . '_joins', $joins, $return);

			// JS actions
			$query->clear();
			$query->select('*')->from('#__{package}_jsactions')->where('element_id IN (' . implode(',', $elementids) . ')');
			$db->setQuery($query);
			$jsactions = $db->loadObjectList();
			$this->rowsToInsert('#__' . $row->component_name . '_jsactions', $jsactions, $return);

			// JS actions
			$query->clear();
			$query->select('*')->from('#__{package}_validations')->where('element_id IN (' . implode(',', $elementids) . ')');
			$db->setQuery($query);
			$validations = $db->loadObjectList();
			$this->rowsToInsert('#__' . $row->component_name . '_validations', $validations, $return);

		}
		/**
		 * ok write the code to update components/componentname/componentname.php
		 * have to do this in the installer as we don't know what package id the component will be installed as
		 */
		$xmlname = str_replace('com_', '', $row->component_name);
		$return[] = "\t\t\$path = JPATH_ROOT . '/components/com_" . "$row->component_name/$xmlname.php';";
		$return[] = "\t\t\$buffer = JFile::read(\$path);";
		$return[] = "\t\t\$buffer = str_replace('{packageid}', \$package_id, \$buffer);";
		$return[] = "\t\tJFile::write(\$path, \$buffer);";
		$return[] = "\t}";
		$return[] = "}";
		$return[] = "?>";
		$return = implode("\n", $return);
		$path = $this->outputPath . $this->manifestClassFileName($row);
		if (!JFile::write($path, $return))
		{
			JError::raiseError(500, 'didnt write to ' . $path);
		}
		return $path;
	}

	/**
	 * Build the SQL for inserting rows
	 *
	 * @param   string  $table    db table name
	 * @param   array   $rows     to insert
	 * @param   array   &$return  sql statements parsed by ref
	 *
	 * @return  void
	 */

	protected function rowsToInsert($table, $rows, &$return)
	{
		$db = FabrikWorker::getDbo(true);

		foreach ($rows as $row)
		{
			$fmtsql = 'INSERT INTO ' . $db->quoteName($table) . ' (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s';
			$fields = array();
			$values = array();
			$updates = array();
			foreach (get_object_vars($row) as $k => $v)
			{
				if (is_array($v) or is_object($v) or $v === null)
				{
					continue;
				}
				if ($k[0] == '_')
				{
					// Internal field
					continue;
				}

				$v = str_replace($db->getPrefix(), '#__', $v);
				$val = $db->quote($v);
				$fields[] = $db->quoteName($k);
				$values[] = $val;
				$updates[] = $db->quoteName($k) . ' = ' . $val;
			}
			$sql = sprintf($fmtsql, implode(",", $fields), implode(",", $values), implode(',', $updates)) . ';';
			$return[] = "\t\t\$db->setQuery(\"$sql\");";
			$return[] = "\t\t\$db->query();";
		}
	}

	/**
	 * recurisive function to add files and folders into the zip
	 *
	 * @param   array   $filenames  list of file names to add $filenames
	 * @param   array   &$files     list of already added files files
	 * @param   string  $root       root path
	 *
	 * @return  array  files
	 */

	protected function addFiles($filenames, &$files, $root = '')
	{
		$root = JPath::clean($root);
		foreach ($filenames as $fpath)
		{
			$fpath = JPath::clean($fpath);
			$zippath = str_replace($root, '', $fpath);
			if (JFolder::exists($fpath))
			{
				$tmpFiles = JFolder::files($fpath, '.', true, true);
				$this->addFiles($tmpFiles, $files, $root);
			}
			else
			{
				$data = JFile::read($fpath);
				if ($data === false)
				{
					JError::raiseNotice(500, 'could not read ' . $fpath);
				}
				$files[] = array('name' => $zippath, 'data' => $data);
			}
		}
		return $files;
	}

	/**
	 * Get component name
	 *
	 * @param   object  $row  package
	 *
	 * @return string
	 */

	protected function getComponentName($row)
	{
		return $row->component_name . '_' . $row->version;
	}

	/**
	 * get the lists, forms etc that have been assigned in the package admin edit screen
	 *
	 * @param   object  $row  package
	 *
	 * @return  array  items
	 */

	protected function getInstallItems($row)
	{
		if (isset($this->items))
		{
			return $this->items;
		}
		$this->items = $row->blocks;
		return $this->items;
	}

	/**
	 * create the SQL install file
	 *
	 * @param   object  $row  package
	 *
	 * @return  string  path
	 */

	protected function makeInstallSQL($row)
	{
		$sql = '';
		$config = JFactory::getConfig();
		$db = FabrikWorker::getDbo(true);

		// Create the sql for the cloned fabrik meta data tables
		foreach ($this->tables as $table)
		{
			$db->setQuery('SHOW CREATE TABLE ' . $table);
			$tbl = $db->loadRow();

			$tbl = str_replace('_fabrik_', '_' . $row->component_name . '_', $tbl[1]);
			$tbl = str_replace($config->get('dbprefix'), '#__', $tbl);
			$sql .= str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $tbl) . ";\n\n";

			$table = str_replace(array('_fabrik_', '{package}'), array('_' . $row->component_name . '_', $row->component_name), $table);
			$sql .= 'TRUNCATE TABLE ' . $table . ";\n\n";
		}
		foreach ($row->blocks as $block => $ids)
		{
			$key = FabrikString::rtrimword($block, 's');
		}

		// Create the sql to build the db tables that store the data.
		$formModel = JModelLegacy::getInstance('form', 'FabrikFEModel');

		$lookups = $this->getInstallItems($row);
		$lids = $lookups->list;
		JArrayHelper::toInteger($lids);
		foreach ($lids as $lid)
		{
			$listModel = JModelLegacy::getInstance('list', 'FabrikFEModel');
			$listModel->setId($lid);
			$sql .= "\n\n" . $listModel->getCreateTableSQL(true);
		}

		$plugins = array();
		foreach ($lookups->form as $fid)
		{
			$formModel->setId($fid);
			if (!in_array($fid, $lookups->list))
			{
				$lookups->list[] = $fid;
			}
			// @FIXME get sql to create tables for dbjoin/cdd elements (need to do if not exists)
			$dbs = $formModel->getElementOptions(false, 'name', true, true, array());
		}
		$sql .= "\n\n";
		foreach ($lookups->visualization as $vid)
		{
			$vrow = FabTable::getInstance('Visualization', 'FabrikTable');
			$vrow->load($vid);
			$visModel = JModelLegacy::getInstance($vrow->plugin, 'fabrikModel');
			$visModel->setId($vid);
			$listModels = $visModel->getlistModels();
			foreach ($listModels as $lmodel)
			{
				$sql .= $lmodel->getCreateTableSQL(true);

				// Add the table ids to the $lookups->list
				if (!in_array($lmodel->getId(), $lookups->list))
				{
					$lookups->list[] = $lmodel->getId();
				}
			}
		}
		$path = $this->outputPath . 'admin/sql/install.mysql.uft8.sql';
		JFile::write($path, $sql);
		return $path;
	}

	/**
	 * get a list of plugins that the package uses
	 *
	 * @param   object  $row  package
	 *
	 * @return array plugins
	 */

	protected function findPlugins($row)
	{
		$listModel = JModelLegacy::getInstance('list', 'FabrikFEModel');
		$formModel = JModelLegacy::getInstance('form', 'FabrikFEModel');
		$lookups = $this->getInstallItems($row);
		$plugins = array();
		foreach ($lookups->form as $fid)
		{
			$formModel->setId($fid);
			$groups = $formModel->getGroupsHiarachy();
			foreach ($groups as $groupModel)
			{
				$elements = $groupModel->getMyElements();
				foreach ($elements as $element)
				{
					$item = $element->getElement();
					$id = 'element_' . $item->plugin;
					$o = new stdClass;
					$o->id = $id;
					$o->name = $item->plugin;
					$o->group = 'fabrik_element';
					$o->file = 'plg_fabrik_' . $id . '.zip';
					$plugins[$id] = $o;
				}
				// Form plugins
				$fplugins = $formModel->getParams()->get('plugins');
				foreach ($fplugins as $fplugin)
				{
					$id = 'form_' . $fplugin;
					$o = new stdClass;
					$o->id = $id;
					$o->name = $fplugin;
					$o->group = 'fabrik_form';
					$o->file = 'plg_fabrik_' . $id . '.zip';
					$plugins[$id] = $o;
				}
			}
		}
		foreach ($lookups->list as $id)
		{
			$listModel->setId($id);
			$tplugins = $listModel->getParams()->get('plugins');
			foreach ($tplugins as $tplugin)
			{
				$id = 'list_' . $tplugin;
				$o = new stdClass;
				$o->id = $id;
				$o->name = $tplugin;
				$o->group = 'fabrik_list';
				$o->file = 'plg_fabrik_' . $id . '.zip';
				$plugins[$id] = $o;
			}
		}
		return $plugins;
	}

	/**
	 * Create the SQL unistall file
	 *
	 * @param   object  $row  package
	 *
	 * @return  string  path
	 */

	protected function makeUnistallSQL($row)
	{
		$sql = array();
		$db = JFactory::getDbo();

		/**
		 * dont do this as the db table may be used by the main fabrik component
		 * perhaps later on we can add some php to the manifest class to inteligently remove orphaned db tables.
		 */

		/*
		$listModel = JModelLegacy::getInstance('list', 'FabrikFEModel');
		$lookups = $this->getInstallItems($row);
		$tids = $lookups->list;
		JArrayHelper::toInteger($tids);
		foreach ($tids as $tid) {
		    $listModel->setId($tid);
		    $table = $listModel->getTable()->db_table_name;
		    $sql[] = "DELETE FROM ". $db->quoteName($table).";";
		    $sql[] = "DROP TABLE ". $db->quoteName($table).";";
		} */

		/**
		 * drop the meta tables as well (currently we don't have a method for
		 * upgrading a package. So unistall should remove these meta tables
		 */
		foreach ($this->tables as $table)
		{
			// As we share the connection table we don't want to remove it on package unistall
			if ($table == '#__fabrik_connections')
			{
				continue;
			}
			$table = str_replace('{package}', $row->component_name, $table);
			$sql[] = 'DROP TABLE IF EXISTS ' . $db->quoteName($table) . ';';
		}

		$path = $this->outputPath . 'admin/sql/uninstall.mysql.uft8.sql';
		JFile::write($path, implode("\n", $sql));
		return $path;
	}

	/**
	 * copy the files from the skeleton component into the tmp folder
	 * ready to be zipped up
	 *
	 * @param   object  $row         package
	 * @param   array   &$filenames  files to copy
	 *
	 * @return  void
	 */

	protected function copySkeleton($row, &$filenames)
	{
		$skeltonFolder = JPATH_ADMINISTRATOR . '/components/com_fabrik/com_fabrik_skeleton/';

		$name = str_replace('com_', '', $row->component_name);
		JFolder::create($this->outputPath . 'site');
		JFolder::create($this->outputPath . 'site/views');
		JFolder::create($this->outputPath . 'admin');
		JFolder::create($this->outputPath . 'admin/images');

		$from = $skeltonFolder . 'fabrik_skeleton.php';
		$to = $this->outputPath . 'site/' . $name . '.php';
		if (JFile::exists($to))
		{
			JFile::delete($to);
		}
		JFile::copy($from, $to);
		$filenames[] = $to;

		// Admin holding page
		$from = $skeltonFolder . 'admin.php';
		$to = $this->outputPath . 'admin/' . $name . '.php';
		if (JFile::exists($to))
		{
			JFile::delete($to);
		}
		JFile::copy($from, $to);
		$filenames[] = $to;

		/* $from = $skeltonFolder.'index.html';
		$to = $this->outputPath . 'admin/installation/index.html';
		JFile::copy($from, $to);
		$filenames[] = $to; */

		$from = $skeltonFolder . 'index.html';
		$to = $this->outputPath . 'site/index.html';
		JFile::copy($from, $to);
		$filenames[] = $to;

		$from = $skeltonFolder . 'index.html';
		$to = $this->outputPath . 'admin/index.html';
		JFile::copy($from, $to);
		$filenames[] = $to;

		$from = $skeltonFolder . 'images/';
		$to = $this->outputPath . 'admin/images';
		JFolder::copy($from, $to, '', true);
		$filenames[] = $to;

		$from = $skeltonFolder . 'views/';
		$to = $this->outputPath . 'site/views';
		JFolder::copy($from, $to, '', true);
		$filenames[] = $to;

		/*//testing this tmp file
		$from = $skeltonFolder.'fabrik_skeleton.php';
		$to = $this->outputPath . 'admin/' . $name.'.php';
		JFile::copy($from, $to);
		$filenames[] = $to;*/

	}

	/**
	 * rather than just installing the component we want to create a package
	 * containing the component PLUS any Fabrik plugins that component might use
	 *
	 * @param   object  $row      package
	 * @param   array   $plugins  plugins to include in the package
	 *
	 * @return  string  filename
	 */

	protected function makePackageXML($row, $plugins)
	{
		/**
		 * @TODO add update url e.g:
		 * <update>http://fabrikar.com/update/packages/free</update>
		 */
		$version = new JVersion;
		$date = JFactory::getDate();
		$xmlname = 'pkg_' . str_replace('com_', '', $row->component_name);
		$str = '<?xml version="1.0" encoding="UTF-8" ?>
<install type="package" version="' . $version->RELEASE . '">
	<name>' . $row->label . '</name>
	<packagename>' . str_replace('com_', '', $row->component_name) . '</packagename>
	<version>' . $row->version
			. '</version>
	<url>http://www.fabrikar.com</url>
	<packager>Rob Clayburn</packager>
	<author>Rob Clayburn</author>
	<creationDate>' . $date->format('M Y')
			. '</creationDate>
	<packagerurl>http://www.fabrikar.com</packagerurl>
	<description>Created by Fabrik</description>

	<files folder="packages">
		<file type="component" id="' . $row->component_name . '">com_' . $this->getComponentName($row) . '.zip</file>
';
		foreach ($plugins as $plugin)
		{
			$str .= '
		<file type="plugin"	id="' . $plugin->id . '"	group="' . $plugin->group . '">' . $plugin->file . '</file>';
		}
		$str .= '
	</files>
</install>';
		JFile::write($this->outputPath . $xmlname . '.xml', $str);
		return $this->outputPath . $xmlname . '.xml';
	}

	/**
	 * get the file name of the file containing the class that is run on install.
	 *
	 * @param   object  $row  package
	 *
	 * @return  string	filename
	 */

	protected function manifestClassFileName($row)
	{
		$xmlname = str_replace('com_', '', $row->component_name);
		return $xmlname . '.manifest.class.php';
	}

	/**
	 * Zip up the plugins used by the package
	 *
	 * @param   object  $row       package
	 * @param   array   &$plugins  plugins to zip
	 *
	 * @return  void
	 */

	protected function zipPlugins($row, &$plugins)
	{
		$archive = JArchive::getAdapter('zip');
		JFolder::create($this->outputPath . 'packages');
		foreach ($plugins as &$plugin)
		{

			$filenames = array(JPATH_ROOT . '/plugins/' . $plugin->group . '/' . $plugin->name);
			$files = array();
			$root = JPATH_ROOT . '/plugins/' . $plugin->group . '/' . $plugin->name . '/';
			$this->addFiles($filenames, $files, $root);
			$plugin->file = str_replace('{version}', $row->version, $plugin->file);
			$pluginZipPath = $this->outputPath . 'packages/' . $plugin->file;
			$ok = $archive->create($pluginZipPath, $files);
			$plugin->fullfile = $pluginZipPath;
			if (!$ok)
			{
				JError::raiseError(500, 'Unable to create zip in ' . $pluginZipPath);
			}
		}
	}

	/**
	 * Create the component installation xml file
	 *
	 * @param   object  $row  package
	 *
	 * @return  string  path to where tmp xml file is saved
	 */

	protected function makeXML($row)
	{
		$date = JFactory::getDate();
		$version = new JVersion;
		$xmlname = str_replace('com_', '', $row->component_name);
		$str = '<?xml version="1.0" encoding="utf-8"?>
<extension
	xmlns="http://www.joomla.org"
	xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.joomla.org extension.xsd "
	method="upgrade"
	client="site"
	version="' . $version->RELEASE . '"
	type="component">

	<name>' . $row->component_name . '</name>
	<creationDate>' . $date->format('M Y')
			. '</creationDate>
	<author>Fabrik</author>
	<copyright>Pollen 8 Design Ltd</copyright>
	<license>GNU/GPL</license>
	<authorEmail>rob@pollen-8.co.uk</authorEmail>
	<authorUrl>www.pollen-8.co.uk</authorUrl>
	<version>' . $row->version
			. '</version>
	<description>Created with Fabrik: THE Joomla Application Creation Component</description>
	<install>
		<sql>
			<file charset="utf8" driver="mysql">sql/install.mysql.uft8.sql</file>
			<file charset="utf8" driver="mysqli">sql/install.mysql.uft8.sql</file>
		</sql>
	</install>

	<uninstall>
		<sql>
			<file charset="utf8" driver="mysql">sql/uninstall.mysql.uft8.sql</file>
			<file charset="utf8" driver="mysqli">sql/uninstall.mysql.uft8.sql</file>
		</sql>
	</uninstall>

	<scriptfile>' . $this->manifestClassFileName($row) . '</scriptfile>

	<files folder="site">
		<folder>views</folder>
		<file>' . $xmlname
			. '.php</file>
		<file>index.html</file>
	</files>

	<administration>
		<menu img="../administrator/components/com_fabrik/images/logo.png">' . $row->label
			. '</menu>

		<files folder="admin">
			<folder>images</folder>
			<folder>sql</folder>
			<file>index.html</file>
			<file>' . $xmlname . '.php</file>
		</files>
	</administration>

</extension>';
		$path = $this->outputPath . $xmlname . '.xml';
		JFile::write($path, $str);
		return $path;
	}

	/**
	 * Get the J Form
	 *
	 * @param   array  $data      form data
	 * @param   bool   $loadData  load the data yes/no
	 *
	 * @return boolean|JForm
	 */

	public function getPackageListForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_fabrik.packagelist', 'packagelist', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}
}
