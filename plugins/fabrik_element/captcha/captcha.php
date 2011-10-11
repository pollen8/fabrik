<?php
/**
 * Plugin element to captcha
 * @package fabrikar
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once(JPATH_SITE.DS.'plugins'.DS.'fabrik_element'.DS.'captcha'.DS.'recaptcha1.10'.DS.'recaptchalib.php');

class plgFabrik_ElementCaptcha extends plgFabrik_Element
{

	var $_font = 'monofont.ttf';

	/**
	 * can be overwritten in plugin class
	 * determines if the element can contain data used in sending receipts, e.g. field returns true
	 */

	function isReceiptElement()
	{
		return true;
	}

	protected function _generateCode($characters)
	{
		/* list all possible characters, similar looking characters and vowels have been removed */
		$possible = '23456789bcdfghjkmnpqrstvwxyz';
		$code = '';
		$i = 0;
		while ($i < $characters) {
			$code .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
			$i++;
		}
		return $code;
	}

	function getLabel($repeatCounter, $tmpl = '')
	{
		$user = JFactory::getUser();
		$params = $this->getParams();
		if ($user->id != 0) {
			if ($params->get('captcha-showloggedin', 0) == 0) {
				return '';
			}
		}
		return parent::getLabel($repeatCounter, $tmpl);
	}

	function isHidden()
	{
		$user = JFactory::getUser();
		$params = $this->getParams();
		if ($user->id != 0) {
			if ($params->get('captcha-showloggedin', 0) == 0) {
				return true;
			}
		}
		return parent::isHidden();
	}

	/**
	 * check user can view the read only element & view in table view
	 * If user logged in return false
	 * @return bol can view or not
	 */

	function canView()
	{
		$user = JFactory::getUser();
		$params = $this->getParams();
		if ($user->id != 0) {
			if ($params->get('captcha-showloggedin', 0) == 0) {
				return false;
			}
		}
		return parent::canView();
	}

	/**
	 * check user can view the active element
	 * If user logged in return false
	 * @return bol can view or not
	 */

	function canUse()
	{
		$user = JFactory::getUser();
		$params = $this->getParams();
		if ($user->id != 0) {
			if ($params->get('captcha-showloggedin', 0) == 0) {
				return false;
			}
		}
		return parent::canUse();
	}

	/**
	 * draws the form element
	 * @param int repeat group counter
	 * @return string returns element html
	 */

	function render($data, $repeatCounter = 0) {
		$session = JFactory::getSession();
		$name = $this->getHTMLName($repeatCounter);
		$id	= $this->getHTMLId($repeatCounter);
		$element = $this->getElement();
		$params = $this->getParams();
		$user = JFactory::getUser();

		if ($params->get('captcha-method') == 'recaptcha') {

			$publickey = $params->get('recaptcha_publickey');

			//$$$tom added lang & theme options
			$theme = $params->get('recaptcha_theme', 'red');
			$lang = strtolower($params->get('recaptcha_lang', 'en'));
			$error = null;
			if ($user->id != 0 && $params->get('captcha-showloggedin', 0) == false) {
				return '<input class="inputbox text" type="hidden" name="'.$name.'" id="'.$id.'" value="" />';
			} else {
				return recaptcha_get_html($id, $publickey, $theme, $lang, $error);
			}
		} else {
			$str = array();
			$size = $element->width;
			$height = $params->get('captcha-height', 40);
			$width = $params->get('captcha-width', 40);
			$characters = $params->get('captcha-chars', 6);
			$code = $this->_generateCode($characters);

			// $$$ hugh - code that generates image now in image.php
			$session->set('com_fabrik.element.captach.security_code', $code);
			// $$$ hugh - changed from static image path to using simple image.php script, to get round IE caching images
			$str[] = '<img src="'.COM_FABRIK_LIVESITE.'plugins/fabrik_element/captcha/image.php?width='.$width.'&amp;height='.$height.'&amp;font='.$this->_font.'&amp;foo='.rand().'" alt="'.JText::_('security image').'" />';
			$str[] = '<br />';

			$value = $this->getValue($data, $repeatCounter);
			$type = ($params->get('password') == "1") ? "password" : "text";
			if (isset($this->_elementError) && $this->_elementError != '') {
				$type .= " elementErrorHighlight";
			}
			if ($element->hidden == '1') {
				$type = "hidden";
			}
			$sizeInfo = ' size="'.$size.'"';
			if (!$this->_editable) {
				if ($element->hidden == '1') {
					return '<!-- '.stripslashes($value).' -->';
				} else {
					return stripslashes($value);
				}
			}
			$str[] = '<input class="inputbox '.$type.'" type="'.$type.'" name="'.$name.'" id="'.$id.'" '.$sizeInfo.' value="" />';
			return implode("\n", $str);
		}
	}

	/**
	 * can be overwritten in adddon class
	 *
	 * checks the posted form data against elements INTERNAL validataion rule - e.g. file upload size / type
	 * @param string elements data
	 * @param int repeat group counter
	 * @return bol true if passes / false if falise validation
	 */

	function validate($data, $repeatCounter = 0)
	{
		$params = $this->getParams();
		if (!$this->canUse()) {
			return true;
		}
		if ($params->get('captcha-method') == 'recaptcha') {
			$privatekey = $params->get('recaptcha_privatekey');
			if (JRequest::getVar('recaptcha_response_field')) {
				$resp = recaptcha_check_answer ($privatekey,
				$_SERVER["REMOTE_ADDR"],
				JRequest::getVar('recaptcha_challenge_field'),
				JRequest::getVar('recaptcha_response_field'));
				return ($resp->is_valid) ? true : false;
			}

			return false;
		} else {

			$this->getParams();
			$elName = $this->getFullName( true, true, false);
			$session = JFactory::getSession();
			if ($session->get('com_fabrik.element.captach.security_code', null) != $data) {
				return false;
			}
			return true;
		}
	}

	/**
	 * @return string error message raised from failed validation
	 */

	function getValidationErr()
	{
		return JText::_('PLG_ELEMENT_CAPTCHA_FAILED');
	}

	function mustValidate()
	{
		$params = $this->getParams();
		if (!$this->canUse() && !$this->canView()) {
			return false;
		}
		return parent::mustValidate();
	}

	/**
	 * return tehe javascript to create an instance of the class defined in formJavascriptClass
	 * @param object element
	 * @return string javascript to create instance. Instance name must be 'el'
	 */

	function elementJavascript($repeatCounter)
	{
		$user = JFactory::getUser();
		if ($user->id == 0) {
			$id = $this->getHTMLId($repeatCounter);
			$opts = $this->getElementJSOptions($repeatCounter);
			$opts = json_encode($opts);
			return "new FbCaptcha('$id', $opts)";
		}
	}

	/**
	 * used to format the data when shown in the form's email
	 * @param mixed element's data
	 * @param array form records data
	 * @param int repeat group counter
	 * @return string formatted value
	 */

	function getEmailValue($value, $data, $c )
	{
		return "";
	}
}
?>
