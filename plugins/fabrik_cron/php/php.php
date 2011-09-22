<?php

/**
* A cron task to email records to a give set of users
* @package Joomla
* @subpackage Fabrik
* @author Rob Clayburn
* @copyright (C) Rob Clayburn
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class plgFabrik_Cronphp extends FabrikPlugin {

	var $_counter = null;


	function canUse()
	{
		return true;
	}

	/**
	 * do the plugin action
	 *
	 */
	function process(&$data, &$listModel)
	{
	  $params = $this->getParams();
	  $file = JFilterInput::clean($params->get('cronphp_file'), 'CMD');
	  require_once(JPATH_ROOT.DS.'plugins'.DS.'fabrik_cron'.DS.'php'.DS.'scripts'.DS.$file);
	}

	/**
	 * show a new for entering the form actions options
	 */

	function renderAdminSettings()
	{
		//JHTML::stylesheet('fabrikadmin.css', 'administrator/components/com_fabrik/views/');
		$this->getRow();
		$pluginParams = $this->getParams();

		$document = JFactory::getDocument();
		?>
		<div id="page-<?php echo $this->_name;?>" class="pluginSettings" style="display:none">

		<?php
			// @TODO - work out why the language diddly doesn't work here, so we can make the above notes translateable?
			echo $pluginParams->render('params');
			echo $pluginParams->render('params', 'fields');
			?>
		</div>
		<?php
		return ;
	}

}
?>