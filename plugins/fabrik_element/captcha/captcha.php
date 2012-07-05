<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

require_once JPATH_SITE . '/plugins/fabrik_element/captcha/recaptcha1.10/recaptchalib.php';

/**
 * Captcha element plugin
 * 
 * @package  Fabrik
 * @since    3.0
 */

class PlgFabrik_ElementCaptcha extends PlgFabrik_Element
{

	protected $font = 'monofont.ttf';

	/**
	 * Determines if the element can contain data used in sending receipts,
	 * e.g. fabrikfield returns true
	 * 
	 * @return  bool
	 */

	public function isReceiptElement()
	{
		return true;
	}

	/**
	 * Generate captcha text
	 * 
	 * @param   int  $characters  number of characters to generate
	 * 
	 * @return  string captcha text
	 */

	protected function _generateCode($characters)
	{
		/* list all possible characters, similar looking characters and vowels have been removed */
		$possible = '23456789bcdfghjkmnpqrstvwxyz';
		$code = '';
		$i = 0;
		while ($i < $characters)
		{
			$code .= JString::substr($possible, mt_rand(0, JString::strlen($possible) - 1), 1);
			$i++;
		}
		return $code;
	}

	/**
	 * Get the element's HTML label
	 *  
	 * @param   int     $repeatCounter  group repeat counter
	 * @param   string  $tmpl           form template
	 * 
	 * @return  string  label
	 */

	public function getLabel($repeatCounter, $tmpl = '')
	{
		$user = JFactory::getUser();
		$params = $this->getParams();
		if ($user->id != 0)
		{
			if ($params->get('captcha-showloggedin', 0) == 0)
			{
				return '';
			}
		}
		return parent::getLabel($repeatCounter, $tmpl);
	}

	/**
	 * Is the element hidden or not - if not set then return false
	 *
	 * @return  bool
	 */

	protected function isHidden()
	{
		$user = JFactory::getUser();
		$params = $this->getParams();
		if ($user->id != 0)
		{
			if ($params->get('captcha-showloggedin', 0) == 0)
			{
				return true;
			}
		}
		return parent::isHidden();
	}

	/**
	 * Check user can view the read only element & view in list view
	 * $$$ rob 14/03/2012 always returns false now - cant see a need to show it in the details / list view
	 * 
	 * @return  bool  can view or not
	 */

