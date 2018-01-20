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
			'billingAddress': false,
			'couponElement': '',
			'renderOrder': ''
		},


		/**
		 * Initialize
		 * @param {object} options
		 */
		initialize: function (options) {
			var self = this;
			this.options = jQuery.extend(this.options, options);
			this.form = Fabrik.getBlock('form_' + this.options.formid);
			Fabrik.FabrikStripeForm = null;
			Fabrik.FabrikStripeFormSubmitting = false;

			if (this.options.couponElement !== '')
			{
                this.couponElement = self.form.formElements.get(this.options.couponElement);
                var elEvnt = this.couponElement.getBlurEvent();

				self.form.dispatchEvent(
					'',
					this.options.couponElement,
					elEvnt,
					function (e) {
						self.getCoupon(e);
					}
				);
			}

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
							'value': JSON.stringify(opts),
							'type' : 'hidden'
						}));
						Fabrik.FabrikStripeForm.mockSubmit();
					},
					closed : function () {
						Fabrik.FabrikStripeFormSubmitting = true;
					}
				});

				Fabrik.addEvent('fabrik.form.submit.start', function (form, event, btn) {
					if (!this.options.ccOnFree && this.options.amount == 0)
					{
						return;
					}

					if (
						typeof Fabrik.FabrikStripeForm === 'undefined' ||
						Fabrik.FabrikStripeFormSubmitting !== true ||
						jQuery('input[name=stripe_token_id]').length === 0
					) {
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
			else if (this.options.updateCheckout)
			{
				var changeBtn = this.form.form.getElement('.fabrikStripeChange');
				if (typeOf(changeBtn) !== 'null') {
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
								'value': JSON.stringify(opts),
								'type' : 'hidden'
							}));
							jQuery('.fabrikStripeLast4').text(Joomla.JText._('PLG_FORM_STRIPE_CUSTOMERS_UPDATE_CC_UPDATED'));
						}
					});
					changeBtn.addEvent('click', function (e) {
						e.preventDefault();
						Fabrik.FabrikStripeForm = this.form;
						this.handler.open({
							name           : this.options.name,
							description    : this.options.item,
							zipCode        : this.options.zipCode,
							allowRememberMe: this.options.allowRememberMe,
							email          : this.options.email,
							panelLabel     : this.options.panelLabel,
							billingAddress : this.options.billingAddress
						});
					}.bind(this));
				}
			}
		},

		getCoupon: function (e) {
            Fabrik.loader.start('form_' + this.options.formid, Joomla.JText._('PLG_FORM_AUTOFILL_SEARCHING'));

            var v = this.couponElement.getValue(),
                formid = this.options.formid,
                self = this;

            jQuery.ajax({
                url     : 'index.php',
                method  : 'post',
                dataType: 'json',
                'data'  : {
                    'option'               : 'com_fabrik',
                    'format'               : 'raw',
                    'task'                 : 'plugin.pluginAjax',
                    'plugin'               : 'stripe',
                    'method'               : 'ajax_getCoupon',
					'amount'               : this.options.origAmount,
                    'g'                    : 'form',
                    'v'                    : v,
                    'formid'               : formid,
					'renderOrder'          : this.options.renderOrder
                }

            }).always(function () {
                Fabrik.loader.stop('form_' + self.options.formid);
            })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    window.alert(textStatus);
                })
                .done(function (json) {
                    self.updateForm(json);
                });

        },

		updateForm: function (json) {
			this.options.amount = json.stripe_amount;
			jQuery('.fabrikStripePrice').html(json.display_amount);
			jQuery('.fabrikStripeCouponText').html(json.msg);
		}

	});

	return Stripe;
});