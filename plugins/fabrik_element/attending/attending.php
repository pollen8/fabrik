<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.attending
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

require_once JPATH_SITE . '/components/com_fabrik/models/element.php';

/**
 * Plugin element to allow user to attend events, join groups etc.
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.element.attending
 * @since       3.0
 */
class PlgFabrik_ElementAttending extends PlgFabrik_Element
{
	/**
	 * Db table field type
	 *
	 * @var string
	 */
	protected $fieldDesc = 'TINYINT(%s)';

	/**
	 * Db table field size
	 *
	 * @var string
	 */
	protected $fieldSize = '1';

	/**
	 * Determines if the element can contain data used in sending receipts,
	 * e.g. fabrikfield returns true
	 *
	 * @deprecated - not used
	 *
	 * @return  bool
	 */

	public function isReceiptElement()
	{
		return true;
	}

	/**
	 * Draws the html form element
	 *
	 * @param   array $data          to preopulate element with
	 * @param   int   $repeatCounter repeat group counter
	 *
	 * @return  string    elements html
	 */

	public function render($data, $repeatCounter = 0)
	{
		$id = $this->getHTMLId($repeatCounter);

		$layout            = $this->getLayout('form');
		$data              = array();
		$data['attendees'] = $this->getAttendees();
		$data['id']        = $id;

		return $layout->render($data);
	}

	protected function getAttendees()
	{
		$app       = JFactory::getApplication();
		$input     = $app->input;
		$listModel = $this->getListModel();
		$list      = $listModel->getTable();
		$listId    = $list->id;
		$formId    = $listModel->getFormModel()->getId();
		$db        = $listModel->getDb();
		$query     = $db->getQuery(true);
		$rowId    = $input->get('row_id');

		$query->select('*')->from('#__fabrik_attending')->where('list_id = ' . (int) $listId)
			->where('form_id = ' . (int) $formId)
			->where('row_id = ' . $db->q($rowId))
			->where('element_id = ' . (int) $this->getId());

		$attending = $db->setQuery($query)->loadObjectList();

		foreach ($attending as &$attend)
		{
			$attend->user = JFactory::getUser($attend->user_id);
		}

		return $attending;
	}

	/**
	 * Called via widget ajax, stores the selected rating and returns the average
	 *
	 * @return  void
	 */

	public function onAjax_rate()
	{
		$app   = JFactory::getApplication();
		$input = $app->input;
		$this->setId($input->getInt('element_id'));
		$this->loadMeForAjax();
		$listModel = $this->getListModel();
		$list      = $listModel->getTable();
		$listid    = $list->id;
		$formid    = $listModel->getFormModel()->getId();
		$row_id    = $input->get('row_id');
		$rating    = $input->getInt('rating');
		$this->doRating($listid, $formid, $row_id, $rating);

		if ($input->get('mode') == 'creator-rating')
		{
			// @todo FIX for joins as well

			// Store in elements table as well
			$db      = $listModel->getDb();
			$element = $this->getElement();
			$query   = $db->getQuery(true);
			$query->update($list->db_table_name)
				->set($element->name . '=' . $rating)->where($list->db_primary_key . ' = ' . $db->quote($row_id));
			$db->setQuery($query);
			$db->execute();
		}

		$this->getRatingAverage('', $listid, $formid, $row_id);
		echo $this->avg;
	}

	/**
	 * Main method to store a rating
	 *
	 * @param   int    $listid List id
	 * @param   int    $formid Form id
	 * @param   string $row_id Row reference
	 * @param   int    $rating Rating
	 *
	 * @return  void
	 */

	private function doRating($listid, $formid, $row_id, $rating)
	{
		$this->createRatingTable();
		$db        = FabrikWorker::getDbo(true);
		$config    = JFactory::getConfig();
		$tzoffset  = $config->get('offset');
		$date      = JFactory::getDate('now', $tzoffset);
		$strDate   = $db->quote($date->toSql());
		$userid    = JFactory::getUser()->get('id');
		$elementid = (int) $this->getElement()->id;
		$query     = $db->getQuery(true);
		$formid    = (int) $formid;
		$listid    = (int) $listid;
		$rating    = (int) $rating;
		$row_id    = $db->quote($row_id);
		$db
			->setQuery(
				"INSERT INTO #__fabrik_ratings (user_id, listid, formid, row_id, rating, date_created, element_id)
		values ($userid, $listid, $formid, $row_id, $rating, $strDate, $elementid)
			ON DUPLICATE KEY UPDATE date_created = $strDate, rating = $rating"
			);
		$db->execute();
	}

	/**
	 * Returns javascript which creates an instance of the class defined in formJavascriptClass()
	 *
	 * @param   int $repeatCounter Repeat group counter
	 *
	 * @return  array
	 */

	public function elementJavascript($repeatCounter)
	{
		$app    = JFactory::getApplication();
		$input  = $app->input;
		$user   = JFactory::getUser();
		$params = $this->getParams();

		$id      = $this->getHTMLId($repeatCounter);
		$element = $this->getElement();
		$data    = $this->getFormModel()->data;
		$listid  = $this->getlistModel()->getTable()->id;
		$formid  = $input->getInt('formid');
		$row_id  = $input->get('rowid', '', 'string');

		$opts         = new stdClass;
		$opts->row_id = $row_id;
		$opts->elid   = $this->getElement()->id;
		$opts->userid = (int) $user->get('id');
		$opts->view   = $input->get('view');

		return array('FbAttending', $id, $opts);
	}

	/**
	 * Get the class to manage the form element
	 * to ensure that the file is loaded only once
	 *
	 * @param   array  &$srcs  Scripts previously loaded
	 * @param   string $script Script to load once class has loaded
	 * @param   array  &$shim  Dependant class names to load before loading the class - put in requirejs.config shim
	 *
	 * @return void
	 */

	public function formJavascriptClass(&$srcs, $script = '', &$shim = array())
	{
		$s                                   = new stdClass;
		$s->deps                             = array('fab/elementlist');
		$shim['element/attending/attending'] = $s;

		parent::formJavascriptClass($srcs, $script, $shim);
	}
}
