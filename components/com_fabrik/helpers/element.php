<?php
/**
 * Element Helper class
 *
 * @package     Joomla
 * @subpackage  Fabrik.helpers
 * @copyright   Copyright (C) 2005 Fabrik. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * Element Helper class
 *
 * @package     Joomla
 * @subpackage  Fabrik.helpers
 * @since       3.0.6
 */

class FabrikHelperElement
{

	/**
	 * For processing repeat elements we need to make its
	 * ID element during the form process
	 *
	 * @param   plgFabrik_Element  $baseElement  repeat element (e.g. db join rendered as checkbox)
	 *
	 * @return  plgFabrik_ElementInternalid
	 */

	public function makeIdElement($baseElement)
	{

		$pluginManager = FabrikWorker::getPluginManager();
		$groupModel = $baseElement->getGroupModel();
		$elementModel = $pluginManager->getPlugIn('internalid', 'element');
		$elementModel->getElement()->name = 'id';

		$elementModel->getParams()->set('repeat', $baseElement->isJoin());

		$elementModel->getElement()->group_id = $groupModel->getId();
		$elementModel->setGroupModel($baseElement->getGroupModel());

		// @TODO wrong when element in repeat group
		$oJoin = $groupModel->getJoinModel()->getJoin();
		$elementModel->_aFullNames['id1_1__1_'] = $oJoin->table_join . '___id';
		return $elementModel;
	}

	/**
	 * For processing repeat elements we need to make its
	 * parent id element during the form process
	 *
	 * @param   plgFabrik_Element  $baseElement  repeat element (e.g. db join rendered as checkbox)
	 *
	 * @return  plgFabrik_ElementField
	 */

	public function makeParentElement($baseElement)
	{
		$pluginManager = FabrikWorker::getPluginManager();
		$groupModel = $baseElement->getGroupModel();
		$elementModel = $pluginManager->getPlugIn('field', 'element');
		$elementModel->getElement()->name = 'parent_id';
		$elementModel->getParams()->set('repeat', $baseElement->isJoin());
		$elementModel->getElement()->group_id = $groupModel->getId();
		$elementModel->setGroupModel($baseElement->getGroupModel());

		// @TODO wrong when element in repeat group
		$oJoin = $groupModel->getJoinModel()->getJoin();
		$elementModel->_aFullNames['parent_id1_1__1_'] = $oJoin->table_join . '___parent_id';
		return $elementModel;
	}
}
