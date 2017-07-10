<?php
/**
* @version		$Id: pdf.php 14401 2010-01-26 14:10:00Z louis $
* @package		Joomla.Framework
* @subpackage	Document
* @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

/**
 * DocumentPDF class, provides an easy interface to parse and display a pdf document
 *
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.5
 */
class JDocumentPDF extends JDocument
{
	var $_engine	= null;

	var $_name		= 'joomla';

	var $_header	= null;
	var $_header_font = 'courier';
	var $_footer_font = 'courier';

	var $_margin_header	= 5;
	var $_margin_footer	= 10;
	var $_margin_top	= 27;
	var $_margin_bottom	= 25;
	var $_margin_left	= 15;
	var $_margin_right	= 15;

	// Scale ratio for images [number of points in user unit]
	var $_image_scale	= 4;

	/**
	 * Class constructore
	 *
	 * @access protected
	 * @param	array	$options Associative array of options
	 */
	function __construct($options = array())
	{
		parent::__construct($options);

		if (isset($options['margin-header'])) {
			$this->_margin_header = $options['margin-header'];
		}

		if (isset($options['margin-footer'])) {
			$this->_margin_footer = $options['margin-footer'];
		}

		if (isset($options['margin-top'])) {
			$this->_margin_top = $options['margin-top'];
		}

		if (isset($options['margin-bottom'])) {
			$this->_margin_bottom = $options['margin-bottom'];
		}

		if (isset($options['margin-left'])) {
			$this->_margin_left = $options['margin-left'];
		}

		if (isset($options['margin-right'])) {
			$this->_margin_right = $options['margin-right'];
		}

		if (isset($options['image-scale'])) {
			$this->_image_scale = $options['image-scale'];
		}

		//set mime type
		$this->_mime = 'application/pdf';

		//set document type
		$this->_type = 'pdf';

	


		/*
		 * Create the pdf document
		 */
		// Default settings are a portrait layout with an A4 configuration using millimeters as units
		if(!class_exists('TCPDF')) require(JPATH_ROOT.DS.'libraries'.DS.'tcpdf'.DS.'tcpdf.php');
		$this->_engine = new TCPDF();

		//set margins
		$this->_engine->SetMargins($this->_margin_left, $this->_margin_top, $this->_margin_right);
		//set auto page breaks
		$this->_engine->SetAutoPageBreak(TRUE, $this->_margin_bottom);
		$this->_engine->SetHeaderMargin($this->_margin_header);
		$this->_engine->SetFooterMargin($this->_margin_footer);
		$this->_engine->setImageScale($this->_image_scale);
	}

	 /**
	 * Sets the document name
	 *
	 * @param   string   $name	Document name
	 * @access  public
	 * @return  void
	 */
	function setName($name = 'joomla') {
		$this->_name = $name;
	}

	/**
	 * Returns the document name
	 *
	 * @access public
	 * @return string
	 */
	function getName() {
		return $this->_name;
	}

	 /**
	 * Sets the document header string
	 *
	 * @param   string   $text	Document header string
	 * @access  public
	 * @return  void
	 */
	function setHeader($text) {
		$this->_header = $text;
	}

	/**
	 * Returns the document header string
	 *
	 * @access public
	 * @return string
	 */
	function getHeader() {
		return $this->_header;
	}

	/**
	 * Render the document.
	 *
	 * @access public
	 * @param boolean 	$cache		If true, cache the output
	 * @param array		$params		Associative array of attributes
	 * @return 	The rendered data
	 */
	function render( $cache = false, $params = array())
	{
		$pdf = &$this->_engine;

		// Set PDF Metadata
		$pdf->SetCreator($this->getGenerator());
		$pdf->SetTitle($this->getTitle());
		$pdf->SetSubject($this->getDescription());
		$pdf->SetKeywords($this->getMetaData('keywords'));

		// Set PDF Header data
		$pdf->setHeaderData('',0,$this->getTitle(), $this->getHeader());

		// Set PDF Header and Footer fonts
		 $lang = JFactory::getLanguage();
		// $font = $lang->getPdfFontName();
		// $font = ($font) ? $font : 'freesans';


		$pdf->setRTL($lang->isRTL());

		$pdf->SetFont('helvetica', '', 8, '', 'false');

		$pdf->setHeaderFont(array($this->_header_font, '', 10));
		$pdf->setFooterFont(array($this->_footer_font, '', 8));



		// Initialize PDF Document
		$pdf->getAliasNbPages();
		$pdf->AddPage();

		// Build the PDF Document string from the document buffer
		$this->fixLinks();
		$pdf->WriteHTML($this->getBuffer(), true);
		$data = $pdf->Output('', 'S');
		// Set document type headers
		parent::render();

		//JResponse::setHeader('Content-Length', strlen($data), true);
		JResponse::setHeader('Content-type', 'application/pdf', true);
		JResponse::setHeader('Content-disposition', 'inline; filename="'.$this->getName().'.pdf"', true);

		//Close and output PDF document
		return $data;
	}

	function fixLinks()
	{

	}
}