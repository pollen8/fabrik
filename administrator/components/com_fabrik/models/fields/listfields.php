<?php
/**
 * Renders a list of elements found in a fabrik list
 *
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');
require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php';

/**
 * Renders a list of elements found in a fabrik list
 *
 * @package     Joomla
 * @subpackage  Form
 * @since       1.6
 */

class JFormFieldListfields extends JFormFieldList
{
	/**
	 * Element name
	 * @access	protected
	 * @var		string
	 */
	var $_name = 'Listfields';

	/**
	 * Objects resulting from this elements queries - keyed on idetifying hash
	 *
	 * @var  array
	 */
	protected $results = null;

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 */

	protected function getInput()
	{
		if (is_null($this->results))
		{
			$this->results = array();
		}
		$app = JFactory::getApplication();
		$input = $app->input;
		$controller = $input->get('view', $input->get('task'));
		$formModel = false;
		$aEls = array();
		$pluginFilters = trim($this->element['filter']) == '' ? array() : explode('|', $this->element['filter']);
		$c = ElementHelper::getRepeatCounter($this);
		$connection = $this->element['connection'];
		/*
		 * 27/08/2011 - changed from default tableelement to id - for juser form plugin - might cause havock
		 * else where but loading elements by id as default seems more robust (and is the default behaviour in f2.1
		 */
		$valueformat = JArrayHelper::getValue($this->element, 'valueformat', 'id');
		$onlylistfields = (int) JArrayHelper::getValue($this->element, 'onlylistfields', 0);
		$showRaw = (bool) JArrayHelper::getValue($this->element, 'raw', false);

		/**
		 * $$$ hugh - added these two options, initially just for the table PK listelements, to avoid issues with
		 * selecting non-INT types, or elements on joined tables, as the PK
		 */
		$noJoins = (bool) JArrayHelper::getValue($this->element, 'nojoins', false);
		$typeFilters = trim($this->element['typefilter']) == '' ? array() : explode('|', strtoupper($this->element['typefilter']));

		$highlightpk = (bool) JArrayHelper::getValue($this->element, 'highlightpk', false);
		switch ($controller)
		{
			case 'validationrule':
				$id = $input->getInt('id');
				$pluginManager = FabrikWorker::getPluginManager();
				$elementModel = $pluginManager->getElementPlugin($id);
				$element = $elementModel->getElement();
				$res = $this->loadFromGroupId($element->group_id);
				break;
			case 'visualization':
			case 'element':
				$repeat = ElementHelper::getRepeat($this) || $this->element['repeat'];

				// @TODO this seems like we could refractor it to use the formModel class as per the table and form switches below?
				//$connectionDd = ($c === false) ? $connection : $connection . '-' . $c;
				$connectionDd = $repeat ? $connection . '-' . $c : $connection;
				if ($connection == '')
				{

					$groupId = isset($this->form->rawData) ? JArrayHelper::getValue($this->form->rawData, 'group_id', 0)
						: $this->form->getValue('group_id');
					$res = $this->loadFromGroupId($groupId);
				}
				else
				{
					$tableDd = $this->element['table'];
					$opts = new stdClass;
					$opts->table = ($repeat) ? 'jform_' . $tableDd . '-' . $c : 'jform_' . $tableDd;
					$opts->conn = 'jform_' . $connectionDd;
					$opts->value = $this->value;
					$opts->repeat = $this->value;
					$opts->highlightpk = (int) $highlightpk;
					$opts = json_encode($opts);
					$script = "new ListFieldsElement('$this->id', $opts);\n";
					FabrikHelperHTML::script('administrator/components/com_fabrik/models/fields/listfields.js', $script);
					$rows = array(JHTML::_('select.option', '', JText::_('SELECT A CONNECTION FIRST')), 'value', 'text');
					$o = new stdClass;
					$o->table_name = '';
					$o->name = '';
					$o->value = '';
					$o->text = JText::_('COM_FABRIK_SELECT_A_TABLE_FIRST');
					$res[] = $o;
				}
				break;
			case 'listform':
			case 'list':
			case 'module':
			case 'item':
			// Menu item
				if ($controller === 'item')
				{
					$id = $this->form->getValue('request.listid');
				}
				else
				{
					$id = $this->form->getValue('id');
				}
				if (!isset($this->form->model))
				{
					if (!in_array($controller, array('item', 'module')))
					{
						// Seems to work anyway in the admin module page - so lets not raise notice
						JError::raiseNotice(500, 'Model not set in listfields field ' . $this->id);
					}
					return;
				}
				$listModel = $this->form->model;
				if ($id !== 0)
				{
					$formModel = $listModel->getFormModel();
					$valfield = $valueformat == 'tableelement' ? 'name' : 'id';
					/**
					 * $$$ hugh - Added nojoins="true" option to listfields, which I added as a param to getElementOptions,
					 * initially just for the PK list, as we really don't want people choosing joined elements as the main table PK!
					 */
					$res = $formModel->getElementOptions(false, $valfield, $onlylistfields, $showRaw, $pluginFilters, $noJoins);
					/**
					 * $$ hugh - new option for 'typefilter', lets us filter by db type, like typefilter="INT|BIGINT"
					 * Specificaly added so we can only show INT types for the list PK, to avoid nasty side effects of converting
					 * a non-INT to an INT PK, thereby destroying any non-numeric the data in that field.
					 * Also added nojoins="true" option to listfields, which I added as a param to getElementOptions, again
					 * initially just for the PK list, as we really don't want people choosing joined elements as the main table PK!
					 */
					if (!empty($typeFilters) && method_exists($listModel, 'getDBFields'))
					{
						list($this_table, $this_value) = explode('.', str_replace('`', '', $this->value));

						$fieldtypes = $listModel->getDBFields();
						foreach ($res as $el_index => $el)
						{
							list($table_name, $el_name) = explode('.', $el->value);
							foreach ($fieldtypes as $fieldtype)
							{
								if ($fieldtype->Field === $el_name)
								{
									if (!in_array($fieldtype->BaseType, $typeFilters))
									{
										/**
										 * Avoid nasty situation if (say) someone already has a PK selected which is not an INT type,
										 * we don't want to unset their PK selection.  May want to enqueue a warning about it, though.
										 */
										if ($el_name != $this_value)
										{
											unset($res[$el_index]);
										}
									}
									break;
								}
							}
						}
					}
				}
				else
				{
					$res = array();
				}

				break;
			case 'form':
				if (!isset($this->form->model))
				{
					JError::raiseNotice(500, 'Model not set in listfields field ' . $this->id);
					return;
				}
				$formModel = $this->form->model;
				$valfield = $valueformat == 'tableelement' ? 'name' : 'id';
				$res = $formModel->getElementOptions(false, $valfield, $onlylistfields, $showRaw, $pluginFilters);
				break;
			case 'group':
				$valfield = $valueformat == 'tableelement' ? 'name' : 'id';
				$id = $this->form->getValue('id');
				$groupModel = JModel::getInstance('Group', 'FabrikFEModel');
				$groupModel->setId($id);
				$formModel = $groupModel->getFormModel();
				$res = $formModel->getElementOptions(false, $valfield, $onlylistfields, $showRaw, $pluginFilters);
				break;
			default:
				return JText::_('THE LISTFIELDS ELEMENT IS ONLY USABLE BY LISTS AND ELEMENTS');
				break;
		}
		$return = '';
		if (is_array($res))
		{
			if ($controller == 'element')
			{
				foreach ($res as $o)
				{
					$s = new stdClass;

					// Element already contains correct key
					if ($controller != 'element')
					{
						$s->value = $valueformat == 'tableelement' ? $o->table_name . '.' . $o->text : $o->value;
					}
					else
					{
						$s->value = $o->value;
					}
					$s->text = FabrikString::getShortDdLabel($o->text);
					$aEls[] = $s;
				}
			}
			else
			{
				foreach ($res as &$o)
				{
					$o->text = FabrikString::getShortDdLabel($o->text);
				}
				$aEls = $res;
			}
			$aEls[] = JHTML::_('select.option', '', '-');

			// For pk fields - we are no longer storing the key with '`' as thats mySQL specific
			$this->value = str_replace('`', '', $this->value);

			// Some elements were stored as names but subsequently changed to ids (need to check for old values an substitute with correct ones)
			if ($valueformat == 'id' && !is_numeric($this->value) && $this->value != '')
			{
				if ($formModel)
				{
					$elementModel = $formModel->getElement($this->value);
					$this->value = $elementModel ? $elementModel->getId() : $this->value;
				}
			}
			$return = JHTML::_('select.genericlist', $aEls, $this->name, 'class="inputbox" size="1" ', 'value', 'text', $this->value, $this->id);
			$return .= '<img style="margin-left:10px;display:none" id="' . $this->id
				. '_loader" src="components/com_fabrik/images/ajax-loader.gif" alt="' . JText::_('LOADING') . '" />';
		}
		return $return;
	}

