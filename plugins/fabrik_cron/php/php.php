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

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';

class plgFabrik_Cronphp extends plgFabrik_Cron {

	/**
	 * Check if the user can use the active element
	 *
	 * @param   object  &$model    calling the plugin list/form
	 * @param   string  $location  to trigger plugin on
	 * @param   string  $event     to trigger plugin on
	 *
	 * @return  bool can use or not
	 */

	public function canUse(&$model = null, $location = null, $event = null)
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
	  eval($params->get('cronphp_params'));
	  require_once JPATH_ROOT . '/plugins/fabrik_cron/php/scripts/' . $file;
	}

	/**
	 * show a new for entering the form actions options
	 */

	function renderAdminSettings()
	{
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