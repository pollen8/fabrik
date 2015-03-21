<?php
/**
 * Get a list of available Ominpay gateways
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.payments
 * @copyright   Copyright (C) 2005-2015 fabrikar.com - All rights reserved.
 * @license     GNU/GPL http://www.gnu.org/copyleft/gpl.html
 *
 * NOTE - as we can only have one addpath file specified for the params group, this file has to be located
 * in the main ./administrator/components/com_fabrik/models/fields folder.  So until we work out how to do the install
 * XML magic to relocate this file on install, we have simply made a copy of it in the admin location in SVN.
 * If you edit the copy in the plugin folder, please be sure to also modify the copy in the admin folder.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

require JPATH_SITE . '/plugins/fabrik_form/payments/vendor/autoload.php';
use Omnipay\Omnipay;

JFormHelper::loadFieldClass('list');

/**
 * List of possible gateways
 *
 * @package     Joomla.Plugin
 * @subpackage  Fabrik.form.payments
 * @since       3.0
 */

class JFormFieldOmnipayGateways extends JFormFieldList
{
	/**
	 * Element name
	 *
	 * @var	string
	 */
	protected $name = 'Omnipaygateways';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$options = array();
		$gateways = Omnipay::find();

		$accepted = array('PayPal_Express', 'Stripe', 'AuthorizeNet_AIM', 'AuthorizeNet_SIM', 'Coinbase');

		$fieldSets = array('PayPal_Express' => 'credentials_paypal',
			'Stripe' => 'credentials_stripe',
			'AuthorizeNet_AIM' => 'credentials_authorize',
			'AuthorizeNet_SIM' => 'credentials_authorize',
			'Coinbase' => 'credentials_coinbase');

		foreach ($gateways as $value)
		{
			$disabled = false;
			$label = 'PLG_FORM_PAYMENTS_GATEWAY_' . strtoupper($value);

			if (in_array($value, $accepted))
			{
				$opts = array(
					'attr'=> 'foo',
					'option.attr' => 'show'
				);

				// Create a new option object based on the <option /> element.
				$opt = JHtml::_(
					'select.option', $value,
					JText::_($label), $opts
				);

				$opt->show = 'data-show="' . $fieldSets[$value] . '"';
				$options[] = $opt;
			}
		}

		reset($options);

		return $options;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 */

	protected function getInput()
	{
		$script = "window.addEvent('domready', function() {

			var sel = document.id('" . $this->id . "');

			var toggleFieldSets = function () {
				var fs = sel.options[sel.selectedIndex].getProperty('data-show');
				var parent = sel.getParent('.pluginOpts');

				var show = parent.getElement('a[href*=\"#tab-' + fs + '\"]');
				var hide = parent.getElements('a[href*=\"#tab-credentials\"]');

				for (var i = 0; i < hide.length; i ++) {
					hide[i].hide();
				}

				show.show();
			};

			toggleFieldSets();
			sel.addEvent('change', function (e) {
				toggleFieldSets();
			});
		})";
		FabrikHelperHTML::addScriptDeclaration($script);

		$html = array();
		$attr = '';

		// Initialize some field attributes.
		$attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';

		// Get the field options.
		$options = (array) $this->getOptions();

		$attribs = array(
			'id' => $this->id,
			'list.select' => $this->value,
			'option.attr' => 'show'
		);

		$html[] = JHtml::_('select.genericlist', $options, $this->name, $attribs);
		//$html[] = JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);

		return implode($html);
	}


}
