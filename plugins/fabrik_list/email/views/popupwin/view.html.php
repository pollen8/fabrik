<?php
/**
 * Email list plugin view
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.list.email
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */


// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

/**
 * Email list plugin view
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.list.email
 * @since       3.0
 */

class FabrikViewPopupwin extends JViewLegacy
{

	/**
	 * Display the view
	 *
	 * @param   string  $tmpl  Template
	 *
	 * @return  JView  this
	 */

	public function display($tmpl = 'default')
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$renderOrder = $input->getInt('renderOrder');
		$usersConfig = JComponentHelper::getParams('com_fabrik');

		$tmplpath = JPATH_ROOT . '/plugins/fabrik_list/email/views/popupwin/tmpl/' . $tmpl;
		$this->_setPath('template', $tmplpath);

		$this->showToField = $this->get('ShowToField');
		$this->fieldList = $this->get('ToField');
		$records = $this->get('records');
		if (count($records) == 0)
		{
			JError::raiseNotice(500, 'None of the selected records can be emailed');
			return;
		}
		$this->recordcount = count($records);
		$this->renderOrder = $renderOrder;
		$this->recordids = implode(',', $records);
		$this->listid = $this->get('id', 'list');
		$this->showSubject = $this->get('ShowSubject');
		$this->subject = $this->get('subject');
		$this->message = $this->get('message');
		$this->allowAttachment = $this->get('allowAttachment');

		$srcs = FabrikHelperHTML::framework();
		FabrikHelperHTML::script($srcs);
		return parent::display();
	}

}
