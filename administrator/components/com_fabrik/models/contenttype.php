<?php
/**
 * Fabrik Admin Content Type Model
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 * @since       3.3.5
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require_once 'fabmodeladmin.php';

use Joomla\Utilities\ArrayHelper;

/**
 * Fabrik Admin Content Type Model
 *
 * @package     Joomla.Administrator
 * @subpackage  Fabrik
 * @since       3.3.5
 */
class FabrikAdminModelContentType
{

	/**
	 * Include paths for searching for JTable classes.
	 *
	 * @var    array
	 */
	private static $_includePaths = array();

	/**
	 * Content type DOM document
	 *
	 * @var DOMDocument
	 */
	private $doc;

	/**
	 * Admin List model
	 *
	 * @var FabrikAdminModelList
	 */
	private $listModel;

	/**
	 * Constructor.
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 *
	 * @throws UnexpectedValueException
	 */
	public function __construct($config = array())
	{
		$listModel = ArrayHelper::getValue($config, 'listModel');

		if (!is_a($listModel, 'FabrikAdminModelList'))
		{
			throw new UnexpectedValueException('Content Type Constructor requires an Admin List Model');
		}
		$this->listModel = $listModel;
	}

	/**
	 * Load in a content type
	 *
	 * @param   string $name File name
	 *
	 * @throws UnexpectedValueException
	 *
	 * @return FabrikAdminModelContentType  Allows for chaining
	 */
	public function loadContentType($name)
	{
		$paths = self::addIncludePath();
		$path  = JPath::find($paths, $name);

		if (!$path)
		{
			throw new UnexpectedValueException('Content type not found in paths');
		}

		$xml       = file_get_contents($path);
		$this->doc = new DOMDocument();
		$this->doc->loadXML($xml);

		return $this;
	}

	public function getFields()
	{
		if (!$this->doc)
		{
			throw new UnexpectedValueException('A content type must be loaded before groups can be created');
		}

		$xpath  = new DOMXpath($this->doc);
		$groups = $xpath->query('/contenttype/group');

		foreach ($groups as $group)
		{

		}
	}

	/**
	 * Create groups & elements from loaded content type
	 *
	 * @return array  Created Group Ids
	 */
	public function createGroupsFromContentType()
	{
		if (!$this->doc)
		{
			throw new UnexpectedValueException('A content type must be loaded before groups can be created');
		}

		$groupIds = array();
		$fields   = array();
		$xpath    = new DOMXpath($this->doc);
		$groups   = $xpath->query('/contenttype/group');

		foreach ($groups as $group)
		{
			$groupData           = array();
			$groupData           = $this->domNodeAttributesToArray($group, $groupData);
			$groupData['params'] = $this->nodeParams($group);

			$isJoin   = ArrayHelper::getValue($groupData, 'is_join', false);
			$isRepeat = isset($groupData['params']->repeat_group_button) ? $groupData['params']->repeat_group_button : false;
			$groupId  = $this->listModel->createLinkedGroup($groupData, $isJoin, $isRepeat);
			$elements = $xpath->query('/contenttype/group/element');

			foreach ($elements as $element)
			{
				$elementData             = $this->domNodeAttributesToArray($element);
				$groupData['params']     = $this->nodeParams($element);
				$elementData['group_id'] = $groupId;
				$name                    = (string) $element->getAttribute('name');
				$fields[$name]           = $this->listModel->makeElement($name, $elementData);
			}

			$groupIds[] = $groupId;

		}

		return $fields;
	}

	/**
	 * @param   DOMElement $node
	 *
	 * @return stdClass
	 */
	private function nodeParams($node)
	{
		$params = $node->getElementsByTagName('params');
		$return = new stdClass;
		if ($params->length > 0)
		{
			if ($params[0]->hasAttributes())
			{
				foreach ($params[0]->attributes as $attr)
				{
					$name          = $attr->nodeName;
					$return->$name = $attr->nodeValue;
				}
			}
		}

		return $return;
	}

	/**
	 * Convert a DOM node's properties into an array
	 *
	 * @param   DOMElement $node
	 * @param   array      $data
	 *
	 * @return array
	 */
	private function domNodeAttributesToArray($node, $data = array())
	{
		if ($node->hasAttributes())
		{
			foreach ($node->attributes as $attr)
			{
				$data[$attr->nodeName] = $attr->nodeValue;
			}
		}

		return $data;
	}

	/**
	 * Add a filesystem path where content type XML files should be searched for.
	 * You may either pass a string or an array of paths.
	 *
	 * @param   mixed $path A filesystem path or array of filesystem paths to add.
	 *
	 * @return  array  An array of filesystem paths to find Content type XML files.
	 */
	public static function addIncludePath($path = null)
	{
		// If the internal paths have not been initialised, do so with the base table path.
		if (empty(self::$_includePaths))
		{
			self::$_includePaths = JPATH_COMPONENT_ADMINISTRATOR . '/models/content_types';
		}

		// Convert the passed path(s) to add to an array.
		settype($path, 'array');

		// If we have new paths to add, do so.
		if (!empty($path))
		{
			// Check and add each individual new path.
			foreach ($path as $dir)
			{
				// Sanitize path.
				$dir = trim($dir);

				// Add to the front of the list so that custom paths are searched first.
				if (!in_array($dir, self::$_includePaths))
				{
					array_unshift(self::$_includePaths, $dir);
				}
			}
		}

		return self::$_includePaths;
	}
}
