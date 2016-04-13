<?php
/**
 * Renders a table of options for controlling the facet / related data links
 *
 * @package     Joomla
 * @subpackage  Form
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Fabrik\Helpers\ArrayHelper;
use Fabrik\Helpers\Text;

jimport('joomla.html.html');
jimport('joomla.form.formfield');
jimport('joomla.form.helper');

/**
 * Renders a table of options for controlling the facet / related data links
 *
 * @package     Joomla
 * @subpackage  Form
 * @since       1.6
 */

class JFormFieldFacetedlinks extends JFormFieldList
{
	/**
	 * Element name
	 * @var		string
	 */
	protected $name = 'Facetedlinks';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 */

	protected function getInput()
	{
		$feListModel = $this->form->model->getFEModel();
		$joins = $feListModel->getJoinsToThisKey();

		if (empty($joins))
		{
			return '<i>' . Text::_('COM_FABRIK_NO_RELATED_DATA') . '</i>';
		}

		$listParams = $feListModel->getParams();
		$formOrder = json_decode($listParams->get('faceted_form_order'));
		$listOrder = json_decode($listParams->get('faceted_list_order'));
		$this->value = (array) $this->value;
		$linkedLists = ArrayHelper::getValue($this->value, 'linkedlist', array());
		$linkedForms = ArrayHelper::getValue($this->value, 'linkedform', array());

		if (empty($listOrder) || is_null($listOrder))
		{
			$listOrder = array_keys($linkedLists);
		}

		if (empty($formOrder) || is_null($formOrder))
		{
			$formOrder = array_keys($linkedForms);
		}

		// Newly added related elements
		foreach ($joins as $linkedList)
		{
			$key = $linkedList->list_id . '-' . $linkedList->form_id . '-' . $linkedList->element_id;

			if (!in_array($key, $listOrder))
			{
				$listOrder[] = $key;
			}

			if (!in_array($key, $formOrder))
			{
				$formOrder[] = $key;
			}
		}

		$listHeaders = ArrayHelper::getValue($this->value, 'linkedlistheader', array());
		$formHeaders = ArrayHelper::getValue($this->value, 'linkedformheader', array());
		$formLinkTypes = ArrayHelper::getValue($this->value, 'linkedform_linktype', array());
		$listLinkTypes = ArrayHelper::getValue($this->value, 'linkedlist_linktype', array());
		$listLinkTexts = ArrayHelper::getValue($this->value, 'linkedlisttext', array());
		$formLinkTexts = ArrayHelper::getValue($this->value, 'linkedformtext', array());

		$this->linkedlists = array();
		$f = 0;
		$listReturn = array();
		$formReturn = array();
		$listReturn[] = '<h4>' . Text::_('COM_FABRIK_LISTS')
			. '</h4><table class="adminlist linkedLists table table-striped">
					<thead>
					<tr>
						<th></th>
						<th>' . Text::_('COM_FABRIK_LIST') . '</th>
						<th>' . Text::_('COM_FABRIK_LINK_TO_LIST') . '</th>
						<th>' . Text::_('COM_FABRIK_HEADING') . '</th>
						<th>' . Text::_('COM_FABRIK_BUTTON_TEXT') . '</th>
						<th>' . Text::_('COM_FABRIK_POPUP') . '</th>
					</tr>
				</thead>
				<tbody>';
		$formReturn[] = '<h4>' . Text::_('COM_FABRIK_FORMS')
			. '</h4><table class="adminlist linkedForms table table-striped">
					<thead>
					<tr>
						<th></th>
						<th>' . Text::_('COM_FABRIK_LIST') . '</th>
						<th>' . Text::_('COM_FABRIK_LINK_TO_FORM') . '</th>
						<th>' . Text::_('COM_FABRIK_HEADING') . '</th>
						<th>' . Text::_('COM_FABRIK_BUTTON_TEXT') . '</th>
						<th>' . Text::_('COM_FABRIK_POPUP') . '</th>
					</tr>
				</thead>
				<tbody>';

		foreach ($listOrder as $order)
		{
			$linkedList = $this->findJoin($joins, $order);

			if ($linkedList === false)
			{
				continue;
			}

			$key = $linkedList->list_id . '-' . $linkedList->form_id . '-' . $linkedList->element_id;
			$label = str_replace(array("\n", "\r", '<br>', '</br>'), '', $linkedList->listlabel);
			$hover = Text::_('ELEMENT') . ': ' . $linkedList->element_label . ' [' . $linkedList->plugin . ']';

			$listReturn[] = '<tr class="row' . ($f % 2) . '">';
			$listReturn[] = '<td class="handle"></td>';
			$listReturn[] = '<td>' . JHTML::_('tooltip', $hover, $label, 'tooltip.png', $label);

			$yesChecked = ArrayHelper::getValue($linkedLists, $key, 0) != '0' ? 'checked="checked"' : '';
			$noChecked = $yesChecked == '' ? 'checked="checked"' : '';

			$listReturn[] = '<td>';
			$listReturn[] = '<label><input name="' . $this->name . '[linkedlist][' . $key . ']" value="0" ' . $noChecked . ' type="radio" />'
				. Text::_('JNO') . '</label>';
			$listReturn[] = '<label><input name="' . $this->name . '[linkedlist][' . $key . ']" value="' . $key . '" ' . $yesChecked
				. ' type="radio" />' . Text::_('JYES') . '</label>';
			$listReturn[] = '</td>';

			$listReturn[] = '<td>';
			$listReturn[] = '<input type="text" name="' . $this->name . '[linkedlistheader][' . $key . ']" value="' . @$listHeaders[$key] . '" size="16" />';
			$listReturn[] = '</td>';

			$listReturn[] = '<td>';
			$listReturn[] = '<input type="text" name="' . $this->name . '[linkedlisttext][' . $key . ']" value="' . @$listLinkTexts[$key] . '" size="16" />';
			$listReturn[] = '</td>';

			$yesChecked = ArrayHelper::getValue($listLinkTypes, $key, 0) != '0' ? 'checked="checked"' : '';
			$noChecked = $yesChecked == '' ? 'checked="checked"' : '';

			$listReturn[] = '<td>';
			$listReturn[] = '<label><input name="' . $this->name . '[linkedlist_linktype][' . $key . ']" value="0" ' . $noChecked
				. ' type="radio" />' . Text::_('JNO') . '</label>';
			$listReturn[] = '<label><input name="' . $this->name . '[linkedlist_linktype][' . $key . ']" value="' . $key . '" ' . $yesChecked
				. ' type="radio" />' . Text::_('JYES') . '</label>';
			$listReturn[] = '</td>';
			$listReturn[] = '</tr>';
		}

		foreach ($formOrder as $order)
		{
			$linkedList = $this->findJoin($joins, $order);

			if ($linkedList === false)
			{
				continue;
			}

			$key = $linkedList->list_id . '-' . $linkedList->form_id . '-' . $linkedList->element_id;
			$label = str_replace(array("\n", "\r", '<br>', '</br>'), '', $linkedList->listlabel);
			$hover = Text::_('ELEMENT') . ': ' . $linkedList->element_label . ' [' . $linkedList->plugin . ']';

			$yesChecked = ArrayHelper::getValue($linkedForms, $key, 0) != '0' ? 'checked="checked"' : '';
			$noChecked = $yesChecked == '' ? 'checked="checked"' : '';

			$formReturn[] = '<tr class="row' . ($f % 2) . '">';
			$formReturn[] = '<td class="handle"></td>';
			$formReturn[] = '<td>' . JHTML::_('tooltip', $hover, $label, 'tooltip.png', $label);
			$formReturn[] = '<td>';
			$formReturn[] = '<label><input name="' . $this->name . '[linkedform][' . $key . ']" value="0" ' . $noChecked . ' type="radio" />'
				. Text::_('JNO') . '</label>';
			$formReturn[] = '<label><input name="' . $this->name . '[linkedform][' . $key . ']" value="' . $key . '" ' . $yesChecked
				. ' type="radio" />' . Text::_('JYES') . '</label>';
			$formReturn[] = '</td>';

			$formReturn[] = '<td>';
			$formReturn[] = '<input type="text" name="' . $this->name . '[linkedformheader][' . $key . ']" value="' . @$formHeaders[$key] . '" size="16" />';
			$formReturn[] = '</td>';

			$formReturn[] = '<td>';
			$formReturn[] = '<input type="text" name="' . $this->name . '[linkedformtext][' . $key . ']" value="' . @$formLinkTexts[$key] . '" size="16" />';
			$formReturn[] = '</td>';

			$yesChecked = ArrayHelper::getValue($formLinkTypes, $key, 0) != '0' ? 'checked="checked"' : '';
			$noChecked = $yesChecked == '' ? 'checked="checked"' : '';

			$formReturn[] = '<td>';
			$formReturn[] = '<label><input name="' . $this->name . '[linkedform_linktype][' . $key . ']" value="0" ' . $noChecked
				. ' type="radio" />' . Text::_('JNO') . '</label>';
			$formReturn[] = '<label><input name="' . $this->name . '[linkedform_linktype][' . $key . ']" value="' . $key . '" ' . $yesChecked
				. ' type="radio" />' . Text::_('JYES') . '</label>';
			$formReturn[] = '</td>';
			$formReturn[] = '</tr>';

			$f++;
		}

		$listReturn[] = '</tbody></table>';
		$formReturn[] = '</tbody></table>';
		$return = array_merge($listReturn, $formReturn);
		$facetedFormOrder = htmlspecialchars($listParams->get('faceted_form_order'));
		$return[] = '<input name="jform[params][faceted_form_order]" type="hidden" value="' . $facetedFormOrder . '" />';
		$facetedListOrder = htmlspecialchars($listParams->get('faceted_list_order'));
		$return[] = '<input name="jform[params][faceted_list_order]" type="hidden" value="' . $facetedListOrder . '" />';

		return implode("\n", $return);
	}

	/**
	 * Find a join based on composite key
	 *
	 * @param   array   $joins      Joins
	 * @param   string  $searchKey  Key
	 *
	 * @return  mixed   False if not found, join object if found
	 */
	protected function findJoin($joins, $searchKey)
	{
		foreach ($joins as $join)
		{
			$key = $join->list_id . '-' . $join->form_id . '-' . $join->element_id;

			if ($searchKey === $key)
			{
				return $join;
			}
		}

		return false;
	}
}