	public function canView()
	{
		return false;
	}

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
		$user = JFactory::getUser();
		$params = $this->getParams();
		if ($user->id != 0)
		{
			if ($params->get('captcha-showloggedin', 0) == 0)
			{
				return false;
			}
		}
		return parent::canUse();
	}

	/**
	 * Draws the html form element
	 * 
	 * @param   array  $data           to preopulate element with
	 * @param   int    $repeatCounter  repeat group counter
	 * 
	 * @return  string	elements html
	 */

	public function render($data, $repeatCounter = 0)
	{
		$session = JFactory::getSession();
		$name = $this->getHTMLName($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$element = $this->getElement();
		$params = $this->getParams();
		$user = JFactory::getUser();
		$value = $this->getValue($data, $repeatCounter);
		if (!$this->editable)
		{
			if ($element->hidden == '1')
			{
				return '<!-- ' . stripslashes($value) . ' -->';
			}
			else
			{
				return stripslashes($value);
			}
		}
		if ($params->get('captcha-method') == 'recaptcha')
		{
			$publickey = $params->get('recaptcha_publickey');

			// $$$tom added lang & theme options
			$theme = $params->get('recaptcha_theme', 'red');
			$lang = JString::strtolower($params->get('recaptcha_lang', 'en'));
			$error = null;
			if ($user->id != 0 && $params->get('captcha-showloggedin', 0) == false)
			{
				return '<input class="inputbox text" type="hidden" name="' . $name . '" id="' . $id . '" value="" />';
			}
			else
			{
				return recaptcha_get_html($id, $publickey, $theme, $lang, $error);
			}
		}
		else
		{
			$str = array();
			$size = $element->width;
			$height = $params->get('captcha-height', 40);
			$width = $params->get('captcha-width', 40);
			$characters = $params->get('captcha-chars', 6);
			$code = $this->_generateCode($characters);

			// $$$ hugh - code that generates image now in image.php
			$session->set('com_fabrik.element.captach.security_code', $code);

			// ***** e-kinst

			// Additional plugin params with validation
			$noise_color = $params->get('captcha-noise-color', '0000FF');

			// '0000FF' again if we have param value but it's invalid
			$noise_color = $this->_getRGBcolor($noise_color, '0000FF');
			$text_color = $params->get('captcha-text-color', '0000FF');
			$text_color = $this->_getRGBcolor($text_color, '0000FF');
			$bg_color = $params->get('captcha-bg', 'FFFFFF');
			$bg_color = $this->_getRGBcolor($bg_color, 'FFFFFF');

			// Let's keep all params in relatively safe place not only captcha value
			$session->set('com_fabrik.element.captach.height', $height);
			$session->set('com_fabrik.element.captach.width', $width);
			$session->set('com_fabrik.element.captach.noise_color', $noise_color);
			$session->set('com_fabrik.element.captach.text_color', $text_color);
			$session->set('com_fabrik.element.captach.bg_color', $bg_color);
			$session->set('com_fabrik.element.captach.font', $this->font);

			// $$$ hugh - changed from static image path to using simple image.php script, to get round IE caching images

			/* e-kinst
			 *	It seems too dangerous to set all parameters here,
			 *	because everybody can enlarge image size and set noise color to
			 *	background color to OCR captcha values without problems
			$str[] = '<img src="' . COM_FABRIK_LIVESITE . 'plugins/fabrik_element/captcha/image.php?foo=' . rand() . '" alt="'
			    . JText::_('security image') . '" />';
			 */
			$str[] = '<br />';

			$type = ($params->get('password') == "1") ? "password" : "text";
			if ($this->elementError != '')
			{
				$type .= ' elementErrorHighlight';
			}
			if ($element->hidden == '1')
			{
				$type = 'hidden';
			}
			$sizeInfo = ' size="' . $size . '"';
			$str[] = '<input class="inputbox ' . $type . '" type="' . $type . '" name="' . $name . '" id="' . $id . '" ' . $sizeInfo . ' value="" />';
			return implode("\n", $str);
		}
	}

	/**
	 * Internal element validation
	 * 
	 * @param   array  $data           form data
	 * @param   int    $repeatCounter  repeeat group counter
	 * 
	 * @return bool
	 */

	public function validate($data, $repeatCounter = 0)
	{
		$params = $this->getParams();
		if (!$this->canUse())
		{
			return true;
		}
		if ($params->get('captcha-method') == 'recaptcha')
		{
			$privatekey = $params->get('recaptcha_privatekey');
			if (JRequest::getVar('recaptcha_response_field'))
			{
				$challenge = JRequest::getVar('recaptcha_challenge_field');
				$response = JRequest::getVar('recaptcha_response_field');
				$resp = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $challenge, $response);
				return ($resp->is_valid) ? true : false;
			}

			return false;
		}
		else
		{
			$this->getParams();
			$elName = $this->getFullName(true, true, false);
			$session = JFactory::getSession();
			if ($session->get('com_fabrik.element.captach.security_code', null) != $data)
			{
				return false;
			}
			return true;
		}
	}

	/**
	 * Get validation error - run through JText
	 * 
	 * @return  string
	 */

	public function getValidationErr()
	{
		return JText::_('PLG_ELEMENT_CAPTCHA_FAILED');
	}

	/**
	 * Determine if the element should run its validation plugins on form submission
	 * 
	 * @return  bool	default true
	 */

	public function mustValidate()
	{
		$params = $this->getParams();
		if (!$this->canUse() && !$this->canView())
		{
			return false;
		}
		return parent::mustValidate();
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 * 
	 * @param   int  $repeatCounter  repeat group counter
	 * 
	 * @return  string
	 */

	public function elementJavascript($repeatCounter)
	{
		$user = JFactory::getUser();
		if ($user->id == 0)
		{
			$id = $this->getHTMLId($repeatCounter);
			$opts = $this->getElementJSOptions($repeatCounter);
			$opts = json_encode($opts);
			return "new FbCaptcha('$id', $opts)";
		}
	}

	/**
	 * Used to format the data when shown in the form's email
	 * 
	 * @param   mixed  $value          element's data
	 * @param   array  $data           form records data
	 * @param   int    $repeatCounter  repeat group counter
	 * 
	 * @return  string	formatted value
	 */

	public function getEmailValue($value, $data, $repeatCounter)
	{
		return "";
	}

	/**
	 * $$$ e-kinst Convert a hext colour to RGB
	 * 
	 * @param   string  $hexColor  3- or 6-digits hex color with optional leading '#'
	 * @param   string  $default   default hex color if first param invalid
	 * 
	 * @return  string 	as 'R+G+B' where R,G,B are decimal
	 */

	private function _getRGBcolor($hexColor, $default = 'FF0000')
	{
		$regex = '/^#?(([\da-f])([\da-f])([\da-f])|([\da-f]{2})([\da-f]{2})([\da-f]{2}))$/i';
		$rgb = array();
		if (!preg_match($regex, $hexColor, $rgb))
		{
			if (!preg_match($regex, $default, $rgb))
			{
				// In case where $default invalid also (call error)
				$rgb = array('FF0000', 'FF0000', 'FF', '00', '00');
			}
		}
		array_shift($rgb);
		array_shift($rgb);
		if (count($rgb) > 3)
		{
			$rgb = array_slice($rgb, 3, 3);
		}
		for ($i = 0; $i < 3; $i++)
		{
			if (JString::strlen($rgb[$i]) == 1)
			{
				$rgb[$i] .= $rgb[$i];
			}
			$rgb[$i] = intval($rgb[$i], 16);
		}
		return implode('+', $rgb);
	}
}
