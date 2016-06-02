<?php
/**
 * @package     Joomla.Framework
 * @subpackage  Document
 * @copyright   Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license     GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

file_exists(JPATH_LIBRARIES . '/joomla/document/html/html.php') && require_once JPATH_LIBRARIES . '/joomla/document/html/html.php';
require_once JPATH_SITE . '/components/com_fabrik/helpers/pdf.php';

/**
 * DocumentPDF class, provides an easy interface to parse and display a pdf document
 *
 * @package     Joomla.Framework
 * @subpackage  Document
 * @since       1.5
 */
class JDocumentpartial extends JDocumentHTML
{
	/**
	 * Render the document.
	 *
	 * @param   boolean  $cache   If true, cache the output
	 * @param   array    $params  Associative array of attributes
	 *
	 * @return	string
	 */
	public function render($cache = false, $params = array())
	{
		$data = parent::render();

		$lnEnd = $this->_getLineEnd();
		$tab = $this->_getTab();
		$tagEnd = ' />';
		$buffer = '';

		// Generate script declarations
		foreach ($this->_script as $type => $content)
		{
			$buffer .= $tab . '<script type="' . $type . '">' . $lnEnd;

			// This is for full XHTML support.
			if ($this->_mime != 'text/html')
			{
				$buffer .= $tab . $tab . '<![CDATA[' . $lnEnd;
			}

			$buffer .= $content . $lnEnd;

			// See above note
			if ($this->_mime != 'text/html')
			{
				$buffer .= $tab . $tab . ']]>' . $lnEnd;
			}
			$buffer .= $tab . '</script>' . $lnEnd;
		}

		return $buffer . $data;
	}

	/**
	 * Get the contents of a document include
	 *
	 * @param   string  $type     The type of renderer
	 * @param   string  $name     The name of the element to render
	 * @param   array   $attribs  Associative array of remaining attributes.
	 *
	 * @return  The output of the renderer
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
	 * Render pre-parsed template
	 *
	 * @return string rendered template
	 *
	 * @since   11.1
	 */
	protected function _renderTemplate()
	{
		$replace = array();
		$with = array();

		$template = <<<EOT
	<jdoc:include type="message" />
	<jdoc:include type="component" />
EOT;


		foreach ($this->_template_tags as $jdoc => $args)
		{
			$replace[] = $jdoc;
			$with[] = $this->getBuffer($args['type'], $args['name'], $args['attribs']);
		}

		return str_replace($replace, $with, $template);
	}

}
