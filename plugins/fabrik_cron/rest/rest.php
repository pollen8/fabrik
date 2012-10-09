<?php

/**
 * A cron task to grab data from a REST API and insert it into a list
 * @package     Joomla
 * @subpackage  Fabrik
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-cron.php';

class plgFabrik_CronRest extends plgFabrik_Cron
{

	/**
	 * do the plugin action
	 * @param array data
	 * @param object list model
	 * @param object admin list model
	 * @return number of records updated
	 *
	 */
	public function process(&$data, &$listModel, &$adminListModel)
	{
		$params = $this->getParams();

		$config_method = 'GET';
		$config_userpass = $params->get('username') . ':' . $params->get('password');
		$config_headers[] = 'Accept: application/xml';
		$endpoint = $params->get('endpoint');

		// Here we set up CURL to grab the data from Unfuddle
		$chandle = curl_init();
		curl_setopt($chandle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($chandle, CURLOPT_URL, $endpoint);
		curl_setopt($chandle, CURLOPT_HTTPHEADER, $config_headers);
		curl_setopt($chandle, CURLOPT_USERPWD, $config_userpass);
		curl_setopt($chandle, CURLOPT_CUSTOMREQUEST, $config_method);
		$output = curl_exec($chandle);
		curl_close($chandle);

		$xml = new SimpleXMLElement($output);

		// Drill down to the specified xpath location for our data
		$xpath = $params->get('xpath');
		if ($xpath !== '')
		{
			$xml = $xml->xpath($xpath);
		}
		$adminListModel->dbTableFromXML($params->get('key'), $params->get('create_list'), $xml);
		$this->createList($listModel, $adminListModel);

	}

	protected function createList($listModel, $adminListModel)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$params = $this->getParams();
		$table = $params->get('create_list');
		if ($table == '')
		{
			return;
		}
		$db = FabrikWorker::getDbo();

		// See if we have a list that already points to the table
		$query = $db->getQuery(true);
		$query->select('id')->from('#__{package}_lists')->where('db_table_name = ' . $db->quoteName($table));
		$db->setQuery($query);
		$res = (int) $db->loadResult();

		$now = JFactory::getDate()->toSql();
		$user = JFactory::getUser();

		$data = array();

		// Fill in some default data
		$data['filter_action'] = 'onchange';
		$data['access'] = 1;
		$data['id'] = $res;
		$data['label'] = $table;
		$data['connection_id'] = 1;
		$data['db_table_name'] = $table;
		$data['published'] = 1;
		$data['created'] = $now;
		$data['created_by'] = $user->get('id');

		$input->set('jform', $data);
		$adminListModel->save($data);
	}

}
