<?php

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

class JDocumentYQL extends JDocument {
    /**
     * Class constructor
     *
     * @param array $options Associative array of options
     */

		var $author = null;

		var $documentationURL = null;

		var $sampleQuery = null;

    function __construct($options = array()) {
      // let the parent class do its bit
      parent::__construct($options);
      // set the MIME type
      $this->_type = 'yql';
      //$this->setMimeEncoding("application/json");
      $this->setAuthor('Fabrik');
    }

    function setAuthor($author)
    {
    	$this->author = $author;
    }

    	/**
	 * Render the document.
	 *
	 * @access public
	 * @param boolean 	$cache		If true, cache the output
	 * @param array		$params		Associative array of attributes
	 * @return 	The rendered data
	 */
	function render($cache = false, $params = array())
	{

		// Instantiate feed renderer and set the mime encoding
		require_once(dirname(__FILE__).DS.'renderer'.DS.'xml.php');
		$renderer =& $this->loadRenderer('xml');
		if (!is_a($renderer, 'JDocumentRenderer')) {
			JError::raiseError(404, JText::_('Resource Not Found'));
		}
		$url = 'http://demo.fabrikar.com/index.php?option=com_fabrik&view=table&listid='.$this->listid.'&format=raw&type=xml';
		$url = htmlspecialchars($url, ENT_COMPAT, 'UTF-8');
		$data = '<?xml version="1.0" encoding="UTF-8"?>
<table xmlns="http://query.yahooapis.com/v1/schema/table.xsd">
  <meta>
    <author>'.htmlspecialchars($this->author, ENT_COMPAT, 'UTF-8').'</author>
    <documentationURL>None</documentationURL>
    <description>'.htmlspecialchars($this->description, ENT_COMPAT, 'UTF-8').'</description>
    <sampleQuery>SELECT * FROM {table} WHERE jos_fabrik_calendar_events___visualization_id_raw="0"</sampleQuery>
  </meta>

  <bindings>
    <select itemPath="root.row" produces="XML">
      <urls>
        <url>'.$url.'</url>
      </urls>
    </select>
  </bindings>
</table>';
		// Render the feed
		//$data .= $renderer->render();
		parent::render();
		return $data;
	}

}