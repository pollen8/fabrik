<?php
/**
 * Required for Fabrik's J2Store implementation to work
 *
 * @package     Joomla.Plugin
 * @subpackage  System
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
defined('_JEXEC') or die();

class plgSystemFabrikj2store extends JPlugin
{

	/**
	 * Decorates the J2Store product row with the related Fabrik data
	 *
	 * @param object &$product
	 *
	 * @return void
	 */
	public function onJ2StoreAfterGetProduct(&$product)
	{
		if (isset($product->product_source) && strstr($product->product_source, 'com_fabrik'))
		{
			static $sets;

			if (!is_array($sets))
			{
				$sets = array();
			}

			$content = $this->getFabrikItem($product);

			if (isset($content->id))
			{
				$product->source           = $content;
				$product->product_name     = $content->title;
				$product->product_edit_url = $content->editLink;
				$product->product_view_url = $content->viewLink;

				if ($content->published == 1)
				{
					$product->exists = 0;
				}
				else
				{
					$product->exists = 0;
				}

				$sets[$product->product_source][$product->product_source_id] = $content;
			}
			else
			{
				$product->exists = 0;
			}
		}
	}

	/**
	 * Decorate the J2Store product information with its related Fabrik record
	 *
	 * @param $product
	 *
	 * @return object
	 */
	private function getFabrikItem(&$product)
	{
		list($component, $listId) = explode('.', $product->product_source);
		$key = $listId . '.' . $product->product_source_id;
		static $sets;

		if (!is_array($sets))
		{
			$sets = array();
		}
		if (!isset ($sets[$key]))
		{
			JModelLegacy::addIncludePath(COM_FABRIK_FRONTEND . '/models', 'FabrikFEModel');

			/** @var FabrikFEModelList $listModel */
			$listModel = JModelLegacy::getInstance('List', 'FabrikFEModel');
			$listModel->setId($listId);
			$formModel = $listModel->getFormModel();
			$formModel->setRowId($product->product_source_id);
			$row = $formModel->getData();

			$params = $formModel->getParams();
			$index  = array_search('j2store', $params->get('plugins'));

			$w      = new FabrikWorker;
			$plugIn = FabrikWorker::getPluginManager()->loadPlugIn('j2store', 'form');

			// Set params relative to plugin render order
			$plugInParams = $plugIn->setParams($params, $index);

			$context            = new stdClass;
			$context->title     = $w->parseMessageForPlaceHolder($plugInParams->get('j2store_product_name'), $row);
			$context->published = $w->parseMessageForPlaceHolder($plugInParams->get('j2store_enabled'), $row);
			$objectRow          = JArrayHelper::toObject($row);
			$context->viewLink  = $listModel->viewDetailsLink($objectRow);
			$context->editLink  = $listModel->editLink($objectRow);
			$context->id        = $objectRow->__pk_val;
			$sets[$key]         = $context;
		}

		return $sets [$key];
	}

	/**
	 * Method to delete K2Item when removed from k2 Items view
	 *
	 * @param string $context
	 * @param object $row
	 *
	 * @throws Exception
	 *
	 * @return bool
	 */
	public function onFinderAfterDelete($context, $row)
	{
		echo "fabrik onFinderAfterDelete";
		//exit;

		if (strpos($context, 'com_fabrik.item') !== false && !empty($row->id))
		{
			if (!defined('F0F_INCLUDED'))
			{
				include_once JPATH_LIBRARIES . '/f0f/include.php';
			}

			$productModel = F0FModel::getTmpInstance('Products', 'J2StoreModel');
			$itemList     = $this->getProductsBySource('com_fabrik', $row->id);

			foreach ($itemList as $item)
			{
				try
				{
					$productModel->setId($item->j2store_product_id)->delete();
				} catch (Exception $e)
				{
					throw new Exception($e->getMessage());
				}
			}
		}

		return true;
	}

	/**
	 * Method to get List of items based on the product_source and product source id
	 *
	 * @param string $source
	 * @param int    $source_id
	 *
	 * @return  array object
	 */
	private function getProductsBySource($source, $source_id)
	{
		echo "fabrik system plguin getProductsBySource";
		//exit;
		if (empty ($source) || empty ($source_id))
		{
			return array();
		}

		static $source_sets;

		if (!is_array($source_sets))
		{
			$source_sets = array();
		}

		if (!isset($source_sets[$source][$source_id]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)->select('*')->from('#__j2store_products')->where($db->qn('product_source') . ' = ' . $db->q($source))->where($db->qn('product_source_id') . ' = ' . $db->q($source_id));
			$db->setQuery($query);
			$source_sets[$source][$source_id] = $db->loadObjectList();
		}

		return $source_sets[$source][$source_id];
	}
}