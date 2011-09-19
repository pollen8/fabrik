<?php
/**
 * Form email plugin
 * @package Joomla
 * @subpackage Fabrik
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

define('FABRIK2ARTICLE_PLUGIN', JPATH_SITE.DS.'plugins'.DS.'fabrik_form'.DS.'fabrik2article');

//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND.DS.'models'.DS.'plugin-form.php');
require_once(FABRIK2ARTICLE_PLUGIN.DS.'controllers'.DS.'article.php');

class plgFabrik_FormFabrik2Article extends plgFabrik_Form {

	/**
	 * run from table model when deleting rows
	 *
	 * @return bol
	 */

	function onDeleteRowsForm(&$params, &$formModel, &$groups)
	{
		$this->formModel = $formModel;
		if ($params->get('fabrik2article_article_id_element') != '' && $params->get('fabrik2article_delete_article', false))
		{
			$articleidfield = $this->_getFieldName($params, 'fabrik2article_article_id_element');
			$f2c = new FabrikControllerArticle();
			$f2c->setFormModel($formModel);
			$f2c->setParameters($params);
			$cids = array();
			foreach ($groups as $group) {
				foreach ($group as $rows) {
					foreach ($rows as $row) {
						if (isset($row->$articleidfield)) {
							if (!empty($row->$articleidfield)) {
								$cids[] = (int)$row->$articleidfield;
							}
						}
					}
				}
			}
			$f2c->deleteArticles($cids);
		}
		return true;
	}

	function onAfterProcess(&$params, &$formModel) {
        if ($this->_fabrik2article($params, $formModel) === false) {
            return false;
        }

 		return true;
 	}

	function _fabrik2article(&$params, &$formModel) {
        $f2c = new FabrikControllerArticle();
        $f2c->setFormModel($formModel);
        $f2c->setParameters($params);
		//$this->formModel = $formModel;
		//$f2c->data 		= array_merge($this->getEmailData(), $formModel->_formData);
		//$f2c->data 		= $this->getEmailData();
        //$f2c->setAlternateTemplatePath(FABRIK2ARTICLE_PLUGIN.DS.'views'.DS.'article'.DS.'tmpl');

        $f2c->execute();

        return true;
	}

	/**
	 * get the element full name for the element id
	 * @param plugin params
	 * @param int element id
	 * @return string element full name
	 */

	private function _getFieldName($params, $pname)
	{
		$elementModel = FabrikWorker::getPluginManager()->getElementPlugin($params->get($pname));
		$element = $elementModel->getElement(true);
		return $elementModel->getFullName();
	}
}
?>