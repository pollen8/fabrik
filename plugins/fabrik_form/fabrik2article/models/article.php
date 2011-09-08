<?php
// ensure a valid entry point
defined('_JEXEC') or die('Restricted Access');

define('JPATH_ADMINISTRATOR_COM_CONTENT', JPATH_ADMINISTRATOR.DS.'components'.DS.'com_content');

require_once(COM_FABRIK_FRONTEND.DS.'models'.DS.'form.php');
require_once(JPATH_ADMINISTRATOR_COM_CONTENT.DS.'helper.php');

class FabrikModelArticle extends FabrikModelForm {

    var $_formModel = null;
    var $_articleText = '';
    var $_articleIdElement = null;
    var $_articleTitleElement = null;
    var $_articleId = 0;
    var $_sectionId = 0;
    var $_categoryId = 0;
    var $_isNew = true;

    function __construct() {
        parent::__construct();

        $this->addTablePath(FABRIK2ARTICLE_PLUGIN.DS.'tables');
    }

    function setFormModel(&$model) {
        $this->_formModel = $model;
    }

    function &getFormModel() {
        return $this->_formModel;
    }

    function setArticleText($text = '') {
        $this->_articleText = $text;
    }

    function getArticleText() {
        return $this->_articleText;
    }

    function setArticleIdElement($articleIdElement) {
        $this->_articleIdElement = $articleIdElement;
    }

    function getArticleIdElement() {
        return $this->_articleIdElement;
    }

    function setArticleTitleElement($articleTitleElement) {
        $this->_articleTitleElement = $articleTitleElement;
    }

    function getArticleTitleElement() {
        return $this->_articleTitleElement;
    }

    function setArticlePublishElement($articlePublishElement) {
        $this->_articlePublishElement = $articlePublishElement;
    }

    function getArticlePublishElement() {
        return $this->_articlePublishElement;
    }

    function setArticleId($articleId) {
        $this->_articleId = $articleId;
    }

    function getArticleId() {
        return $this->_articleId;
    }

    function setSectionId($sectionId) {
        $this->_sectionId = $sectionId;
    }

    function getSectionId() {
        return $this->_sectionId;
    }

    function setCategoryId($categoryId) {
        $sectionId = $this->_selectSectionId($categoryId);
        if (empty($sectionId)) {
            $this->sectionId = 0;
            $this->_categoryId = 0;
        } else {
            $this->_sectionId = $sectionId;
            $this->_categoryId = $categoryId;
        }
    }

    function getCategoryId() {
        return $this->_categoryId;
    }

    function _selectSectionId($categoryId) {
		$db = FabrikWorker::getDbo();
		$sql =
            'SELECT '
                . $db->nameQuote('section')
            . ' FROM '
                . $db->nameQuote('#__categories')
            . ' WHERE '
                . $db->nameQuote('id') . ' = ' . $categoryId
           ;
		$db->setQuery($sql);

        return (int)$db->loadResult();
    }

	/*
	 * Code for deleteArticles() mostly stolen from J!'s removeContent() in the com_content backend controller
	*/

	function deleteArticles(&$cid) {
		if (empty($cid)) {
			return false;
		}

		$db			= & FabrikWorker::getDbo();

		$nullDate	= $db->getNullDate();

		JArrayHelper::toInteger($cid);

		if (count($cid) < 1) {
			return false;
		}

		// Removed content gets put in the trash [state = -2] and ordering is always set to 0
		$state		= '-2';
		$ordering	= '0';

		// Get the list of content id numbers to send to trash.
		$cids = implode(',', $cid);

		// Update articles in the database
		$query = 'UPDATE #__content' .
				' SET state = '.(int)$state .
				', ordering = '.(int)$ordering .
				', checked_out = 0, checked_out_time = '.$db->Quote($nullDate).
				' WHERE id IN ( '. $cids. ')';
		$db->setQuery($query);
		if (!$db->query())
		{
			JError::raiseError(500, $db->getErrorMsg());
			return false;
		}

		$cache = & JFactory::getCache('com_content');
		$cache->clean();

		return true;
	}

	function save() {
        if (!empty($this->_articleIdElement) && !empty($this->_articleTitleElement)) {
            $this->saveArticle();
            $this->updateFabrikData();
        }
    }

