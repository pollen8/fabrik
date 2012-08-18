<?php
/**
 * @package     Joomla
 * @subpackage  Fabrik
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @since       1.6
 */

// No direct access.
defined('_JEXEC') or die;

require_once 'fabmodeladmin.php';

/**
 * Fabrik Admin Plugin Model
 * Used for loading via ajax form plugins
 *
 * @package  Fabrik
 * @since    3.0.6
 */

class FabrikModelPlugin extends JModel
{

	public function render()
	{

		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');
		$plugin = $pluginManager->getPlugIn($this->getState('plugin'), $this->getState('type'));
		$feModel = $this->getPluginModel();
		$plugin->getJForm()->model = $feModel;

		$data = $this->getData();
		JRequest::setVar('view', $this->getState('type'));
		$str = $plugin->onRenderAdminSettings($data, $this->getState('c'));
		JRequest::setVar('view', 'plugin');

		return $str;
	}

	protected function getData()
	{
		$type = $this->getState('type');
		if ($type === 'validationrule')
		{
			$item = FabTable::getInstance('Element', 'FabrikTable');
			$item->load($this->getState('id'));
		}
		else
		{
			$feModel = $this->getPluginModel();
			$item = $feModel->getTable();
		}
		$data = (array) json_decode($item->params);
		$data['plugin'] = $this->getState('plugin');

		$state = JArrayHelper::getValue($data, 'plugin_state');
		$data['params']['plugin_state'] = $state[0];
		$data['params']['plugins'] = $this->getState('plugin');
		$data['validationrule']['plugin'] = $this->getState('plugin');
		return $data;
	}

	protected function getPluginModel()
	{
		$feModel = null;
		$type = $this->getState('type');
		/* if ($type === 'validationrule')
		{
		    $type = 'element';
		} */
		if ($type === 'validationrule')
		{
			/* $pluginManager = JModel::getInstance('Pluginmanager', 'FabrikFEModel');

			// Require the abstract plugin class
			require_once COM_FABRIK_FRONTEND . '/models/validation_rule.php';
			require_once COM_FABRIK_FRONTEND . '/models/element.php';
			$feModel = $pluginManager->getPlugIn($this->getState('plugin'), 'element');
			echo "<pre>plg";
			print_r($feModel);
			exit; */
		}
		else
		{
			// Set the parent model e.g. form/list
			$feModel = JModel::getInstance($type, 'FabrikFEModel');
			$feModel->setId($this->getState('id'));
		}

		// Set the parent model e.g. form/list
		/* $feModel = JModel::getInstance($type, 'FabrikFEModel');
		$feModel->setId($this->getState('id')); */
		return $feModel;
	}

	public function top()
	{
		$data = $this->getData();
		$c = $this->getState('c') + 1;
		$str = array();
		$str[] = '<div class="pane-slider content pane-down">';
		$str[] = '<fieldset class="adminform pluginContanier" id="formAction_' . $c . '"><ul>';
		$formName = 'com_fabrik.' . $this->getState('type') . '-plugin';
		$topForm = new JForm($formName, array('control' => 'jform'));
		$topForm->repeatCounter = $c;
		$xmlFile = JPATH_SITE . '/administrator/components/com_fabrik/models/forms/' . $this->getState('type') . '-plugin.xml';

		// Add the plugin specific fields to the form.
		$topForm->loadFile($xmlFile, false);

		$topForm->bind($data);

		// Filer the forms fieldsets for those starting with the correct $serachName prefix
		foreach ($topForm->getFieldsets() as $fieldset)
		{
			if ($fieldset->label != '')
			{
				$str[] = '<legend>' . $fieldset->label . '</legend>';
			}

			foreach ($topForm->getFieldset($fieldset->name) as $field)
			{
				$str[] = '<li>' . $field->label . $field->input . '</li>';
			}
		}
		$str[] = '</ul>';
		$str[] = '<div class="pluginOpts" style="clear:left"></div>';
		$str[] = '<a href="#" class="delete removeButton">' . JText::_('COM_FABRIK_DELETE') . '</a>';
		$str[] = '</fieldset>';
		$str[] = '</div>';
		return implode("\n", $str);
	}
}
