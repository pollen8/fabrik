<?php
/**
 * Renders planyo api events
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.planyo
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 *
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');
require_once JPATH_ADMINISTRATOR . '/components/com_fabrik/helpers/element.php';

/**
 * Renders planyo api events
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.planyo
 * @since       3.4
 */
class JFormFieldPlanyoEvents extends JFormFieldList
{
	/**
	 * Element name
	 *
	 * @var    string
	 */
	protected $name = 'Planyoevents';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 */
	protected function getOptions()
	{
		return array(
			JHtml::_('select.option', '', ''),
			JHtml::_('select.option', 'add_agent', JText::_('PLG_PLANYO_ADD_AGENT')),
			JHtml::_('select.option', 'add_user', JText::_('PLG_PLANYO_ADD_USER')),
			JHtml::_('select.option', 'add_custom_property_definition', JText::_('PLG_PLANYO_ADD_CUSTOM_PROPERTY_DEFINITION')),
			JHtml::_('select.option', 'add_notification_callback', JText::_('PLG_PLANYO_ADD_NOTIFICATION_CALLBACK')),
			JHtml::_('select.option', 'add_reservation_payment', JText::_('PLG_PLANYO_ADD_RESERVATION_PAYMENT')),
			JHtml::_('select.option', 'add_resource', JText::_('PLG_PLANYO_ADD_RESOURCE')),
			JHtml::_('select.option', 'add_resource_image', JText::_('PLG_PLANYO_ADD_RESOURCE_IMAGE')),
			JHtml::_('select.option', 'add_site', JText::_('PLG_PLANYO_ADD_SITE')),
			JHtml::_('select.option', 'add_vacation', JText::_('PLG_PLANYO_ADD_VACATION')),
			JHtml::_('select.option', 'apply_coupon', JText::_('PLG_PLANYO_APPLY_COUPON')),
			JHtml::_('select.option', 'can_make_reservation', JText::_('PLG_PLANYO_MAKE_RESERVATION')),
			JHtml::_('select.option', 'do_reservation_action', JText::_('PLG_PLANYO_DO_RESERVATION_ACTION')),
			JHtml::_('select.option', 'generate_coupon', JText::_('PLG_PLANYO_GENERATE_COUPON')),
			JHtml::_('select.option', 'get_custom_property', JText::_('PLG_PLANYO_GET_CUSTOM_PROPERTY')),
			JHtml::_('select.option', 'get_custom_property_definition', JText::_('PLG_PLANYO_GET_PROPERTY_DEFINITION')),
			JHtml::_('select.option', 'get_event_times', JText::_('PLG_PLANYO_GET_EVENT_TIMES')),
			JHtml::_('select.option', 'get_form_items', JText::_('PLG_PLANYO_GET_FORM_ITEMS')),
			JHtml::_('select.option', 'get_invoice_items', JText::_('PLG_PLANYO_GET_INVOICE_ITEMS')),
			JHtml::_('select.option', 'get_rental_price', JText::_('PLG_PLANYO_GET_RENTAL_PRICE')),
			JHtml::_('select.option', 'get_reservation_actions', JText::_('PLG_PLANYO_GET_RESERVATION_ACTIONS')),
			JHtml::_('select.option', 'get_reservation_data', JText::_('PLG_PLANYO_GET_RESERVATION_DATA')),
			JHtml::_('select.option', 'get_reservation_payment_amount', JText::_('PLG_PLANYO_GET_RESERVATION_PAYMENT_AMOUNT')),
			JHtml::_('select.option', 'get_reservation_products', JText::_('PLG_PLANYO_GET_RESERVATION_PRODUCTS')),
			JHtml::_('select.option', 'get_resource_info', JText::_('PLG_PLANYO_GET_RESOURCE_INFO')),
			JHtml::_('select.option', 'get_resource_pricing', JText::_('PLG_PLANYO_GET_RESOURCE_PRICING')),
			JHtml::_('select.option', 'get_resource_seasons', JText::_('PLG_PLANYO_GET_RESOURCE_SEASONS')),
			JHtml::_('select.option', 'get_resource_usage_for_month', JText::_('PLG_PLANYO_GET_RESOURCE_USAGE_FOR_MONTH')),
			JHtml::_('select.option', 'get_site_info', JText::_('PLG_PLANYO_GET_SITE_INFO')),
			JHtml::_('select.option', 'get_user_data', JText::_('PLG_PLANYO_GET_USER_DATA')),
			JHtml::_('select.option', 'get_weekly_schedule', JText::_('PLG_PLANYO_GET_WEEKLY_SCHEDULE')),
			JHtml::_('select.option', 'is_resource_available', JText::_('PLG_PLANYO_IS_RESOURCE_AVAILABLE')),
			JHtml::_('select.option', 'list_additional_products', JText::_('PLG_PLANYO_LIST_ADDITIONAL_PRODUCTS')),
			JHtml::_('select.option', 'list_coupons', JText::_('PLG_PLANYO_LIST_COUPONS')),
			JHtml::_('select.option', 'list_reservation_payments', JText::_('PLG_PLANYO_LIST_RESERVATION_PAYMENTS')),
			JHtml::_('select.option', 'list_reservations', JText::_('PLG_PLANYO_LIST_RESERVATIONS')),
			JHtml::_('select.option', 'list_resources', JText::_('PLG_PLANYO_LIST_RESOURCES')),
			JHtml::_('select.option', 'list_sites', JText::_('PLG_PLANYO_LIST_SITES')),
			JHtml::_('select.option', 'list_users', JText::_('PLG_PLANYO_LIST_USERS')),
			JHtml::_('select.option', 'list_vacations', JText::_('PLG_PLANYO_LIST_VACATIONS')),
			JHtml::_('select.option', 'list_vouchers', JText::_('PLG_PLANYO_LIST_VOUCHERS')),
			JHtml::_('select.option', 'make_reservation', JText::_('PLG_PLANYO_MAKE_RESERVATION')),
			JHtml::_('select.option', 'modify_reservation', JText::_('PLG_PLANYO_MODIFY_RESERVATION')),
			JHtml::_('select.option', 'modify_resource', JText::_('PLG_PLANYO_MODIFY_RESOURCE')),
			JHtml::_('select.option', 'modify_site', JText::_('PLG_PLANYO_MODIFY_SITE')),
			JHtml::_('select.option', 'modify_user', JText::_('PLG_PLANYO_MODIFY_USER')),
			JHtml::_('select.option', 'process_template', JText::_('PLG_PLANYO_PROCESS_TEMPLATE')),
			JHtml::_('select.option', 'remove_custom_property_definition', JText::_('PLG_PLANYO_REMOVE_CUSTOM_PROPERTY_DEFINITION')),
			JHtml::_('select.option', 'remove_notification_callback', JText::_('PLG_PLANYO_REMOVE_NOTIFICATION_CALLBACK')),
			JHtml::_('select.option', 'remove_resource', JText::_('PLG_PLANYO_REMOVE_RESOURCE')),
			JHtml::_('select.option', 'remove_vacation', JText::_('PLG_PLANYO_REMOVE_VACATION')),
			JHtml::_('select.option', 'reservation_search', JText::_('PLG_PLANYO_RESERVATION_SEARCH')),
			JHtml::_('select.option', 'resource_search', JText::_('PLG_PLANYO_RESOURCE_SEARCH')),
			JHtml::_('select.option', 'search_reservations_by_form_item', JText::_('PLG_PLANYO_SEARCH_RESERVATIONS_BY_FORM_ITEM')),
			JHtml::_('select.option', 'set_custom_properties', JText::_('PLG_PLANYO_SET_CUSTOM_PROPERTIES')),
			JHtml::_('select.option', 'set_custom_property', JText::_('PLG_PLANYO_SET_CUSTOM_PROPERTY')),
			JHtml::_('select.option', 'set_event_times', JText::_('PLG_PLANYO_SET_EVENT_TIMES')),
			JHtml::_('select.option', 'set_payment_gateway', JText::_('PLG_PLANYO_SET_PAYMENT_GATEWAY')),
			JHtml::_('select.option', 'set_reservation_color', JText::_('PLG_PLANYO_SET_RESERVATION_COLOR')),
			JHtml::_('select.option', 'set_resource_availability', JText::_('PLG_PLANYO_SET_RESOURCE_AVAILABILITY')),
			JHtml::_('select.option', 'set_translation', JText::_('PLG_PLANYO_SET_TRANSLATION')),
			JHtml::_('select.option', 'set_weekly_schedule', JText::_('PLG_PLANYO_SET_WEEKLY_SCHEDULE'))
		);
	}
}
