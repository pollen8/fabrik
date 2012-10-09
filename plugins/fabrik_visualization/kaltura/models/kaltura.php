<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.kaltura
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once(JPATH_SITE . '/components/com_fabrik/models/visualization.php');

jimport('kaltura.kaltura_client_base');
jimport('kaltura.kaltura_client');

/**
 * Fabrik Kaltura Plug-in Model
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.visualization.kaltura
 */

class fabrikModelKaltura extends FabrikFEModelVisualization
{

	var $kalturaConfig = null;

	function getKalturaConfig()
	{
		if (!isset($this->kalturaConfig))
		{
			$params = $this->getParams();
			$partner_id = $params->get('kaltura_partnerid');
			$subp_id = $params->get('kaltura_sub_partnerid');
			$this->kalturaConfig = new KalturaConfiguration($partner_id, $subp_id);
			//$this->kalturaConfig->partnerId = $partner_id;
			//$this->kalturaConfig->subPartnerId = $subp_id;
			//$this->kalturaConfig->secret = $params->get('kaltura_webservice_secret');
			$this->kalturaConfig->adminSecret = $params->get('kaltura_admin_secret');
			$this->kalturaConfig->serviceUrl = "http://www.kaltura.com";
			//$this->kalturaConfig->setLogger(new KalturaDemoLogger());
		}
		return $this->kalturaConfig;
	}

	function getData()
	{
		$params = $this->getParams();
		$conf = $this->getKalturaConfig();

		$user = new KalturaSessionUser();
		$user->userId = "1";

		$cl = new KalturaClient($conf);

		$res = $cl->startSession($user, $params->get('kaltura_admin_secret'), true);
		//$res =$cl->startAdmin($user, $conf->adminSecret , null);
		//$ks = $cl->getKs();
		$ks = $res['result']['ks'];
		echo $ks;
		// create a filter to define what exactly we want to be in the list

		$filter = &$this->getKalturaFilter();
		$page = $this->getKalturaPage();
		$page_size = 20; // choose the page_size to be some number that will fit the area you would like to display the thumbnails gallery

		$detailed = false;
		$res = $cl->listentries($user, $filter, $detailed, $page_size, $page);
		$count = @$res["result"]["count"];
		$entries = @$res["result"]["entries"];
		if (!$entries)
			$entries = array();
		return $entries;
	}

	private function getKalturaPage()
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$page = $input->getInt('page'); // read the current page from the request.
		// page=1 is the first page
		if ($page < 1)
		{
			$page = 1;
		}
		return $page;
	}

	private function getKalturaFilter()
	{
		$filter = new KalturaEntryFilter();

		$filter->inMediaType = "1,2,5,6"; // allow clips of mediaType 1=video, 2=images, 5=audio, 6=roughcuts. Separate the choice with ',' and no spaces

		// order the results by the creation data descending
		$filter->orderBy = KalturaEntryFilter::ORDER_BY_CREATED_AT_DESC;
		// or ascending :
		// $filter->orderBy = KalturaEntryFilter::ORDER_BY_CREATED_AT_ASC;
		return $filter;
	}

}
