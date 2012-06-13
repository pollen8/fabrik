<?php
/**
 * Plugin element to render fields
 * @package fabrikar
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

class plgFabrik_ElementField extends plgFabrik_Element
{

	/**
	 * (non-PHPdoc)
	 * @see plgFabrik_Element::renderListData()
	 */

	public function renderListData($data, &$thisRow)
	{
		$params = $this->getParams();
		$data = $this->numberFormat($data);
		$format = $params->get('text_format_string');
		if ($format  != '')
		{
			$data = sprintf($format, $data);
		}
		if ($params->get('password') == "1")
		{
			$data = str_pad('', JString::strlen($data), '*');
		}
		$this->_guessLinkType($data, $thisRow, 0);
		return parent::renderListData($data, $thisRow);
	}

	/**
	 * determines if the element can contain data used in sending receipts, e.g. field returns true
	 */

	function isReceiptElement()
	{
		return true;
	}

	/**
	 * draws the form element
	 * @param	array	data to preopulate element with
	 * @param	int		repeat group counter
	 * @return	string	returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		$params = $this->getParams();
		$element = $this->getElement();
		$bits = $this->inputProperties($repeatCounter);
		// $$$ rob - not sure why we are setting $data to the form's data
		//but in table view when getting read only filter value from url filter this
		// _form_data was not set to no readonly value was returned
		// added little test to see if the data was actually an array before using it
		if (is_array($this->getFormModel()->_data))
		{
			$data = $this->getFormModel()->_data;
		}
		$value = $this->getValue($data, $repeatCounter);

		// $$$ hugh - if the form just failed validation, number formatted fields will already
		// be formatted, so we need to un-format them before formatting them!
		// $$$ rob - well better actually check if we are coming from a failed validation then :)
		if (JRequest::getCmd('task') == 'form.process') {
			$value = $this->unNumberFormat($value);
		}
		$value = $this->numberFormat($value);
		if (!$this->editable) 
		{
			$this->_guessLinkType($value, $data, $repeatCounter);
			//$value = $this->numberFormat($value);
			$format = $params->get('text_format_string');
			if ($format != '')
			{
				//$value =  eval(sprintf($format,$value));
				//not sure why this was being evald??
				$value = sprintf($format, $value);
			}
			if ($params->get('password') == "1")
			{
				$value = str_pad('', JString::strlen($value), '*');
			}
			return($element->hidden == '1') ? "<!-- " . $value . " -->" : $value;
		}

		//stop "'s from breaking the content out of the field.
		// $$$ rob below now seemed to set text in field from "test's" to "test&#039;s" when failed validation
		//so add false flag to ensure its encoded once only
		// $$$ hugh - the 'double encode' arg was only added in 5.2.3, so this is blowing some sites up
		if (version_compare( phpversion(), '5.2.3', '<'))
		{
			$bits['value'] = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
		}
		else
		{
			$bits['value'] = htmlspecialchars($value, ENT_COMPAT, 'UTF-8', false);
		}
		$bits['class'] .= ' ' . $params->get('text_format');
		return $this->buildInput('input', $bits);
	}

	/**
	 * format guess link type
	 *
	 * @param	string	$value
	 * @param	array	data
	 * @param	int		repeat counter
	 */

	function _guessLinkType(&$value, $data, $repeatCounter = 0)
	{
		$params = $this->getParams();
		$guessed = false;
		if ($params->get('guess_linktype') == '1')
		{
			jimport('joomla.mail.helper');
			$target = $this->guessLinkTarget();
			if (JMailHelper::isEmailAddress($value))
			{
				$value = JHTML::_('email.cloak', $value);
				$guessed = true;
			}
			// Changes JF Questiaux
			else if (JString::stristr($value, 'http'))
			{
				$value = '<a href="' . $value . '"' . $target . '>' . $value . '</a>';
				$guessed = true;
			}
			elseif (JString::stristr($value, 'www.'))
			{
				$value = '<a href="http://' . $value . '"' . $target . '>' . $value . '</a>';
				$guessed = true;
			}
		}
		// $$$ hugh - this gets done in $listModel->_addLink(), called from element parent::renderListData()
		/*
		if (!$guessed)
		{
			$this->addCustomLink($value, $data, $repeatCounter);
		}
		*/
	}

	/**
	 * get the guess type link target property
	 * @return	string
	 */

	protected function guessLinkTarget()
	{
		$params = $this->getParams();
		$target = $params->get('link_target_options', 'default');
		switch ($target)
		{
			default:
				$str = ' target="' . $target . '"';
				break;
			case 'default':
				$str = '';
				break;
		 	case 'lightbox':
		 		FabrikHelperHTML::slimbox();
				$str = ' rel="lightbox[]"';
				break;
		}
		return $str;
	}

	/**
	 * return the javascript to create an instance of the class defined in formJavascriptClass
	 * @return	string	javascript to create instance. Instance name must be 'el'
	 */

	function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts = json_encode($opts);
		return "new FbField('$id', $opts)";
	}

	/**
	 * defines the type of database table field that is created to store the element's data
	 */

	function getFieldDescription()
	{
		$p = $this->getParams();
		if ($this->encryptMe())
		{
			return 'BLOB';
		}
		$group = $this->getGroup();
		if ($group->isJoin() == 0 && $group->canRepeat())
		{
			return "TEXT";
		}
		switch ($p->get('text_format'))
		{
			case 'text':
			default:
				//$objtype = "VARCHAR(255)";
				$objtype = "VARCHAR(" . $p->get('maxlength', 255) . ")";
				break;
			case 'integer':
				$objtype = "INT(" . $p->get('integer_length', 10) . ")";
				break;
			case 'decimal':
				$total = (int) $p->get('integer_length', 10) + (int) $p->get('decimal_length', 2);
				$objtype = "DECIMAL(" . $total . "," . $p->get('decimal_length', 2) . ")";
				break;
		}
		return $objtype;
	}

	/**
	 * @return	array	key=>value options
	 */

	function getJoomfishOptions()
	{
		$params = $this->getParams();
		$return  = array();
		$size = (int)$this->getElement()->width;
		if ($size !== 0)
		{
			$return['length'] = $size;
		}
		$maxlength  = (int) $params->get('maxlength');
		if ($maxlength === 0)
		{
			$maxlength = $size;
		}
		if ($params->get('textarea-showmax') && $maxlength !== 0)
		{
			$return['maxlength'] = $maxlength;
		}
		return $return;
	}

	/**
	 * can the element's data be encrypted
	 */

	public function canEncrypt()
	{
		return true;
	}

	/**
	 *  can be overwritten in add on classes
	 * @param	mixed	thie elements posted form data
	 * @param	array	posted form data
	 */

	function storeDatabaseFormat($val, $data)
	{
		if (is_array($val))
		{
			foreach ($val as $k => $v)
			{
				$val[$k] = $this->_indStoreDatabaseFormat($v);
			}
			$val = implode(GROUPSPLITTER, $val);
		}
		else
		{
			$val = $this->_indStoreDatabaseFormat($val);
		}
		return $val;
	}

	function _indStoreDatabaseFormat($val)
	{
		return $this->unNumberFormat($val);
	}

	/**
	* @since 3.0.4
	* get the element's cell class
	* @return	string	css classes
	*/

	public function getCellClass()
	{
		$params = $this->getParams();
		$classes = parent::getCellClass();
		$format = $params->get('text_format');
		if ($format == 'decimal' || $format == 'integer')
		{
			$classes .= ' ' . $format;
		}
		 return $classes;
	}
}
?>