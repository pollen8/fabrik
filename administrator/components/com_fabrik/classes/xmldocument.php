<?php

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

class JDocumentXML extends JDocument {
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
		$data = '';
		// Render the feed
		$data .= $renderer->render();
		parent::render();
		return $data;
	}

}