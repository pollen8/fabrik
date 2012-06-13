<?php
/**
 * Plugin element to render fields
 * @package fabrikar
 * @author peamak
 * @copyright (C) fabrikar.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once(JPATH_SITE . '/components/com_fabrik/models/element.php');

class plgFabrik_ElementYoutube extends plgFabrik_Element {

	var $_pluginName = 'youtube';

	/**
	 * shows the data formatted for the table view
	 * @param string data
	 * @param object all the data in the tables current row
	 * @return string formatted value
	 */

	public function renderListData($data, &$thisRow)
	{
		$params = $this->getParams();
		// ------------------ Construct embedded player

		// Player size
		if (($params->get('display_in_table') == 2) || ($params->get('display_in_table') == 1)) { // Display in table = Normal
			if ($params->get('or_width_player') != NULL) {
				$width = $params->get('or_width_player');
				$height = $params->get('or_height_player');
			} else if ($params->get('player_size') == 'small') {
				$width = '340';
				$height = '285';
			} else if ($params->get('player_size') == 'medium') {
				$width = '445';
				$height = '364';
			} else if ($params->get('player_size') == 'normal') {
				$width = '500';
				$height = '405';
			} else if ($params->get('player_size') == 'big') {
				$width = '660';
				$height = '525';
			}
		} else if ($params->get('display_in_table') == 0) { // Display in table = Mini
				$width = '170';
				$height = '142';
		}

			// Include related videos
			if ($params->get('include_related') == 0) {
				$rel = '&rel=0';
			} else {
				$rel = '';
			}

			// Show border
			if (($params->get('show_border') == 1) && ($params->get('display_in_table') != 0)) { // Don't show borders if display in table = Mini
				$border = '&border=1';
			} else {
				$border = '';
			}

			// Enable delayed cookies
			if ($params->get('enable_delayed_cookies') == 1) {
				$url = 'http://www.youtube-nocookie.com/v/';
			} else {
				$url = 'http://www.youtube.com/v/';
			}

			// Colors
			$color1 = JString::substr($params->get('color1'), -6);
			$color2 = JString::substr($params->get('color2'), -6);

			$vid = array_pop(explode("/", $data));
                        //$$$tom: if one copies an URL from youtube, the URL has the "watch?v=" which barfs the player
                        if (strstr($vid, 'watch')) {
                            $vid = explode("=", $vid);
                            unset($vid[0]); // That's the watch?v=
                            $vid = implode('', $vid);
                        }
			if ($vid == '') {
				//$$$ rob perhaps they just added in the code???
				$vid = $data;
			}
			if ($data != NULL) {
				if ($params->get('display_in_table') == 1) { // Display link
					if ($params->get('display_link') == 0) {
						$object_vid = $data;
					} else {
						if ($params->get('display_link') == 1) {
							$dlink = $data;
						} else {
							if ($params->get('text_link') != NULL) {
								$dlink = $params->get('text_link');
							} else {
								$dlink = 'Watch Video';
							}
						}
						if ($params->get('target_link') == 1) {
							$object_vid = '<a href="'.$data.'" target="blank">'.$dlink.'</a>';
						} else if ($params->get('target_link') == 2) {


							$element =& $this->getElement();
							$object_vid = "<a href='".$data."' rel='lightbox[social ".$width." ".$height."]' title='".$element->label."'>".$dlink."</a>";


						} else {
							$object_vid = '<a href="'.$data.'">'.$dlink.'</a>';
						}
					}
				} else {
					$object_vid = '<object width="'.$width.'" height="'.$height.'"><param name="movie" value="'.$url.$vid.'&hl=en&fs=1'.$rel.'&color1=0x'.$color1.'&color2=0x'.$color2.$border.'"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="'.$url.$vid.'&hl=en&fs=1'.$rel.'&color1=0x'.$color1.'&color2=0x'.$color2.$border.'" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="'.$width.'" height="'.$height.'"></embed></object>';
				}
			} else {
				$object_vid = '';
			}

			return $object_vid;
		//}
	}


	/**
	 * do we need to include the lighbox js code
	 *
	 * @return bol
	 */

	function requiresLightBox()
	{
		return true;
	}

	/**
	 * determines if the element can contain data used in sending receipts, e.g. fabrikfield returns true
	 */

	function isReceiptElement()
	{
		return true;
	}

	/**
	 * draws the form element
	 * @param array data to preopulate element with
	 * @param int repeat group counter
	 * @return string returns element html
	 */

	function render($data, $repeatCounter = 0)
	{
		if (JRequest::getVar('view') != 'details') {

			$name 			= $this->getHTMLName($repeatCounter);
			$id 				= $this->getHTMLId($repeatCounter);
			$params 		=& $this->getParams();
			$element 		=& $this->getElement();
			$size 			= $params->get('width');
			//$maxlength  = $params->get('maxlength');
			$maxlength = 255;
			$bits = array();
			$data 	=& $this->_form->_data;
			$value 	= $this->getValue($data, $repeatCounter);
			$type = "text";
			if (isset($this->_elementError) && $this->_elementError != '') {
				$type .= " elementErrorHighlight";
			}

			if (!$this->_editable) {
				return($element->hidden == '1') ? "<!-- " . $value . " -->" : $value;
			}

			$bits['class']		= "fabrikinput inputbox $type";
			$bits['type']		= $type;
			$bits['name']		= $name;
			$bits['id']			= $id;
			//stop "'s from breaking the content out of the field.
			$bits['value']		= htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
			$bits['size']		= $size;
			$bits['maxlength']	= $maxlength;


			$str = "<input ";
			foreach ($bits as $key=>$val) {
				$str.= "$key = \"$val\" ";
			}
			$str .= " />\n";
			return $str;
		} else {
			$params 		=& $this->getParams();
			$element 		=& $this->getElement();
			$data 	=& $this->_form->_data;
			$value 	= $this->getValue($data, $repeatCounter);
		// ------------------ Construct embedded player

		// Player size
		if ($params->get('or_width_player') != NULL) {
			$width = $params->get('or_width_player');
			$height = $params->get('or_height_player');
		} else {
			if ($params->get('player_size') == 'small') {
				$width = '340';
				$height = '285';
			} else if ($params->get('player_size') == 'medium') {
				$width = '445';
				$height = '364';
			} else if ($params->get('player_size') == 'normal') {
				$width = '500';
				$height = '405';
			} else {
				$width = '660';
				$height = '525';
			}
		}

			// Include related videos
			if ($params->get('include_related') == 0) {
				$rel = '&rel=0';
			} else {
				$rel = '';
			}

			// Show border
			if ($params->get('show_border') == 1) {
				$border = '&border=1';
			} else {
				$border = '';
			}

			// Enable delayed cookies
			if ($params->get('enable_delayed_cookies') == 1) {
				$url = 'http://www.youtube-nocookie.com/v/';
			} else {
				$url = 'http://www.youtube.com/v/';
			}

			// Colors
			$color1 = JString::substr($params->get('color1'), - 6);
			$color2 = JString::substr($params->get('color2'), - 6);
			// $$$ rob - barfed if url entered was like http://www.youtube.com/v/zD8XclVc3DQ&hl=en
			//$vid = JString::substr($value, 31);
			//this seems more sturdy a method:
			$vid = array_pop(explode("/", $value));
			//$$$tom: if one copies an URL from youtube, the URL has the "watch?v=" which barfs the player
			if (strstr($vid, 'watch')) {
				$vid = explode("=", $vid);
				unset($vid[0]); // That's the watch?v=
				$vid = implode('', $vid);
			}
			if ($vid == '') {
				//$$$ rob perhaps they just added in the code???
				$vid = $value;
			}
			if ($value != NULL) {
				$object_vid = '<object width="'.$width.'" height="'.$height.'"><param name="movie" value="'.$url.$vid.'&hl=en&fs=1'.$rel.'&color1=0x'.$color1.'&color2=0x'.$color2.$border.'"></param><param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><embed src="'.$url.$vid.'&hl=en&fs=1'.$rel.'&color1=0x'.$color1.'&color2=0x'.$color2.$border.'" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" width="'.$width.'" height="'.$height.'"></embed></object>';
			} else {
				$object_vid = '';
			}

			return $object_vid;
		}
	}


	/**
	 * return tehe javascript to create an instance of the class defined in formJavascriptClass
	 * @return string javascript to create instance. Instance name must be 'el'
	 */

	function elementJavascript($repeatCounter)
	{
		$id = $this->getHTMLId($repeatCounter);
		$opts =& $this->getElementJSOptions($repeatCounter);
		$opts = json_encode($opts);
		return "new FbYouTube('$id', $opts)";
	}

	/**
	 * load the javascript class that manages interaction with the form element
	 * should only be called once
	 * @return string javascript class file
	 */

	function formJavascriptClass(&$srcs, $script = '')
	{
		plgFabrik_Element::formJavascriptClass($srcs, 'plugins/fabrik_element/youtube/youtube.js');
		parent::formJavascriptClass($srcs);
	}

	/**
	 * defines the type of database table field that is created to store the element's data
	 */
	function getFieldDescription()
	{
		$p =& $this->getParams();
		if ($this->encryptMe()) {
			return 'BLOB';
		}
		$group =& $this->getGroup();
		if ($group->isJoin() == 0 && $group->canRepeat()) {
			return "TEXT";
		} else {
			$objtype = "VARCHAR(" . $p->get('maxlength', 255) . ")";
		}
		return $objtype;
	}

}
?>