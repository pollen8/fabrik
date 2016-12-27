/**
 * Form Autofill
 *
 * @copyright: Copyright (C) 2005-2016  Media A-Team, Inc. - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */
define(['jquery', 'fab/fabrik'], function (jQuery, Fabrik) {
	'use strict';
	var Stripe = new Class({

		options: {
			'publicKey' : '',
			'item' : '',
			'zipCode': true,
			'allowRememberMe': false,
			'email': '',
			'name': '',
			'panelLabel': '',
			'useCheckout': true,
			'billingAddress': false
		},


		/**
		 * Initialize
		 * @param {object} options
		 */
		initialize: function (options) {
			var self = this;
			this.options = jQuery.extend(this.options, options);

			if (this.options.useCheckout) {
				this.handler = StripeCheckout.configure({
					key   : this.options.publicKey,
					image : 'https://stripe.com/img/documentation/checkout/marketplace.png',
					locale: 'auto',
					token : function (token, opts) {
						Fabrik.FabrikStripeForm.form.adopt(new Element('input', {
							'name' : 'stripe_token_id',
							'value': token.id,
							'type' : 'hidden'
						}));
						Fabrik.FabrikStripeForm.form.adopt(new Element('input', {
							'name' : 'stripe_token_email',
							'value': token.email,
							'type' : 'hidden'
						}));
						Fabrik.FabrikStripeForm.form.adopt(new Element('input', {
							'name' : 'stripe_token_opts',
							'value': JSON.encode(opts),
							'type' : 'hidden'
						}));
						Fabrik.FabrikStripeForm.mockSubmit();
					}
				});

				Fabrik.addEvent('fabrik.form.submit.start', function (form, event, btn) {
					if (typeof Fabrik.FabrikStripeForm === 'undefined') {
						Fabrik.FabrikStripeForm = form;
						this.handler.open({
							name           : this.options.name,
							description    : this.options.item,
							amount         : this.options.amount,
							zipCode        : this.options.zipCode,
							allowRememberMe: this.options.allowRememberMe,
							email          : this.options.email,
							panelLabel     : this.options.panelLabel,
							billingAddress : this.options.billingAddress
						});
						event.preventDefault();
						form.result = false;
					}
				}.bind(this));

				window.addEventListener('popstate', function () {
					this.handler.close();
				});
			}
		}

	});

	return Stripe;
});