	function saveArticle() {
		// Initialize variables
		$db = FabrikWorker::getDbo();
		$user = JFactory::getUser();
		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('content');

        $this->_postFabrikDataAsArticleData();

		$details	= JRequest::getVar('details', array(), 'post', 'array');
		$option		= JRequest::getCmd('option');
		$sectionid	= JRequest::getVar('sectionid', 0, '', 'int');
		$nullDate	= $db->getNullDate();

		$row = & FabTable::getInstance('content');

		if (!$row->bind(JRequest::get('post'))) {
			JError::raiseError(500, $db->stderr());
			return false;
		}
		$row->bind($details);

		// sanitise id field
		$row->id = (int)$row->id;

		$this->_isNew = true;
		// Are we saving from an item edit?
		if ($row->id) {
			$this->_isNew = false;
			$datenow = JFactory::getDate();
			$row->modified 		= $datenow->toMySQL();
			$row->modified_by 	= $user->get('id');
		}

		$row->created_by 	= $row->created_by ? $row->created_by : $user->get('id');

		if ($row->created && strlen(trim($row->created )) <= 10) {
			$row->created 	.= ' 00:00:00';
		}

		$config = JFactory::getConfig();
		$tzoffset = $config->getValue('config.offset');
		$date = JFactory::getDate($row->created, $tzoffset);
		$row->created = $date->toMySQL();

		// Append time if not added to publish date
		if (strlen(trim($row->publish_up)) <= 10) {
			$row->publish_up .= ' 00:00:00';
		}

		$date = JFactory::getDate($row->publish_up, $tzoffset);
		$row->publish_up = $date->toMySQL();

		// Handle never unpublish date
		if (trim($row->publish_down) == JText::_('Never') || trim($row->publish_down) == '')
		{
			$row->publish_down = $nullDate;
		}
		else
		{
			if (strlen(trim($row->publish_down )) <= 10) {
				$row->publish_down .= ' 00:00:00';
			}
			$date = JFactory::getDate($row->publish_down, $tzoffset);
			$row->publish_down = $date->toMySQL();
		}

		// Get a state and parameter variables from the request
		// should probably punt this logic into the controller, but for now ...
		$articlePublishElementName = $this->_elementBaseName($this->_articlePublishElement);
		$row->state = $this->_formModel->_formData[$articlePublishElementName];
		// probably an array, i.e. coming from a yes/no radio or dropdown
		if (is_array($row->state)) {
			$row->state = $row->state[0];
		}
		$params		= JRequest::getVar('params', null, 'post', 'array');

		$row->params = json_encode($params);
		// Get metadata string
		$metadata = JRequest::getVar('meta', null, 'post', 'array');
		if (is_array($metadata))
		{
			$txt = array();
			foreach ($metadata as $k => $v) {
				if ($k == 'description') {
					$row->metadesc = $v;
				} elseif ($k == 'keywords') {
					$row->metakey = $v;
				} else {
					$txt[] = "$k=$v";
				}
			}
			$row->metadata = implode("\n", $txt);
		}

		// Prepare the content for saving to the database
		ContentHelper::saveContentPrep( $row);

		// Make sure the data is valid
		if (!$row->check()) {
			JError::raiseError(500, $db->stderr());
			return false;
		}

		// Increment the content version number
		$row->version++;

		$result = $dispatcher->trigger('onBeforeContentSave', array(&$row, $this->_isNew));
		if(in_array(false, $result, true)) {
			JError::raiseError(500, $row->getError());
			return false;
		}

		// Store the content to the database
		if (!$row->store()) {
			JError::raiseError(500, $db->stderr());
			return false;
		}

        $this->_articleId = $row->id;

		// Check the article and update item order
		$row->checkin();
		$row->reorder('catid = '.(int)$row->catid.' AND state >= 0');

//		*
//		 * We need to update frontpage status for the article.
//		 *
//		 * First we include the frontpage table and instantiate an instance of it.
//		 *
		require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_frontpage'.DS.'tables'.DS.'frontpage.php');
		$fp = new TableFrontPage($db);

		// Is the article viewable on the frontpage?
		if (JRequest::getVar('frontpage', 0, '', 'int'))
		{
			// Is the item already viewable on the frontpage?
			if (!$fp->load($row->id))
			{
				// Insert the new entry
				$query = 'INSERT INTO #__content_frontpage' .
						' VALUES ( '. (int)$row->id .', 1 )';
				$db->setQuery($query);
				if (!$db->query())
				{
					JError::raiseError(500, $db->stderr());
					return false;
				}
				$fp->ordering = 1;
			}
		}
		else
		{
			// Delete the item from frontpage if it exists
			if (!$fp->delete($row->id)) {
				$msg .= $fp->stderr();
			}
			$fp->ordering = 0;
		}
		$fp->reorder();

		$cache = & JFactory::getCache('com_content');
		$cache->clean();

		$dispatcher->trigger('onAfterContentSave', array(&$row, $this->_isNew));
	}