	/**
	 * Load the element list from the group id
	 *
	 * @param   int  $groupId  Group id
	 *
	 * @since   3.0.6
	 *
	 * @return array
	 */

	protected function loadFromGroupId($groupId)
	{
		$app = JFactory::getApplication();
		$input = $app->input;
		$controller = $input->get('view', $input->get('task'));
		$valueformat = JArrayHelper::getValue($this->element, 'valueformat', 'id');
		$onlylistfields = (int) JArrayHelper::getValue($this->element, 'onlylistfields', 0);
		$pluginFilters = trim($this->element['filter']) == '' ? array() : explode('|', $this->element['filter']);
		$bits = array();
		$showRaw = (bool) JArrayHelper::getValue($this->element, 'raw', false);
		$groupModel = JModel::getInstance('Group', 'FabrikFEModel');
		$groupModel->setId($groupId);
		$optskey = $valueformat == 'tableelement' ? 'name' : 'id';
		$res = $groupModel->getForm()->getElementOptions(false, $optskey, $onlylistfields, $showRaw, $pluginFilters);
		$hash = $controller . '.' . implode('.', $bits);
		if (array_key_exists($hash, $this->results))
		{
			$res = $this->results[$hash];
		}
		else
		{
			$this->results[$hash] = &$res;
		}
		return $res;
	}
}
