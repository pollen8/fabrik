<?php

/**
* Add an action button to the table to copy rows
* @package Joomla
* @subpackage Fabrik
* @author Rob Clayburn
* @copyright (C) Pollen 8 Design Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND . '/models/plugin-list.php');

class plgFabrik_ListDownload extends plgFabrik_List {

	protected $buttonPrefix = 'download';

	protected $msg = null;

	function button()
	{
		return "download files";
	}

	protected function buttonLabel()
	{
		return $this->getParams()->get('download_button_label', parent::buttonLabel());
	}


	/**
	 * (non-PHPdoc)
	 * @see FabrikModelTablePlugin::getAclParam()
	 */

	function getAclParam()
	{
		return 'download_access';
	}

	/**
	 * determine if the table plugin is a button and can be activated only when rows are selected
	 *
	 * @return bol
	 */

	function canSelectRows()
	{
		return $this->canUse();
	}

	/**
	 * do the plugin action
	 * @param object parameters
	 * @param object table model
	 */
	function process(&$params, &$model)
	{
		$ids	= JRequest::getVar('ids', array(), 'method', 'array');
		//$params = $model->getParams();
		$download_table = $params->get('download_table');
		$download_fk = $params->get('download_fk');
		$download_file = $params->get('download_file');
		$download_width = $params->get('download_width');
		$download_height = $params->get('download_height');
		$download_resize = ($download_width || $download_height) ? true : false;
		$table = $model->getTable();
		$filelist = array();
		$zip_err = '';

		if (empty($download_fk) && empty($download_file) && empty($download_table)) {
			return;
		}
		elseif (empty($download_fk) && empty($download_table) && !empty($download_file)) {
			foreach ($ids AS $id) {
				$row = $model->getRow($id);
				if (isset($row->$download_file)) {
					$this_file = JPATH_SITE . '/' . $row->$download_file;
					if (is_file($this_file))
					{
						$filelist[] = $this_file;
					}
				}
			}
		}
		else {
			$db = FabrikWorker::getDbo();
			$ids_string = implode(',',$ids);
			$query = "SELECT $download_file FROM $download_table WHERE $download_fk IN ($ids_string)";
			$db->setQuery($query);
			$results = $db->loadObjectList();
			foreach ($results AS $result) {
				$this_file = JPATH_SITE.DS.$result->$download_file;
				if (is_file($this_file)) {
					$filelist[] = $this_file;
				}
			}
		}
		if (!empty($filelist)) {
			if ($download_resize) {
				ini_set('max_execution_time', 300);
				require_once(COM_FABRIK_FRONTEND . '/helpers/image.php');
				$storage = $this->getStorage();
				$download_image_library = $params->get('download_image_library');
				$oImage = FabimageHelper::loadLib($download_image_library);
				$oImage->setStorage($storage);
			}
			$zipfile = tempnam(sys_get_temp_dir(), "zip");
			$zipfile_basename = basename($zipfile);
			$zip = new ZipArchive;
			$zipres = $zip->open($zipfile, ZipArchive::OVERWRITE);
			if ($zipres === true) {
				$ziptot = 0;
				$tmp_files = array();
				foreach ($filelist AS $this_file) {
					$this_basename = basename($this_file);
					if ($download_resize && $oImage->getImgType($this_file)) {
						$tmp_file = '/tmp/' . $this_basename;
						$oImage->resize($download_width, $download_height, $this_file, $tmp_file);
						$this_file = $tmp_file;
						$tmp_files[] = $tmp_file;
					}
					$zipadd = $zip->addFile($this_file, $this_basename);
					if ($zipadd === true) {
						$ziptot++;
					}
					else {
						$zip_err .= JText::_('ZipArchive add error: ' . $zipadd);
					}
				}
				if (!$zip->close()) {
					$zip_err = JText::_('ZipArchive close error') . ($zip->status);
				}

				if ($download_resize) {
					foreach ($tmp_files as $tmp_file) {
						$storage->delete($tmp_file);
					}
				}
				if ($ziptot > 0) {
					// Stream the file to the client
					$filesize = filesize($zipfile);
					if ($filesize > 0) {
						header("Content-Type: application/zip");
						header("Content-Length: " . filesize($zipfile));
						header("Content-Disposition: attachment; filename=\"$zipfile_basename.zip\"");
						echo JFile::read($zipfile);
						JFile::delete($zipfile);
						exit;
					}
					else {
						$zip_err .= JText::_('ZIP is empty');
					}
				}
			}
			else {
				$zip_err = JText::_('ZipArchive open error: ' . $zipres);
			}

		}
		else {
			$zip_err = "No files to ZIP!";
		}
		if (empty($zip_err)) {
			return true;
		}
		else {
			$this->msg = $zip_err;
			return false;
		}
	}

	function process_result($c)
	{
		return $this->msg;
	}

	/**
	 * return the javascript to create an instance of the class defined in formJavascriptClass
	 * @param object parameters
	 * @param object table model
	 * @param array [0] => string table's form id to contain plugin
	 * @return bool
	 */

	function onLoadJavascriptInstance($params, $model, $args)
	{
		parent::onLoadJavascriptInstance($params, $model, $args);
		$opts = $this->getElementJSOptions($model);
		$opts = json_encode($opts);
		$this->jsInstance = "new FbListDownload($opts)";
		return true;
	}

	function getStorage()
	{
		if (!isset($this->storage))
		{
			$params = $this->getParams();
			//$storageType = $params->get('fileupload_storage_type', 'filesystemstorage');
			$storageType = 'filesystemstorage';
			require_once(JPATH_ROOT . '/plugins/fabrik_element/fileupload/adaptors/' . $storageType . '.php');
			$this->storage = new $storageType($params);
		}
		return $this->storage;
	}

}
?>