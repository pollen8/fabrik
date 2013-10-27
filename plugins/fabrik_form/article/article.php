<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.article
 * @copyright   Copyright (C) 2005-2013 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Require the abstract plugin class
require_once COM_FABRIK_FRONTEND . '/models/plugin-form.php';

/**
 * Create Joomla article(s) upon form submission
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.article
 * @since       3.0
 */

class PlgFabrik_FormArticle extends PlgFabrik_Form
{
	/**
	 * Create articles - needed to be before store as we are altering the metastore element's data
	 *
	 * @param   object  $params      Plugin params
	 * @param   object  &$formModel  Form model
	 *
	 * @return	bool
	 */

	public function onAfterProcess($params, &$formModel)
	{
		$this->formModel = $formModel;
		$params = $this->getParams();
		$this->data = array_merge($formModel->_formData, $this->getEmailData());

		if (!$this->shouldProcess('article_conditon', null))
		{
			return;
		}

		$store = $this->metaStore();
		$categories = (array) $params->get('categories');

		foreach ($categories as $category)
		{
			$id = isset($store->$category) ? $store->$category : null;
			$item = $this->saveAritcle($id, $category);
			$store->$category = $item->id;
		}

		$this->setMetaStore($store);

		return true;
	}

	/**
	 * Save article
	 *
	 * @param   int  $id     Article Id
	 * @param   int  $catid  Category Id
	 *
	 * @return JTable
	 */
	protected function saveAritcle($id, $catid)
	{
		$params = $this->getParams();
		$data = array('articletext' => $this->buildContent(), 'catid' => $catid, 'state' => 1, 'language' => '*');
		$attribs = array('title', 'publish_up', 'publish_down', 'featured', 'state', 'metadesc', 'metakey');

		$data['images'] = json_encode($this->images());
		if (is_null($id))
		{
			$data['created'] = JFactory::getDate()->toSql();
			$attribs[] = 'created_by';
		}
		else
		{
			$data['modified'] = JFactory::getDate()->toSql();
		}

		foreach ($attribs as $attrib)
		{
			$elementId = $params->get($attrib);
			$data[$attrib] = $this->findElementData($elementId, $data);
		}

		$this->generateNewTitle($catid, $data);

		$item = JTable::getInstance('Content');
		$item->load($id);
		$item->bind($data);
		$item->store();

		return $item;
	}

	/**
	 * Get the element data from the Fabrik form
	 *
	 * @param   int    $elementId  Element id
	 * @param   array  $data       Data
	 *
	 * @return mixed
	 */
	protected function findElementData($elementId)
	{
		$value = '';
		if ($elementModel = $this->formModel->getElement($elementId, true))
		{
			$fullName = $elementModel->getFullName(false, true, false);
			$value = $this->formModel->getElementData($fullName, false, '', 0);

			if (is_array($value))
			{
				$value = array_shift($value);
			}
		}

		return $value;
	}

	/**
	 * Build the Joomla article image data
	 *
	 * @return stdClass
	 */
	protected function images()
	{
		$params = $this->getParams();
		$introImg = $params->get('image_intro', '');
		$fullImg = $params->get('image_full', '');
		$img = new stdClass;

		if ($introImg !== '')
		{
			$img->image_intro = $this->findElementData($introImg);
			$img->image_intro = str_replace('\\', '/', $img->image_intro);

			$img->image_intro = FabrikString::ltrimword($img->image_intro, '/');
			$img->float_intro = '';
			$img->image_intro_alt = '';
			$img->image_intro_caption = '';
		}

		if ($fullImg !== '')
		{
			$img->image_fulltext = $this->findElementData($introImg);
			$img->image_fulltext = str_replace('\\', '/', $img->image_fulltext);
			$img->image_fulltext = FabrikString::ltrimword($img->image_fulltext, '/');
			$img->float_fulltext = '';
			$img->image_fulltext_alt = '';
			$img->image_fulltext_caption = '';
		}

		return $img;
	}

	/**
	 * Method to change the title & alias.
	 *
	 * @param   integer  $catid  The id of the category.
	 * @param   array    &$data  The row data.
	 *
	 * @return	null
	 */
	protected function generateNewTitle($catid, &$data)
	{
		// Alter the title & alias
		$table = JTable::getInstance('Content');
		$alias = $data['title'];
		$title = $data['title'];

		while ($table->load(array('alias' => $alias, 'catid' => $catid)))
		{
			$title = JString::increment($title);
			$alias = JString::increment($alias, 'dash');
		}

		$data['title'] = $title;
		$data['alias'] = $alias;
	}

	/**
	 * Update the form model with the new meta store data
	 *
	 * @param   object  $store  Meta store (categoryid => articleid)
	 *
	 * @return  null
	 */
	protected function setMetaStore($store)
	{
		$params = $this->getParams();

		if ($elementModel = $this->formModel->getElement($params->get('meta_store'), true))
		{
			$key = $elementModel->getElement()->name;
			$val = json_encode($store);
			$listModel = $this->formModel->getListModel();

			// Ensure we store to the main db table
			$listModel->clearTable();
			$rowId = JFactory::getApplication()->input->getString('rowid');
			$listModel->storeCell($rowId, $key, $val);
		}
	}

