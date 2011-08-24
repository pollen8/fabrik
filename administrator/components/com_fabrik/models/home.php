<?php
/*
 * Cron Model
 *
 * @package Joomla.Administrator
 * @subpackage Fabrik
 * @since		1.6
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');


class FabrikModelHome extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_HOME';

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 * @return	JTable	A database object
	 * @since	1.6
	 */

	public function getTable($type = 'Cron', $prefix = 'FabrikTable', $config = array())
	{
		$config['dbo'] = FabriKWorker::getDbo();
		return parent::getTable($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */

	public function getForm($data = array(), $loadData = true)
	{
		return false;
	}

	/**
	 * get fabrikar.com rss feed
	 * @return string
	 */

	function getRSSFeed()
	{
		//  get RSS parsed object
		$options = array();
		$options['rssUrl']		= 'http://feeds.feedburner.com/fabrik';
		$options['cache_time']	= 86400;

		$rssDoc =& JFactory::getXMLparser('RSS', $options);
		if ($rssDoc == false) {
			$output = JText::_('Error: Feed not retrieved');
		} else {
			// channel header and link
			$title 	= $rssDoc->get_title();
			$link	= $rssDoc->get_link();

			$output = '<table class="adminlist">';
			$output .= '<tr><th colspan="3"><a href="'.$link.'" target="_blank">'.JText::_($title) .'</th></tr>';

			$items = array_slice($rssDoc->get_items(), 0, 3);
			$numItems = count($items);
			if ($numItems == 0) {
				$output .= '<tr><th>' .JText::_('No news items found'). '</th></tr>';
			} else {
				$k = 0;
				for ($j = 0; $j < $numItems; $j++) {
					$item = $items[$j];
					$output .= '<tr><td class="row' .$k. '">';
					$output .= '<a href="' .$item->get_link(). '" target="_blank">' .$item->get_title(). '</a>';
					$output .= '<br />'.$item->get_date('Y-m-d') ;
					if($item->get_description()) {
						$description = FabrikString::truncate($item->get_description(), array('wordcount'=>50));
						$output .= '<br />' .$description;
					}
					$output .= '</td></tr>';
				}
			}
			$k = 1 - $k;

			$output .= '</table>';
		}
		return $output;
	}

	/**
	 * install sample data
	 */

	public function installSampleData()
	{
		$db = FabrikWorker::getDbo();
		$group = $this->getTable('Group');
		$config = JFactory::getConfig();

		$dbTableName = $config->getValue('dbprefix') . "fb_contact_sample";
		echo "<div style='text-align:left;border:1px dotted #cccccc;padding:10px;'>" .
		"<h3>Installing data...</h3><ol>";

		//$group = FabTable::getInstance('Group', 'Table');
		$group->name = "Contact Details";
		$group->label = "Contact Details";
		$group->published = 1;
		if (!$group->store()) {
			return JError::raiseWarning(500, $group->getError());
		}
		$groupId = $db->insertid();

		$sql = "DROP TABLE IF EXISTS $dbTableName;";
		$db->setQuery($sql);
		$db->query();

		echo "<li>Group 'Contact Details' created</li>";

		$element = $this->getTable('Element');
		$element->label = "First Name";
		$element->name = "first_name";
		$element->plugin = "field";
		$element->show_in_list_summary = 1;
		$element->link_to_detail = 1;
		$element->width = 30;
		$element->group_id = $groupId;
		$element->published = 1;
		$element->ordering = 1;
		if (!$element->store()) {
			return JError::raiseWarning(500, $element->getError());
		}

		echo "<li>Element 'First Name' added to group 'Contact Details'</li>";

		$element = $this->getTable('Element');
		$element->label = "Last Name";
		$element->name = "last_name";
		$element->plugin = "field";
		$element->show_in_list_summary = 1;
		$element->width = 30;
		$element->link_to_detail = 1;
		$element->group_id = $groupId;
		$element->published = 1;
		$element->ordering = 2;
		if (!$element->store()) {
			return JError::raiseWarning(500, $element->getError());
		}
		echo "<li>Element 'Last Name' added to group 'Contact Details'</li>";

		$element = $this->getTable('Element');
		$element->label = "Email";
		$element->show_in_list_summary = 1;
		$element->name = "email";
		$element->plugin = "field";
		$element->width = 30;
		$element->group_id = $groupId;
		$element->published = 1;
		$element->ordering = 3;
		if (!$element->store()) {
			return JError::raiseWarning(500, $element->getError());
		}
		echo "<li>Element 'Email' added to group 'Contact Details'</li>";

		$group = $this->getTable('Group');
		$group->name = "Your Enquiry";
		$group->label = "Your Enquiry";
		$group->published = 1;

		if (!$group->store()) {
			return JError::raiseWarning(500, $group->getError());
		}
		$group2Id = $db->insertid();
		echo "<li>Group 'Your Enquiry' created</li>";

		$element = $this->getTable('Element');
		$element->label = "Message";
		$element->name = "message";
		$element->plugin = "textarea";
		$element->show_in_list_summary = 0;
		$element->width = 30;
		$element->height = 10;
		$element->published = 1;
		$element->ordering = 1;
		$element->group_id = $group2Id;
		if (!$element->store()) {
			return JError::raiseWarning(500, $element->getError());
		}
		echo "<li>Element 'Message' added to group 'Your Enquiry'</li>";

		$form = $this->getTable('Form');
		$form->label = "Contact Us";
		$form->record_in_database = 1;
		$form->intro = "This is a sample contact us form, that is stored in a database table";

		$form->submit_button_label = "Submit";
		$form->published = 1;

		$form->form_template = "default";
		$form->view_only_template = "default";

		if (!$form->store()) {
			return JError::raiseWarning(500, $form->getError());
		}
		echo "<li>Form 'Contact Us' created</li>";
		$formId = $db->insertid();

		$query = $db->getQuery(true);
		$query->insert('#__{package}_formgroup')->set(array('form_id='.(int)$formId, 'group_id='.(int)$groupId, 'ordering=0'));
		$db->setQuery($query);
		$db->query();
		echo $db->getErrorMsg();

		$query = $db->getQuery(true);
		$query->insert('#__{package}_formgroup')->set(array('form_id='.(int)$formId, 'group_id='.(int)$group2Id, 'ordering=1'));
		$db->setQuery($query);
		$db->query();
		echo $db->getErrorMsg();
		echo "<li>Groups added to 'Contact Us' form</li>";
		//JModel::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models');
		$listModel = JModel::getInstance('List', 'FabrikFEModel');
		$list = $this->getTable('List');
		$list->label = "Contact Us Data";
		$list->introduction = "This table stores the data submitted in the contact us for";
		$list->form_id = $formId;
		$list->connection_id = 1;
		$list->db_table_name = $dbTableName;
		// store without name quotes as that's db specific
		$list->db_primary_key = $dbTableName.'.id';
		$list->auto_inc = 1;
		$list->published = 1;
		$list->params = json_encode($listModel->getDefaultParams());
		$list->template = 'default';

		if (!$list->store()) {
			JError::raiseWarning(500, $list->getError());
		}
		echo "<li>Table for 'Contact Us' created</li></div>";
		if (!$form->store()) {
			JError::raiseError(500, $form->getError());
		}
		$formModel = JModel::getInstance('Form', 'FabrikFEModel');
		//echo "seeting form model id to " . $form->id;
		$formModel->setId($form->id);
		$formModel->_form = $form;

		$listModel->setId($list->id);
		$listModel->getTable();
		$listModel->createDBTable($formModel, $list->db_table_name, $db);
	}

	/**
	 * empty all fabrik db tables of their data
	 */

	public function reset()
	{
		$db = FabrikWorker::getDbo();
		$prefix = '#__{package}_';
		$tables = array('cron', 'elements',
		'formgroup', 'forms', 'form_sessions', 'groups', 'joins',
		'jsactions', 'packages', 'lists', 'validations',
		'visualizations');

		foreach ($tables as $table) {
			$db->setQuery("TRUNCATE TABLE " . $prefix.$table);
			if (!$db->query()) {
				return JError::raiseError(500, $db->getErrorMsg() . ": " . $db->getQuery());
			}
		}
	}

	/**
	 * drop all the lists db tables
	 */

	public function dropData()
	{
		//JModel::addIncludePath(JPATH_SITE.DS.'components'.DS.'com_fabrik'.DS.'models');
		$connModel 	=& JModel::getInstance('Connection', 'FabrikFEModel');
		$connModel->setId($item->connection_id);
		$db = FabrikWorker::getDbo();
		$query = $db->getQuery(true);
		$query->select("connection_id, db_table_name")->from('#__{package}_lists');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		foreach ($rows as $row) {
			$connModel->setId($row->connection_id);
			$c = $connModel->getConnection($row->connection_id);
			$fabrikDb = $connModel->getDb();
			if (!JError::isError($fabrikDb)) {
				$fabrikDb->setQuery("DROP $row->db_table_name");
			} else {
				jexit("error with getting connection id " . $row->connection_id . " for " . $row->db_table_name);
			}
			$fabrikDb->query();
		}
	}

}
