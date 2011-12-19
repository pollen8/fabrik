<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_fabrik'.DS.'helpers'.DS.'element.php');

/**
 * Renders a upload size field
 *
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class JFormFieldUploadsize extends JFormField
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Uploadsize';

	// $$$ hugh - ini settings can be in K, M or G
	protected function _return_bytes($val)
	{
		$val = trim($val);
		$last = strtolower(substr($val, -1));

		if($last == 'g')
			$val = $val*1024*1024*1024;
		if($last == 'm')
			$val = $val*1024*1024;
		if($last == 'k')
			$val = $val*1024;

		return $val;
	}

	function getInput()
	{
		$size = $this->element['size'] ? 'size="'.$this->element['size'].'"' : '';
		$class = $this->element['class'] ? 'class="'.$this->element['class'].'"' : 'class="text_area"';
		$value = htmlspecialchars(html_entity_decode($this->value, ENT_QUOTES), ENT_QUOTES);
		if ($value == '') {
			$value = $this->getMax();
		}
		return '<input type="text" name="'.$this->name.'" id="'.$this->id.'" value="'.$value.'" '.$class.' '.$size.' />';
	}

	/**
	 * (non-PHPdoc)
	 * @see JFormField::getLabel()
	 */
	function getLabel()
	{
		// Get the label text from the XML element, defaulting to the element name.
		$text = $this->element['label'] ? (string) $this->element['label'] : (string) $this->element['name'];
		$text = $this->translateLabel ? JText::_($text) : $text;
		$max = $this->getMax();
		$mb = $max/1024;
		$this->description = JText::_($this->description). $max .'Kb / '.$mb.'Mb';
		return parent::getLabel();
	}

	/**
	 * get the max upload size allowed by the server.
	 * @return int kilobyte upload size
	 */

	protected function getMax()
	{
		$post_value 	= $this->_return_bytes(ini_get('post_max_size'));
		$upload_value 	= $this->_return_bytes(ini_get('upload_max_filesize'));
		$value = min($post_value, $upload_value);
		$value = $value / 1024;
		return $value;
	}
}