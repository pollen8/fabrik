<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();


class FabrikString extends JString{

	/**
	 * UTF-8 aware - replace the first word
	 *
	 * @static
	 * @access public
	 * @param string the string to be trimmed
	 * @param string the word to trim
	 * @return string the trimmed string
	 */

	function ltrimword($str, $word = false)
	{
		$pos = strpos($str,$word);
		if ($pos === 0) { // true ? then exectue!
			$str = JString::substr($str, strlen($word));
		}
		return $str;
	}

	/**
	 * Right trim a word from a string
	 *
	 * @param string the string to be trimmed
	 * @param string the word to trim
	 * @return string the trimmed string
	 */
	function rtrimword(&$str, $word = false)
	{
		$l = strlen($word);
		$end = substr($str, -$l);
		if ($end === $word) {
			return substr($str, 0, strlen($str)-$l);
		}else{
			return $str;
		}
	}

	/**
	 * UTF-8 aware - remove the first word
	 * CASE INSENSETIVE
	 *
	 * @static
	 * @access public
	 * @param string the string to be trimmed
	 * @param string the word to trim
	 * @return string the trimmed string
	 */

	function ltrimiword($str, $word = false)
	{
		$pos = stripos($str, $word);
		if ($pos === 0) { // true ? then exectue!
			$str = JString::substr($str, strlen($word));
		}
		return $str;
	}

	/**
	 * formats a string to return a safe db col name - eg
	 * table.field is returned as `table`.field`
	 * table is return as `table`
	 *
	 * @param string col name to format
	 * @param string in table`.field` format
	 */

	function safeColName($col)
	{
		$db = FabrikWorker::getDbo();
		$col = str_replace('`', '', $col);
		$splitter = '';
		if (strstr($col, '___')) {
			$splitter = '___';
		}
		if (strstr($col, '.')) {
			$splitter = '.';
		}
		if ($splitter == '') {
			return $db->nameQuote($col);
		}
		if (strstr($col, $splitter)) {
			$col = explode($splitter, $col);
			foreach ($col as &$c) {
				$c = $db->nameQuote($c);
			}
			return implode('.', $col);
		}
		return $col;
	}

	/**
	 * inverse of safeColName takes `table`.`field`
	 * and returns table___field
	 * @param string string in `table`.`field` format
	 * @return string in table___field format
	 */

	function safeColNameToArrayKey($col)
	{
		$col = str_replace(array("`.`", "." ) , "___", $col);
		$col = str_replace("`", "", $col);
		return $col;
	}

	/**
	 * takes tablename.element or tablename___elementname
	 * (with or without quotes) and returns elementname
	 * @param string column name to shorten
	 * @return string element name
	 */

	function shortColName($col)
	{
		if (strstr($col, '.')) {
			$bits = explode('.', $col);
			$col = array_pop($bits);
		} else	if (strstr($col, '___')) {
			$bits = explode('___', $col);
			$col = array_pop($bits);
		}
		$col = str_replace("`", "", $col);
		return $col;
	}

	/**
	 * get a shortened version of the element label - so that the admin pages
	 * don't get too stretched when we populate dropdowns with the label
	 * @param string complete element label
	 * @return string shortened element label
	 */

	function getShortDdLabel($label)
	{
		$label = strip_tags($label);
		preg_replace('/<[a-z][a-z0-9]*[^<>]*>/', '', $label);
		if (strlen($label) > 50) {
			$label = substr($label, 0, 47).'...';
		}
		$label = trim($label);
		return $label;
	}

	/**
	 * clean variable names for use as fabrik element names
	 * whitespace compressed and replaced with '_'
	 * replace all non-alphanumeric chars except _ and - with '_'
	 * 28/06/2011 replaces umlauts with eu
	 * @param $str to clean
	 * @param str from encoding
	 * @paran str to encoding
	 * @return string cleaned
	 */

	function clean($str, $fromEnc = "UTF-8", $toEnc = "ASCII//TRANSLIT")
	{
		//replace umlauts

		$out = "";
		for ($i = 0; $i<strlen($str);$i++){
			$ch= ord($str{$i});

			switch($ch){
				case 195: $out .= "";break;
				case 164: $out .= "ae"; break;
				case 188: $out .= "ue"; break;
				case 182: $out .= "oe"; break;
				case 132: $out .= "Ae"; break;
				case 156: $out .= "Ue"; break;
				case 150: $out .= "Oe"; break;
				default : $out .= chr($ch) ;
			}
		}
		$str = $out;
		if (function_exists('iconv')) {
			$str = (str_replace("'", '', iconv($fromEnc, $toEnc, $str))); // replace accented characters with ascii equivalent e.g. é => e
		}
		$str = preg_replace('/\s+/', '_', $str); // compress internal whitespace and replace with _
		return strtolower(preg_replace('/\W+/', '_', $str));// replace all non-alphanumeric chars except _ and - with '_'
	}

	function truncate($text, $opts = array())
	{
		$text = htmlspecialchars(strip_tags($text), ENT_QUOTES);
		$orig = $text;
		$wordCount = JArrayHelper::getValue($opts, 'wordcount', 10);
		$showTip =  JArrayHelper::getValue($opts, 'tip', true);
		$title =  JArrayHelper::getValue($opts, 'title', "");
		$text = explode(" ", $text);
		$summary = array_slice($text, 0, $wordCount);

		if (count($text) > $wordCount) {
			$summary[] = " ...";
		}
		$summary = implode(" ", $summary);
		if ($showTip) {
			FabrikHelperHTML::tips();
			if($title !== '') {
				$title .= "::";
			}
			$summary = "<span class=\"fabrikTip\" title=\"$title"."$orig\">$summary</span>";
		}
		return $summary;
	}

	function removeQSVar($url, $key) {
	  $url = preg_replace('/(.*)(?|&)' . $key . '=[^&]+?(&)(.*)/i', '$1$2$4', $url . '&');
	  $url = substr($url, 0, -1);
	  return $url;
	}
}
?>