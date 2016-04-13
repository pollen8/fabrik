<?php
/**
 * String helpers
 *
 * @package     Joomla
 * @subpackage  Fabrik.helpers
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

namespace Fabrik\Helpers;

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 *
 * $$$ hugh JText::_() does funky stuff to strings with commas in them, like
 * truncating everything after the first comma, if what follows the first comma
 * is all "upper case".  But it tests for that using non MB safe code, so any non
 * ASCII strings (like Greek text) with a comma in them get truncated at the comma.
 * Corner case or what!  But we need to work round this behavior.
 *
 * So ... here's a wrapper for JText::_().
 */
class Text extends \JText
{
	/**
	 * Translates a string into the current language.
	 *
	 * Examples:
	 * <script>alert(Joomla.JText._('<?php echo Text::_("JDEFAULT", array("script"=>true));?>'));</script>
	 * will generate an alert message containing 'Default'
	 * <?php echo Text::_("JDEFAULT");?> it will generate a 'Default' string
	 *
	 * @param   string   $string                The string to translate.
	 * @param   mixed    $jsSafe                Boolean: Make the result javascript safe.
	 * @param   boolean  $interpretBackSlashes  To interpret backslashes (\\=\, \n=carriage return, \t=tabulation)
	 * @param   boolean  $script                To indicate that the string will be push in the javascript language store
	 *
	 * @return  string  The translated string or the key is $script is true
	 *
	 * @since   11.1
	 */
	public static function _($string, $jsSafe = false, $interpretBackSlashes = true, $script = false)
	{
		/**
		 * In JText::_(), it does the following tests to see if everything following a comma is all upp
		 * case, and if it is, it does Funky Stuff to it.  We ned to avoid that behavior.  So us this
		 * logic, and if it's true, return the string untouched.  We could just check for a comma and not
		 * process anything with commas (unlikely to be a translatable phrase), but unless this test adds
		 * too much overhead, might as well do the whole J! test sequence.
		 */

		if (!(strpos($string, ',') === false))
		{
			$test = substr($string, strpos($string, ','));

			if (strtoupper($test) === $test)
			{
				/**
				 * This is where JText::_() would do Funky Stuff, chopping off everything after
				 * the first comma.  So we'll just return the input string untouched.
				 */
				return $string;
			}
		}

		// if we got this far, hand it to JText::_() as normal
		return parent::_($string, $jsSafe, $interpretBackSlashes, $script);
	}
}
