<?php
/**
 * Plugin element to render fields
 * @package fabrikar
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();



class videoRender
{

	var $output = '';
	
	/**
	 * @param object element model
	 * @param object element params
	 * @param string row data for this element
	 * @param object all row's data
	 */

	function renderListData(&$model, &$params, $file, $oAllRowsData)
	{
		$this->render($model, $params, $file);
	}

	/**
	 * @param object element model
	 * @param object element params
	 * @param string row data for this element
	 */

	function render(&$model, &$params, $file)
	{
		$src = str_replace("\\", "/", COM_FABRIK_LIVESITE  . $file);
		ini_set('display_errors', true);
		require_once(COM_FABRIK_FRONTEND . '/libs/getid3/getid3/getid3.php');
		require_once(COM_FABRIK_FRONTEND . '/libs/getid3/getid3/getid3.lib.php');
			
		getid3_lib::IncludeDependency(COM_FABRIK_FRONTEND . '/libs/getid3/getid3/extension.cache.mysql.php', __FILE__, true);
		$config = JFactory::getConfig();
		$host = $config->getValue('host');
		$database = $config->getValue('db');
		$username = $config->getValue('user');
		$password = $config->getValue('password');
		$getID3 = new getID3_cached_mysql($host, $database, $username, $password);
		// Analyze file and store returned data in $ThisFileInfo
		$relPath = JPATH_SITE . "$file";
		$thisFileInfo = $getID3->analyze($relPath);

		if (array_key_exists('video', $thisFileInfo))
		{
			if (array_key_exists('resolution_x', $thisFileInfo['video']))
			{
				$w = $thisFileInfo['video']['resolution_x'];
				$h = $thisFileInfo['video']['resolution_y'];
			}
			else
			{
				$w = $thisFileInfo['video']['streams']['2']['resolution_x']; //for wmv files
				$h = $thisFileInfo['video']['streams']['2']['resolution_y'];
			}
			
			switch ($thisFileInfo['fileformat']) {
				//add in space for controller
				case 'quicktime':
					$h += 16;
					break;
				default:
					$h += 64;
			}
		}
		$file = str_replace("\\", "/", COM_FABRIK_LIVESITE . $file);
		
		switch ($thisFileInfo['fileformat']) {
			case 'asf':
				
				$this->output = '<object id="MediaPlayer" width='.$w.' height='.$h.' classid="CLSID:22D6f312-B0F6-11D0-94AB-0080C74C7E95" standby="Loading Windows Media Player components�" type="application/x-oleobject" codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=6,4,7,1112">

<param name="filename" value="http://yourdomain/yourmovie.wmv">
<param name="Showcontrols" value="true">
<param name="autoStart" value="false">

<embed type="application/x-mplayer2" src="'.$src.'" name="MediaPlayer" width='.$w.' height='.$h.'></embed>

</object>
				'
;			
				break;
			default:
				$this->output = "<object width=\"$w\" height=\"$h\"
			classid=\"clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B\"
			codebase=\"http://www.apple.com/qtactivex/qtplugin.cab\">
			<param name=\"src\" value=\"$src\">
			<param name=\"autoplay\" value=\"false\">
			<param name=\"controller\" value=\"true\">
			<embed src=\"$src\" width=\"$w\" height=\"$h\"
			autoplay=\"false\" controller=\"true\"
			pluginspage=\"http://www.apple.com/quicktime/download/\">
			</embed>
			
			</object>";
				break;
		}
		
	}
}

?>