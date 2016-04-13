<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Fabrik\Helpers;

defined('JPATH_BASE') or die;

use \JLoader;
use \JPath;

/**
 * Base class for rendering a display layout
 * loaded from from a layout file
 *
 * @package     Joomla.Libraries
 * @subpackage  Layout
 * @see         http://docs.joomla.org/Sharing_layouts_across_views_or_extensions_with_JLayout
 * @since       3.0
 */
class LayoutFile extends \JLayoutFile
{
	/**
	 * Method to finds the full real file path, checking possible overrides
	 *
	 * @return  string  The full path to the layout file
	 *
	 * @since   3.0
	 */
	protected function getPath()
	{
		JLoader::import('joomla.filesystem.path');

		if (is_null($this->fullPath) && !empty($this->layoutId))
		{
			$this->addDebugMessage('<strong>Layout:</strong> ' . $this->layoutId);

			/*
			 * Refresh paths - override is due to this line
			 * -commenting out to stop default paths being re-instated in render()
			 *
			 * $this->refreshIncludePaths(false);
			 */

			$this->addDebugMessage('<strong>Include Paths:</strong> ' . print_r($this->includePaths, true));

			$suffixes = $this->options->get('suffixes', array());

			// Search for suffixed versions. Example: tags.j31.php
			if (!empty($suffixes))
			{
				$this->addDebugMessage('<strong>Suffixes:</strong> ' . print_r($suffixes, true));

				foreach ($suffixes as $suffix)
				{
					$rawPath  = str_replace('.', '/', $this->layoutId) . '.' . $suffix . '.php';
					$this->addDebugMessage('<strong>Searching layout for:</strong> ' . $rawPath);

					if ($this->fullPath = JPath::find($this->includePaths, $rawPath))
					{
						$this->addDebugMessage('<strong>Found layout:</strong> ' . $this->fullPath);

						return $this->fullPath;
					}
				}
			}

			// Standard version
			$rawPath  = str_replace('.', '/', $this->layoutId) . '.php';
			$this->addDebugMessage('<strong>Searching layout for:</strong> ' . $rawPath);

			$this->fullPath = JPath::find($this->includePaths, $rawPath);

			if ($this->fullPath = JPath::find($this->includePaths, $rawPath))
			{
				$this->addDebugMessage('<strong>Found layout:</strong> ' . $this->fullPath);
			}
		}

		return $this->fullPath;
	}
}
