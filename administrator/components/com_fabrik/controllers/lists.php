<?php
/**
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access.
defined('_JEXEC') or die;

require_once('fabcontrolleradmin.php');

/**
 * Lists list controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	Fabrik
 * @since		1.6
 */
class FabrikControllerLists extends FabControllerAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_LISTS';

	protected $view_item = 'lists';

	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */

	public function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */

	public function &getModel($name = 'List', $prefix = 'FabrikModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

	/**
	 * Method to publish a list of taxa
	 *
	 * @since	1.6
	 */

	function publish()
	{
		$cid = JRequest::getVar('cid', array(), '', 'array');
		$data = array('publish' => 1, 'unpublish' => 0, 'archive'=> 2, 'trash' => -2, 'report'=>-3);
		$task = $this->getTask();
		$value = JArrayHelper::getValue($data, $task, 0, 'int');
		if (empty($cid))
		{
			JError::raiseWarning(500, JText::_($this->text_prefix.'_NO_ITEM_SELECTED'));
		}
		else
		{
			// Make sure the item ids are integers
			JArrayHelper::toInteger($cid);
			$model = $this->getModel('Form');
			$formids = $model->swapListToFormIds($cid);
			// Publish the items.
			$formKeys = array();
			if (!$model->publish($formids, $value))
			{
				JError::raiseWarning(500, $model->getError());
			}
			else
			{
				//publish the groups
				$groupModel = $this->getModel('Group');
				if (is_object($groupModel))
				{
					$groupids = $groupModel->swapFormToGroupIds($formids);
					if (!empty($groupids))
					{
						if ($groupModel->publish($groupids, $value) === false)
						{
							JError::raiseWarning(500, $groupModel->getError());
						}
						else
						{
							//publish the elements
							$elementModel = $this->getModel('Element');
							$elementIds = $elementModel->swapGroupToElementIds($groupids);
							if (!$elementModel->publish($elementIds, $value))
							{
								JError::raiseWarning(500, $elementModel->getError());
							}
						}
					}
				}
				//finally publish the list
				parent::publish();
			}
		}
		$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
	}

	/**
	 * Set up page asking about what to delete
	 * @since	1.6
	 */

	function delete()
	{
		$model = $this->getModel();
		$viewType = JFactory::getDocument()->getType();
		$view = $this->getView($this->view_item, $viewType);
		$view->setLayout('confirmdelete');
		if (!JError::isError($model))
		{
			$view->setModel($model, true);
		}
		//used to load in the confirm form fields
		$view->setModel($this->getModel('list'));
		$view->display();
	}

}