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

require_once(JPATH_LIBRARIES .'/joomla/document/html/html.php');

/**
 * DocumentPDF class, provides an easy interface to parse and display a pdf document
 *
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.5
 */
class JDocumentpdf extends JDocumentHTML
{
	private $engine	= null;

	private $name = 'joomla';

	/**
	 * Class constructore
	 * @param	array	$options Associative array of options
	 */

	function __construct($options = array())
	{
		parent::__construct($options);

		//set mime type
		$this->_mime = 'application/pdf';

		//set document type
		$this->_type = 'pdf';

		if (!$this->iniDomPdf())
		{
			JError::raiseError(JText::_('COM_FABRIK_ERR_NO_PDF_LIB_FOUND'));
		}
	}

	protected function iniDomPdf()
	{
		$file = JPATH_LIBRARIES .'/dompdf/dompdf_config.inc.php';
		if (!JFile::exists($file))
		{
			return false;
		}
		if (!defined('DOMPDF_ENABLE_REMOTE'))
		{
			define('DOMPDF_ENABLE_REMOTE', true);
		}
		require_once($file);
		// Default settings are a portrait layout with an A4 configuration using millimeters as units
		$this->engine =new DOMPDF();
		return true;
	}

	/**
	 * Sets the document name
	 * @param   string   $name	Document name
	 * @return  void
	 */

	public function setName($name = 'joomla')
	{
		$this->name = $name;
	}

	/**
	 * Returns the document name
	 * @return	string
	 */

	public function getName()
	{
		return $this->name;
	}

	/**
	 * Render the document.
	 * @access public
	 * @param boolean 	$cache		If true, cache the output
	 * @param array		$params		Associative array of attributes
	 * @return	string
	 */

	function render($cache = false, $params = array())
	{
		$pdf = $this->engine;
		$data = parent::render();
 		$this->fullPaths($data);
		//echo $data;exit;
		$pdf->load_html($data);
		$pdf->render();
		$pdf->stream($this->getName() . '.pdf');
		return '';
	}

	/**
	 * (non-PHPdoc)
	 * @see JDocumentHTML::getBuffer()
	 */

	public function getBuffer($type = null, $name = null, $attribs = array())
	{
		if ($type == 'head' || $type == 'component')
		{
			return parent::getBuffer($type, $name, $attribs);
		}
		else
		{
			return '';
		}
	}


	/**
	 * parse relative images a hrefs and style sheets to full paths
	 * @param	string	&$data
	 */

	private function fullPaths(&$data)
	{
		$data = str_replace('xmlns=', 'ns=', $data);
		libxml_use_internal_errors(true);
		try
		{
			$ok = new SimpleXMLElement($data);
			if ($ok)
			{
				$uri = JUri::getInstance();
				$base = $uri->getScheme() . '://' . $uri->getHost();
				$imgs = $ok->xpath('//img');
				foreach ($imgs as &$img)
				{
					if (!strstr($img['src'], $base))
					{
						$img['src'] = $base . $img['src'];
					}
				}
				//links
				$as = $ok->xpath('//a');
				foreach ($as as &$a)
				{
					if (!strstr($a['href'], $base))
					{
						$a['href'] = $base . $a['href'];
					}
				}
			
				// css files.
				$links = $ok->xpath('//link');
				foreach ($links as &$link)
				{
					if ($link['rel'] == 'stylesheet' && !strstr($link['href'], $base))
					{
						$link['href'] = $base . $link['href'];
					}
				}
				$data = $ok->asXML();
			}
		} catch (Exception $err)
		{
			//oho malformed html - if we are debugging the site then show the errors
			// otherwise continue, but it may mean that images/css/links are incorrect
			$errors = libxml_get_errors();
			if (JDEBUG)
			{
				echo "<pre>";print_r($errors);echo "</pre>";
				exit;
			} 
		}
		
	}

}