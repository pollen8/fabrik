<?php
/**
 * Plugin element to render text area
 * @package fabrikar
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class plgFabrik_ElementTextarea extends plgFabrik_Element
{

	protected $fieldDesc = 'TEXT';

	/**
	 * tagify a string
	 * @param	string	to tagify
	 * @return	string	tagified string
	 */

	protected function tagify($data)
	{
		$name = $this->getFullName(false, true, false);
		$params = $this->getParams();
		$listModel = $this->getlistModel();
		$filters = $listModel->getFilterArray();
		$fkeys = JArrayHelper::getValue($filters, 'key', array());
		$data = explode(",", strip_tags($data));
		$tags = array();
		$url = $params->get('textarea_tagifyurl');
		if ($url == '')
		{
			$url = $_SERVER['REQUEST_URI'];
			$bits = explode('?', $url);
			$bits = JArrayHelper::getValue($bits, 1, '', 'string');
			$bits = explode("&", $bits);
			foreach ($bits as $bit)
			{
				$parts = explode("=", $bit);
				if (count($parts) > 1)
				{
					$key = FabrikString::ltrimword(FabrikString::safeColNameToArrayKey($parts[0]), '&');
					if ($key == $this->getFullName(false, true, false))
					{
						$url = str_replace($key.'='.$parts[1], '', $url);
					}
				}
			}
		}
		// $$$ rbo 24/02/2011 remove duplicates from tags
		$data = array_unique($data);
		$icon = FabrikHelperHTML::image('tag.png', 'form', @$this->tmpl, array('alt' => 'tag'));
		foreach ($data as $d)
		{
			$d = trim($d);
			if ($d != '')
			{
				if (trim($params->get('textarea_tagifyurl')) == '')
				{
					$qs = strstr($url, '?');
					if (substr($url, -1) === '?')
					{
						$thisurl = "$url$name=$d";
					}
					else
					{
						$thisurl = strstr($url, '?') ? "$url&$name=".urlencode($d) : "$url?$name=".urlencode($d);
					}
				}
				else
				{
					$thisurl = str_replace('{tag}', urlencode($d), $url);
				}
				$tags[] = '<a href="' . $thisurl . '" class="fabrikTag">' . $icon . $d . '</a>';
			}
		}
		return implode(' ', $tags);
	}

	/**
	 * (non-PHPdoc)
	 * @see plgFabrik_Element::renderListData()
	 */

	public function renderListData($data, &$thisRow)
	{
		$data = parent::renderListData($data, $thisRow);
		$params = $this->getParams();

		if ($params->get('textarea-tagify') == true)
		{
			$data = $this->tagify( $data);
		}
		//$$$rob dont strip slashes here - this is done when saving to db now

		if ($params->get('use_wysiwyg', 0) == 0)
		{
			if (is_array($data))
			{
				for ($i = 0; $i<count($data); $i++)
				{
					$data[$i] = nl2br($data[$i]);
				}
			}
			else
			{
				if (is_object($data))
				{
					$this->convertDataToString($data);
				}
				$data = nl2br($data);
			}
		}
		if (!$params->get('textarea-tagify') && $data !== '' && (int) $params->get('textarea-truncate', 0) !== 0)
		{
			$opts = array();
			$opts['wordcount'] = (int) $params->get('textarea-truncate', 0);
			$opts['tip'] = $params->get('textarea-hover');
			$opts['position'] = $params->get('textarea_hover_location', 'top');
			$data = fabrikString::truncate($data, $opts);
		}
		return $data;
	}

	/**
	 * state if the element uses a wysiwyg editor
	 * @return	bool	use editor
	 */

	function useEditor()
	{
		$params = $this->getParams();
		$element = $this->getElement();
		if ($params->get('use_wysiwyg', 0) && JRequest::getInt('ajax') !== 1)
		{
			return preg_replace("/[^A-Za-z0-9]/", "_", $element->name);
		}
		else
		{
			return false;
		}
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
	 * @param	array	data
	 * @param	int		repeat group counter
	 * @return string returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		$name = $this->getHTMLName($repeatCounter);
		$id = $this->getHTMLId($repeatCounter);
		$element = $this->getElement();
		if ($element->hidden == '1')
		{
			return $this->getHiddenField($name, $data[$name], $id);
		}
		$params = $this->getParams();
		$cols = $element->width;
		$rows = $element->height;
		$value = $this->getValue($data, $repeatCounter);
		$bits = array();
		if (!$this->editable)
		{
			if ($params->get('use_wysiwyg', 0) == 0)
			{
				$value = nl2br($value);
			}
			if ($params->get('textarea-tagify') == true)
			{
				$value = $this->tagify($value);
			}
			return $value;
		}
		if ($params->get('textarea_placeholder', '') !== '')
		{
			$bits['placeholder'] = $params->get('textarea_placeholder');
		}
		$bits['class'] = "fabrikinput inputbox";
		if ($this->elementError != '')
		{
			$bits['class'] .= ' elementErrorHighlight';
		}
		if ($params->get('use_wysiwyg'))
		{
			if (JRequest::getVar('ajax') == 1)
			{
				//$bits['class'] .= " mce_editable";
				$str = "<textarea ";
				foreach ($bits as $key => $val)
				{
					$str .= $key . '="' . $val . '" ';
				}
				$str .= 'name="' . $name . '" id="' . $id. '" cols="' . $cols . '" rows="' . $rows . '">' . $value . '</textarea>';
			}
			else
			{
				$editor = JFactory::getEditor();
				$str = $editor->display($name, $value, $rows, $rows, $cols, $rows, true, $id);
			}
		}
		else
		{
			if ($params->get('disable'))
			{
				$bits['class'] .= " disabled";
				$bits['disabled'] = 'disabled';
			}
			if ($params->get('textarea-showmax'))
			{
				$bits['maxlength'] = $params->get('textarea-maxlength');
			}

			$str = "<textarea ";
			foreach ($bits as $key => $val)
			{
				$str .= $key . '="' . $val . '" ';
			}
			$str .= "name=\"$name\" id=\"". $id. "\" cols=\"$cols\" rows=\"$rows\">".$value."</textarea>\n";
		}
		if ($params->get('textarea-showmax'))
		{
			$charsLeft = $params->get('textarea-maxlength') - strlen($value);
			$str .= "<div class=\"fabrik_characters_left\"><span>" . $charsLeft . "</span> " . JText::_('PLG_ELEMENT_TEXTAREA_CHARACTERS_LEFT') . "</div>";
		}
		return $str;
	}
	
	/**
	 * used to format the data when shown in the form's email
	 * @param	mixed	element's data
	 * @param	array	form records data
	 * @param	int		repeat group counter
	 * @return	string	formatted value
	 */

	function getEmailValue($value, $data, $c)
	{
		$groupModel = $this->getGroup();
		if ($groupModel->isJoin() && $groupModel->canRepeat())
		{
			$value = $value[$c];
		}
		return $this->renderListData($value, new stdClass());
	}

	/**
	 * return the javascript to create an instance of the class defined in formJavascriptClass
	 * @param	int		repeat group counter
	 * @return	string	javascript to create instance. Instance name must be 'el'
	 */

	function elementJavascript($repeatCounter)
	{
		$params = $this->getParams();
		if ($params->get('use_wysiwyg'))
		{
			// $$$ rob need to use the NAME as the ID when wysiwyg end in joined group
			$id	= $this->getHTMLName($repeatCounter);
			if ($this->inDetailedView)
			{
				$id .= '_ro';
			}
		}
		else
		{
			$id = $this->getHTMLId($repeatCounter);
		}
		$opts = $this->getElementJSOptions($repeatCounter);
		$opts->max = $params->get('textarea-maxlength');
		$opts->wysiwyg = ($params->get('use_wysiwyg') && JRequest::getInt('ajax') != 1) ? true: false;
		$opts->deleteOverflow = $params->get('delete_overflow', true) ? true : false;
		$opts = json_encode($opts);
		return "new FbTextarea('$id', $opts)";
	}

	/**
	 * can be overwritten in adddon class
	 *
	 * checks the posted form data against elements INTERNAL validataion rule - e.g. file upload size / type
	 * @param	string	elements data
	 * @param	int		repeat group counter
	 * @return	bool	true if passes / false if falise validation
	 */

	function validate($data, $repeatCounter = 0)
	{
		$params = $this->getParams();
		if (!$params->get('textarea-showmax', false))
		{
			return true;
		}
		if ($params->get('delete_overflow', true))
		{
			return true;
		}
		if (JString::strlen($data) > (int) $params->get('textarea-maxlength'))
		{
			return false;
		}
		return true;
	}

	/**
	 * @return string error message raised from failed validation
	 */

	function getValidationErr()
	{
		return JText::_('PLG_ELEMENT_TEXTAREA_CONTENT_TOO_LONG');
	}

	/**
	 * @return	string	joomfish translation type e.g. text/textarea/referenceid/titletext
	 */

	function getJoomfishTranslationType()
	{
		return 'textarea';
	}

	/**
	 * @return	array	key=>value options
	 */

	function getJoomfishOptions()
	{
		$params = $this->getParams();
		$return  = array();
		if ($params->get('textarea-showmax'))
		{
			$return['maxlength'] = $params->get('textarea-maxlength');
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
}
?>