	/**
	 * Get the meta store - this is a categoryid => articleid mapping object
	 *
	 * @return  object
	 */
	protected function metaStore()
	{
		$params = $this->getParams();
		$metaStore = new stdClass;

		if ($elementModel = $this->formModel->getElement($params->get('meta_store'), true))
		{
			$fullName = $elementModel->getFullName(false, true, false);
			$metaStore = $this->formModel->getElementData($fullName);
			$metaStore = json_decode($metaStore);
		}

		return $metaStore;
	}

	/**
	 * Run from list model when deleting rows
	 *
	 * @param   object  $params      Plugin parameters
	 * @param   object  &$formModel  Form model
	 * @param   array   &$groups     List data for deletion
	 *
	 * @return	bool
	 */

	public function onDeleteRowsForm($params, &$formModel, &$groups)
	{
		$params = $this->getParams();
		$item = JTable::getInstance('Content');

		if ($elementModel = $formModel->getElement($params->get('meta_store'), true))
		{
			$fullName = $elementModel->getFullName(false, true, false);
			foreach ($groups as $group)
			{
				foreach ($group as $rows)
				{
					foreach ($rows as $row)
					{
						$store = json_decode($row->$fullName);

						if (is_object($store))
						{
							$store = JArrayHelper::fromObject($store);

							foreach ($store as $catid => $articleId)
							{
								$item->delete($articleId);
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Create the article content
	 *
	 * @return string
	 */
	protected function buildContent()
	{
		$formModel = $this->formModel;
		$app = JFactory::getApplication();
		$input = $app->input;
		$package = $app->getUserState('com_fabrik.package', 'fabrik');
		$params = $this->getParams();
		$template = JPath::clean(JPATH_SITE . '/plugins/fabrik_form/article/tmpl/' . $params->get('template', ''));
		$contentTemplate = $params->get('template_content');
		$content = $contentTemplate != '' ? $this->_getConentTemplate($contentTemplate) : '';
		$messageTemplate = '';

		if (JFile::exists($template))
		{
			$messageTemplate = JFile::getExt($template) == 'php' ? $this->_getPHPTemplateEmail($template) : $this
			->_getTemplateEmail($template);

			$messageTemplate = str_replace('{content}', $content, $messageTemplate);
		}

		$message = '';

		if (!empty($messageTemplate))
		{
			$message = $messageTemplate;
		}
		elseif (!empty($content))
		{
			$message = $content;
		}

		// $$$ hugh - test stripslashes(), should be safe enough.
		$message = stripslashes($message);

		$editURL = COM_FABRIK_LIVESITE . 'index.php?option=com_' . $package . '&amp;view=form&amp;fabrik=' . $formModel->get('id') . '&amp;rowid='
			. $input->get('rowid', '', 'string');
		$viewURL = COM_FABRIK_LIVESITE . 'index.php?option=com_' . $package . '&amp;view=details&amp;fabrik=' . $formModel->get('id') . '&amp;rowid='
			. $input->get('rowid', '', 'string');
		$editlink = '<a href="' . $editURL . '">' . JText::_('EDIT') . '</a>';
		$viewlink = '<a href="' . $viewURL . '">' . JText::_('VIEW') . '</a>';
		$message = str_replace('{fabrik_editlink}', $editlink, $message);
		$message = str_replace('{fabrik_viewlink}', $viewlink, $message);
		$message = str_replace('{fabrik_editurl}', $editURL, $message);
		$message = str_replace('{fabrik_viewurl}', $viewURL, $message);
		$w = new FabrikWorker;

		return $w->parseMessageForPlaceholder($message, $this->data, false);
	}

	/**
	 * Use a php template for advanced email templates, partularly for forms with repeat group data
	 *
	 * @param   string  $tmpl  Path to template
	 *
	 * @return string email message
	 */

	protected function _getPHPTemplateEmail($tmpl)
	{
		$emailData = $this->data;

		// Start capturing output into a buffer
		ob_start();
		$result = require $tmpl;
		$message = ob_get_contents();
		ob_end_clean();

		if ($result === false)
		{
			return false;
		}

		return $message;
	}

	/**
	 * Template email handling routine, called if email template specified
	 *
	 * @param   string  $template  path to template
	 *
	 * @return  string	email message
	 */

	protected function _getTemplateEmail($template)
	{
		return file_get_contents($template);
	}

	/**
	 * Get content item template
	 *
	 * @param   int  $contentTemplate  Joomla article ID to load
	 *
	 * @return  string  content item html (translated with Joomfish if installed)
	 */

	protected function _getConentTemplate($contentTemplate)
	{
		$app = JFactory::getApplication();

		if ($app->isAdmin())
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('introtext, ' . $db->quoteName('fulltext'))->from('#__content')->where('id = ' . (int) $contentTemplate);
			$db->setQuery($query);
			$res = $db->loadObject();
		}
		else
		{
			JModelLegacy::addIncludePath(COM_FABRIK_BASE . 'components/com_content/models');
			$articleModel = JModelLegacy::getInstance('Article', 'ContentModel');
			$res = $articleModel->getItem($contentTemplate);
		}

		return $res->introtext . ' ' . $res->fulltext;
	}
}