    function updateFabrikData() {
        if ($this->_isNew && $this->_articleId > 0) {
            $articleIdElementName = $this->_elementBaseName($this->_articleIdElement);
            $this->_formModel->_formData[$articleIdElementName] = $this->_articleId;
            $this->_formModel->_formData[$articleIdElementName . '_raw'] = $this->_articleId;
            $listModel = $this->_formModel->getlistModel();
            $listModel->_oForm = $this->_formModel;
            $primaryKey = FabrikString::shortColName($listModel->getTable()->db_primary_key);
            $this->_formModel->_formData[$primaryKey] = $this->_formModel->_fullFormData['rowid'];
            $this->_formModel->_formData[$primaryKey . '_raw'] = $this->_formModel->_fullFormData['rowid'];
            $listModel->storeRow($this->_formModel->_formData, $this->_formModel->_fullFormData['rowid']);
        }
    }

    function _postFabrikDataAsArticleData() {
        // Required
        if ($this->_articleId <= 0) {
            $articleIdElementName = $this->_elementBaseName($this->_articleIdElement);
            $this->_articleId = $this->_formModel->_formData[$articleIdElementName];

        }
        JRequest::setVar('id', $this->_articleId, 'post');

        $titleElementName = $this->_elementFullName($this->_articleTitleElement);
        //$title = $this->_formModel->_formData[$titleElementName];
        $title = $this->_data[$titleElementName];
        JRequest::setVar('title', $title, 'post');

        JRequest::setVar('text', $this->_articleText, 'post');
        JRequest::setVar('sectionid', $this->_sectionId, 'post');
        JRequest::setVar('catid', $this->_categoryId, 'post');

        // Optional
		/*
        $optionalElements = array(
            'state' => 0
      );
        foreach ($optionalElements as $element => $defaultValue) {
            $this->_optionalSetting($element, $defaultValue);
        }
		*/
    }

    function _optionalSetting($attribute, $default = null) {
        $value = null;

        if (array_key_exists($attribute, $this->_formModel->_formData)) {
            if (is_array($this->_formModel->_formData[$attribute])) {
                if (count($this->_formModel->_formData[$attribute]) == 1) {
                    $value = $this->_formModel->_formData[$attribute][0];
                }
            } else {
                $value = $this->_formModel->_formData[$attribute];
            }
        } else if (!is_null($default)) {
            $value = $default;
        }

        if (!is_null($value)) {
            JRequest::setVar($attribute, $value, 'post');
        }
    }

    function _elementBaseName($element) {
        $parts = explode('.', $element);
        return $parts[1];
    }

	function _elementFullName($element) {
        $parts = explode('.', $element);
        return implode('___',$parts);
    }

	function getArticleCss($tmpl = 'default')
	{
		$document = JFactory::getDocument();
		$cssFiles = array();
		/* check for a custom css file */
		$cssFiles[] = JURI::root(true) .'/media/com_fabrik/css/form.css';
		/* check for a form template file (code moved from view) */
		if ($tmpl != '') {
			$aCssPath = FABRIK2ARTICLE_PLUGIN.DS.'views'.DS.'article'.DS.'tmpl'.DS.$tmpl.'.css';
			if (JFile::exists($aCssPath)) {
				$cssFiles[] = 	JURI::root(true) . '/components/com_fabrik/views/form/tmpl/'."$tmpl".'/template.css';
			}
		}
		/*
		if (JRequest::getVar('tmpl') !== 'component') {
			foreach ($cssFiles as $css) {
				$document->addStyleSheet($css);
			}
		}
		*/
		return $cssFiles;
	}
}