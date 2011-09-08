<?php
/**
 * Plugin element to kaltura video
 * @package fabrikar
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models'.DS.'element.php');
jimport('kaltura.kaltura_client');

class plgFabrik_ElementKaltura extends plgFabrik_Element {

	protected $fieldDesc = 'TEXT';

	/**
	 * shows the data formatted for the table view
	 * @param string data
	 * @param object all the data in the tables current row
	 * @return string formatted value
	 */

	function renderListData($data, $oAllRowsData)
	{
		$id = $this->getHTMLId();
		$id .= "_" . $oAllRowsData->__pk_val;
		FabrikHelperHTML::script('media/com_fabrik/js/swfobject.js', false);
		$params = $this->getParams();
		$partnerid = $params->get('kaltura_partnerid');
		?>

<script type="text/javascript">
	var params = {
		allowscriptaccess: "always",
		allownetworking: "all",
		allowfullscreen: "true",
		wmode: "opaque"
	};

	var flashVars = {
		entryId: "<?php echo $data?>"
	};

	swfobject.embedSWF("http://www.kaltura.com/kwidget/wid/_<?php echo $partnerid?>", "<?php echo $id?>", "400", "360", "9.0.0", false, flashVars, params);
</script>
		<?php
return '<div id="'.$id.'"></div>';
		//return parent::renderListData($data, $oAllRowsData);
	}


	/**
	 * draws the form element
	 * @param int repeat group counter
	 * @return string returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		$name 			= $this->getHTMLName($repeatCounter);
		$id 				= $this->getHTMLId($repeatCounter);
		$return = "<input id=\"$id\" type=\"hidden\" value=\"$data\" name=\"$name\" />";
		$return .= '<div id="kcw"></div>';
		return $return;
	}

	private function getKalturaFlashVars()
	{
		if (!isset($this->kalturaFlashVars)) {
			$params = $this->getParams();
			$partnerid = $params->get('kaltura_partnerid');
			$subpartnerid = $params->get('kaltura_sub_partnerid');
			$user = $this->getKalturaUser();
			$ksession = $this->getKalturaSession();

			$flashVars = array();
			$flashVars["partnerId"] 	= $partnerid;
			$flashVars["subpId"] 		= $subpartnerid;
			$flashVars["uid"] 		= $user->userId;
			$flashVars["ks"] 		= JArrayHelper::getValue($ksession["result"], "ks");
			$flashVars["kshowId"] 		= -2;
			$flashVars["afterAddEntry"]     = "onContributionWizardAfterAddEntry";
			$flashVars["showCloseButton"]   = $params->get('kaltura_show_closebutton') ? true : false;
			$this->kalturaFlashVars = $flashVars;
		}
		return $this->kalturaFlashVars;
	}

	private function getKalturaSession()
	{
		if (!isset($this->kalturaSession)) {
			$params = $this->getParams();
			$secret = $params->get('kaltura_webservice_secret');
			$client = $this->getKalturaClient();
			$user = $this->getKalturaUser();
			$this->kalturaSession = $client->startSession($user, $secret, false);
		}
		return $this->kalturaSession;
	}

	/**
	 * get the kaltura client
	 * @return unknown_type
	 */
	private function getKalturaConfig()
	{
		if (!isset($this->kalturaConfig)) {
			$params = $this->getParams();
			$partnerid = $params->get('kaltura_partnerid');
			$subpartnerid = $params->get('kaltura_sub_partnerid');
			$secret = $params->get('kaltura_webservice_secret');
			$this->kalturaConfig = new KalturaConfiguration($partnerid, $subpartnerid);
		}
		return $this->kalturaConfig;
	}

	/**
	 * get the curent kaltura user
	 * @return unknown_type
	 */
	private function getKalturaUser()
	{
		if (!isset($this->kalturaUser)) {
			$this->kalturaUser = new KalturaSessionUser(2);
		}
		return $this->kalturaUser;
	}

	/**
	 * get the kaltura client
	 * @return unknown_type
	 */

	private function getKalturaClient()
	{
		if (!isset($this->kalturaClient)) {
			$conf = $this->getKalturaConfig();
			$this->kalturaClient = new KalturaClient($conf);
		}
		return $this->kalturaClient;
	}

	/**
	 * return the javascript to create an instance of the class defined in formJavascriptClass
	 * @return string javascript to create instance.
	 */

	function elementJavascript($repeatCounter)
	{
		$app = JFactory::getApplication();
		$id = $this->getHTMLId($repeatCounter);
		FabrikHelperHTML::addScriptDeclaration('
		function onContributionWizardAfterAddEntry(entries) {
	alert("Added " + entries.length + " entries");
	var res = [];
	for(var i = 0; i < entries.length; i++) {

		alert("entries["+i+"] = " + entries[i].entryId);
		res.push(entries[i].entryId);
	}
	$("'.$id.'").value = JSON.encode(res);
}');

		$params = $this->getParams();
		$element = $this->getElement();
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts->id = $element->id;

		$uploader = new stdClass();
		$uploader->allowScriptAccess = "always";
		$uploader->allowNetworking = "all";
		$uploader->wmode = "opaque";
		$opts->uploader = $uploader;
		$opts->flash = $this->getKalturaFlashVars();
		$opts = json_encode($opts);
		return "new FbKaltura('$id', $opts)";
	}

	/**
	 * load the javascript class that manages interaction with the form element
	 * should only be called once
	 * @return string javascript class file
	 */

	function formJavascriptClass(&$srcs)
	{
		FabrikHelperHTML::script('media/com_fabrik/js/swfobject.js', false);
		parent::formJavascriptClass($srcs);
	}

}
?>