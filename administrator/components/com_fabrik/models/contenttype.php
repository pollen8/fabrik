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
class FabrikAdminModelContentType extends FabModelAdmin
{

	/**
	 * Include paths for searching for Content type XML files
	 *
	 * @var    array
	 */
	private static $_contentTypeIncludePaths = array();

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
		parent::__construct($config);
		$listModel = ArrayHelper::getValue($config, 'listModel', JModelLegacy::getInstance('List', 'FabrikAdminModel'));

		if (!is_a($listModel, 'FabrikAdminModelList'))
		{
			throw new UnexpectedValueException('Content Type Constructor requires an Admin List Model');
		}
		$this->listModel = $listModel;
	}

	/**
	 * Method to get the select content type form.
	 *
	 * @param   array $data     Data for the form.
	 * @param   bool  $loadData True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since    3.3.5
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_fabrik.content-type', 'content-type', array('control' => 'jform', 'load_data' => $loadData));

		if (empty($form))
		{
			return false;
		}

		return $form;
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
		$paths = self::addContentTypeIncludePath();
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
				$elementData['params']   = $this->nodeParams($element);
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

		foreach ($params as $param)
		{
			if ($param->hasAttributes())
			{
				foreach ($param->attributes as $attr)
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
	public static function addContentTypeIncludePath($path = null)
	{
		// If the internal paths have not been initialised, do so with the base table path.
		if (empty(self::$_contentTypeIncludePaths))
		{
			self::$_contentTypeIncludePaths = JPATH_COMPONENT_ADMINISTRATOR . '/models/content_types';
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
				if (!in_array($dir, self::$_contentTypeIncludePaths))
				{
					array_unshift(self::$_contentTypeIncludePaths, $dir);
				}
			}
		}

		return self::$_contentTypeIncludePaths;
	}

	/**
	 * Prepare the group and element models for form view preview
	 *
	 * @return array
	 */
	public function preview()
	{
		$pluginManager = FabrikWorker::getPluginManager();
		$xpath         = new DOMXpath($this->doc);
		$groups        = $xpath->query('/contenttype/group');
		$return        = array();

		foreach ($groups as $group)
		{
			$groupData           = array();
			$groupData           = $this->domNodeAttributesToArray($group, $groupData);
			$groupData['params'] = $this->nodeParams($group);
			$groupModel          = JModelLegacy::getInstance('Group', 'FabrikFEModel');
			$groupTable          = FabTable::getInstance('Group', 'FabrikTable');
			$groupTable->bind($groupData);
			$groupModel->setGroup($groupTable);

			$elements      = $xpath->query('/contenttype/group/element');
			$elementModels = array();

			foreach ($elements as $element)
			{
				$elementData            = $this->domNodeAttributesToArray($element);
				$elementData['params']  = $this->nodeParams($element);
				$elementModel           = clone($pluginManager->getPlugIn($elementData['plugin'], 'element'));
				$elementModel->element  = $elementModel->getDefaultProperties($elementData);
				$elementModel->editable = true;
				$elementModels[]        = $elementModel;
			}

			$groupModel->elements = $elementModels;
			$return[]             = $groupModel;
		}

		return $return;
	}

	/**
	 * Get default insert fields - either from content type or defaultfields input value
	 *
	 * @param string|null $contentType
	 *
	 * @return array
	 */
	public function getDefaultInsertFields($contentType = null)
	{
		$input = $this->app->input;

		if (!is_null($contentType))
		{
			$fields = $this->loadContentType($contentType)
					->createGroupsFromContentType();
		}
		else
		{
			// Could be importing from a CSV in which case default fields are set.
			$fields =  $input->get('defaultfields',array('id' => 'internalid', 'date_time' => 'date'));
		}

		return $fields;
	}
}
