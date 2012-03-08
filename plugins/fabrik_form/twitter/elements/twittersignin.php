<?php
defined('_JEXEC') or die('Restricted access');
?>

<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 * NOTE - as we can only have one addpath file specified for the params group, this file has to be located
 * in the main ./administrator/components/com_fabrik/models/fields folder.  So until we work out how to do the install
 * XML magic to relocate this file on install, we have simply made a copy of it in the admin location in SVN.
 * If you edit the copy in the plugin folder, please be sure to also modify the copy in the admin folder.
 */

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_fabrik'.DS.'helpers'.DS.'element.php');

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a twitter sign in button
 *
 * @subpackage		Parameter
 * @since		1.5
 */

class JFormFieldTwittersignin extends JFormField
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Twittersignin';

	var $_array_counter = null;

	function getInput()
	{
		$iframeid = $this->id.'_iframe';
		$cid = JRequest::getVar('id', array(), 'array');
		// $$$ hugh - when creating a new form, no 'cid' ... not sure what to do, so just set it to 0.  Should
		// prolly just return something like 'available after save' ?
		if (!empty($cid))
		{
			$cid = (int)$cid[0];
		}
		else
		{
			$cid = 0;
		}
		$c = isset($this->form->repeatCounter) ? (int)$this->form->repeatCounter : 0;


		//$href = COM_FABRIK_LIVESITE . 'index.php?option=com_fabrik&controller=plugin&task=pluginAjax&plugin=fabriktwitter&g=form&method=authenticateAdmin&tmpl=component&formid='.$cid.'&repeatCounter='.$c;
		$href = COM_FABRIK_LIVESITE . 'index.php?option=com_fabrik&task=plugin.pluginAjax&plugin=twitter&g=form&method=authenticateAdmin&tmpl=component&formid='.$cid.'&repeatCounter='.$c;

		$clearjs = '$(\'jform_params_twitter_oauth_token-'.$c.'\').value = \'\';';
		$clearjs .= '$(\'jform_params_twitter_oauth_token_secret-'.$c.'\').value = \'\';';
		$clearjs .= '$(\'jform_params_twitter_oauth_user-'.$c.'\').value = \'\';';
		$clearjs .= "return false;";

		$js = "window.open('$href', 'twitterwins', 'width=800,height=460,toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=yes,resizable=yes');return false;";
		$str =  '<a href="#" onclick="'.$js.'"><img src="'.COM_FABRIK_LIVESITE.'components/com_fabrik/libs/abraham-twitteroauth/images/lighter.png" alt="Sign in with Twitter"/></a>';
		$str .= " | <a href=\"#\" onclick=\"$clearjs\">" .    JText::_('PLG_FORM_TWITTER_CLEAR_CREDENTIALS') . "</a><br/>";
		$str .= "<br /><input type=\"text\" readonly=\"readonly\" name=\"". $this->name . "\" id=\"" .$this->id . "\" value=\"" . $this->value . "\" />";
		return $str;
	}
}
?>