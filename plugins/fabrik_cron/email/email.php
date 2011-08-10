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

//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND.DS.'models'.DS.'plugin.php');
require_once(COM_FABRIK_FRONTEND.DS.'helpers'.DS.'html.php');

class plgFabrik_Cronemail extends FabrikPlugin {

	var $_counter = null;

	function canUse()
	{
		return true;
	}

	/**
	 * do the plugin action
	 * @return number of records updated
	 */

	function process(&$data)
	{
		$app = JFactory::getApplication();
		jimport('joomla.mail.helper');
		$params =& $this->getParams();
		$msg = $params->get('message');
		$to = $params->get('to');
		$w = new FabrikWorker();
		$MailFrom = $app->getCfg('mailfrom');
		$FromName = $app->getCfg('fromname');
		$subject = $params->get('subject', 'Fabrik cron job');
		$eval = $params->get('cronemail-eval');
		$condition = $params->get('cronemail_condition', '');
		$updates = array();
		foreach ($data as $group) {
			if (is_array($group)) {
				foreach ($group as $row) {
					if (!empty($condition)) {
						$this_condition = $w->parseMessageForPlaceHolder($condition, $row);
						if (eval($this_condition === false)) {
							continue;
						}
					}
					$row = JArrayHelper::fromObject($row);
					$thisto = $w->parseMessageForPlaceHolder($to, $row);
					if (JMailHelper::isEmailAddress($thisto)) {
						$thismsg = $w->parseMessageForPlaceHolder($msg, $row);
						if ($eval) {
							$thismsg = eval($thismsg);
						}
						$thissubject = $w->parseMessageForPlaceHolder($subject, $row);
						$res = JUTility::sendMail( $MailFrom, $FromName, $thisto, $thissubject, $thismsg, true);
					}
					$updates[] = $row['__pk_val'];

				}
			}
		}
		$field = $params->get('cronemail-updatefield');
		if (!empty( $updates) && trim($field ) != '') {
			//do any update found
			$listModel =& JModel::getInstance('list', 'FabrikFEModel');
			$listModel->setId($params->get('table'));
			$table =& $listModel->getTable();

			$connection = $params->get('connection');
			$field = $params->get('cronemail-updatefield');
			$value = $params->get('cronemail-updatefield-value');

			$field = str_replace("___", ".", $field);
			$query = "UPDATE $table->db_table_name set $field = " . $fabrikDb->Quote($value) . " WHERE $table->db_primary_key IN (" . implode(',', $updates) . ")";
			$fabrikDb =& $listModel->getDb();
			$fabrikDb->setQuery($query);
			$fabrikDb->query();
		}
		return count($updates);
	}

	/**
	 * show a new for entering the form actions options
	 */

	function renderAdminSettings()
	{
		//JHTML::stylesheet('fabrikadmin.css', 'administrator/components/com_fabrik/views/');
		$this->getRow();
		$pluginParams =& $this->getParams();

		$document =& JFactory::getDocument();
		?>
		<div id="page-<?php echo $this->_name;?>" class="pluginSettings" style="display:none">
		<?php
			echo $pluginParams->render('params');
			echo $pluginParams->render('params', 'fields');
			?>
			<fieldset>
				<legend><?php echo JText::_('update') ?></legend>
				<?php echo $pluginParams->render('params','update') ?>
			</fieldset>
		</div>

		<?php
		return ;
	}

}
?>