<?php
/**
* Plugin element to render fileuploads of file type
* @package fabrik
* @author Rob Clayburn
* @copyright (C) Rob Clayburn
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class fileRender{

	var $output = '';
	/**
	 * @param object element model
	 * @param object element params
	 * @param string row data for this element
	 * @param object all row's data
	 */

	function renderListData(&$model, &$params, $file, $thisRow)
	{
		$this->render($model, $params, $file);
	}

	/**
	 * @param object element model
	 * @param object element params
	 * @param string row data for this element
	 */

	function render(&$element, &$params, $file)
	{
		jimport('joomla.filesystem.file');
		$filename = basename($file);
		$filename = strip_tags($filename);
		$ext = JFile::getExt($filename);
		$file = str_replace("\\", "/", COM_FABRIK_LIVESITE  . $file);
		$thumb_path = COM_FABRIK_BASE . 'media/com_fabrik/images/' . $ext . '.png';
		// $$$ hugh - using 'make_thumbnail' to mean 'use default $ext.png as an icon
		// instead of just putting the filename.
		if ($params->get('make_thumbnail', false) && JFile::exists($thumb_path)) {
			$thumb_file = COM_FABRIK_LIVESITE . "/media/com_fabrik/images/" . $ext . ".png";
			$this->output = "<a class=\"download-archive fabrik-filetype-$ext\" title=\"$file\" href=\"$file\"><img src=\"$thumb_file\" alt=\"$filename\"></a>";
		}
		else {
			$this->output = "<a class=\"download-archive fabrik-filetype-$ext\" title=\"$file\" href=\"$file\">" . $filename . "</a>";
		}
	}
}

?>