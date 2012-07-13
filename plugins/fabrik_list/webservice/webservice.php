<?php

/**
* Add an action button to run PHP
* @package Joomla
* @subpackage Fabrik
* @author Rob Clayburn
* @copyright (C) Pollen 8 Design Ltd
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-list.php';

class plgFabrik_ListWebservice extends plgFabrik_List
{

	/**@var	string	button prefix name */
	protected $buttonPrefix = 'update_col';

	/**
	 * does the plugin render a button at the top of the list?
	 * @return  bool
	 */
	public function topButton()
	{
		return true;
	}

	/**
	 * create the HTML for rendering a button in the top button list
	 * @return  string	<a> link
	 */
	public function topButton_result()
	{
		if ($this->canUse()) {
			$name = $this->_getButtonName();
			$label = $this->buttonLabel();
			$imageName = $this->getParams()->get('list_' . $this->buttonPrefix . '_image_name', $this->buttonPrefix . '.png');
			$img = FabrikHelperHTML::image($imageName, 'list', '',  $label);
			return '<a href="#" class="'.$name.' listplugin" title="'.$label.'">'.$img.'<span>'.$label.'</span></a>';
		}
	}

	/**
	 * row button set up code
	 * @return  string
	 */
	function button()
	{
		return "run webservice";
	}

	/**
	 * @see plgFabrik_List::button_result()
	 */

	public function button_result()
	{
		return '';
	}

	protected function buttonLabel()
	{
		return $this->getParams()->get('webservice_button_label', parent::buttonLabel());
	}

	/**
	 * (non-PHPdoc)
	 * @see FabrikModelTablePlugin::getAclParam()
	 */

	function getAclParam()
	{
		return 'webservice_access';
	}

	/**
	 * determine if the list plugin is a button and can be activated only when rows are selected
	 * @return  bool
	 */

	function canSelectRows()
	{
		return false;
	}

	/**
	 * do the plug-in action
* @param   object	parameters
* @param   object	table model
* @param   array	custom options
	 */

	function process(&$params, &$model, $opts = array())
	{
		JLoader::import('webservice', JPATH_SITE . '/components/com_fabrik/models/');
		$params = $this->getParams();
		$fk = $params->get('webservice_foreign_key');
		$formModel = $model->getFormModel();
		$fk = $formModel->getElement($fk, true)->getElement()->name;
		$credentials = $this->getCredentials();

		$driver = $params->get('webservice_driver');
		$opts = array(
			'driver' => $driver,
			'endpoint' => $params->get('webservice_url'),
			'credentials' => $credentials
		);
		$service = FabrikWebService::getInstance($opts);
		if (JError::isError($service)) {
			echo $service->getMessage();
			JError::raiseError(500, $service->getMessage());
			jexit();
		}
		$filters = $this->getServiceFilters($service);
		$service->setMap($this->getMap($formModel));
		$filters = array_merge($opts['credentials'], $filters);

		$method = $params->get('webservice_get_method');
		$startPoint = $params->get('webservice_start_point');

		$serviceData = $service->get($method, $filters, $startPoint, null);

		$update = (bool)$params->get('webservice_update_existing', false);
		$service->storeLocally($model, $serviceData, $fk, $update);
		$this->msg = JText::sprintf($params->get('webservice_msg'), $service->addedCount, $service->updateCount);
		return true;
	}

	/**
	 * get the data map to transform web service data into list data
* @param   object	$formModel
	 * @return  array	data map
	 */
	protected function getMap($formModel)
	{
		$params = $this->getParams();
		$map = json_decode($params->get('webservice_map'));
		$return = array();
		$from = $map->map_from;
		$to = $map->map_to;
		$match = $map->map_match;
		$value = $map->map_value;
		$eval = $map->map_eval;
		$n = count($from);
		for ($i = 0; $i < $n; $i ++)
		{
			$tid = $formModel->getElement($to[$i], true)->getElement()->name;
			$return[] = array('from' => $from[$i], 'to' => $tid, 'value' => $value[$i], 'match' => $match[$i], 'eval' => (bool)$eval[$i]);
		}
		return $return;
	}

	/**
	 * get an array of key/value filters to send to the web serive
* @param   object	$service
	 * @return  array	key/val pairs
	 */

	protected function getServiceFilters($service)
	{
		$params = $this->getParams();
		$filters = json_decode($params->get('webservice_filters'));
		$return = array();
		$keys = $filters->webservice_filters_key;
		$vals = $filters->webservice_filters_value;
		$types = $filters->webservice_filters_type;
		$n = count($keys);
		for ($i = 0; $i < $n; $i ++)
		{
			$return[$keys[$i]] = $service->getFilterValue($vals[$i], $types[$i]);
		}
		return $return;
	}

	/**
	 * get sign in credentials to the service
	 * @return  array	login credentials
	 */

	protected function getCredentials()
	{
		$params = $this->getParams();
		$credentials = json_decode($params->get('webservice_credentials'));
		$return = array();
		$keys = isset($credentials->webservice_credentials_key) ? $credentials->webservice_credentials_key : array();
		$vals = isset($credentials->webservice_credentials_value) ? $credentials->webservice_credentials_value : array();
		$n = count($keys);
		for ($i = 0; $i < $n; $i ++)
		{
			$return[$keys[$i]] = $vals[$i];
		}
		return $return;
	}

	function process_result()
	{
		return $this->msg;
	}

	/**
	 * return the javascript to create an instance of the class defined in formJavascriptClass
* @param   object	parameters
* @param   object	table model
* @param   array	[0] => string table's form id to contain plugin
	 * @return  bool
	 */

	function onLoadJavascriptInstance($params, $model, $args)
	{
		parent::onLoadJavascriptInstance($params, $model, $args);
		$opts = $this->getElementJSOptions($model);
		$opts->requireChecked = false;
		$opts = json_encode($opts);
		$this->jsInstance = "new FbListWebservice($opts)";
		return true;
	}

}
?>