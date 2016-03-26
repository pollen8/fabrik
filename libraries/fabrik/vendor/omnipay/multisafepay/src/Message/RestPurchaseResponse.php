<?php
/**
 * MultiSafepay Rest Api Purchase Response.
 */
namespace Omnipay\MultiSafepay\Message;

use Omnipay\Common\Message\RedirectResponseInterface;

/**
 * MultiSafepay Rest Api Purchase Response.
 *
 * The payment request allows the merchant to charge a customer.
 *
 * ### Redirect vs Direct Payments
 *
 * A redirect payment is the standard payment process.
 * During a redirect payment the consumer is redirected to
 * MultiSafepay's secure Hosted Payment Pages to enter their
 * payment details and is suitable for most scenarios. If you
 * prefer greater control over your checkout process you should
 * use direct payments. During a direct payment the consumer
 * does not need to visit MultiSafepay Hosted Payment Pages.
 * Instead your website collects the requirement payment details
 * and the consumer is redirect immediately to their chosen payment
 * provider.
 *
 * ### Create Card
 *
 * <code>
 *    // Create a credit card object
 *    $card = new CreditCard(array(
 *       'firstName'     => 'Example',
 *       'lastName'      => 'Customer',
 *       'number'        => '4222222222222222',
 *       'expiryMonth'   => '01',
 *       'expiryYear'    => '2020',
 *       'cvv'           => '123',
 *       'email'         => 'customer@example.com',
 *       'address1'      => '1 Scrubby Creek Road',
 *       'country'       => 'AU',
 *       'city'          => 'Scrubby Creek',
 *       'postalcode'    => '4999',
 *       'state'         => 'QLD',
 *    ));
 * </code>
 *
 * ### Initialize a "redirect" payment.
 *
 * The customer will be redirected to the MultiSafepay website
 * where they need to enter their payment details.
 *
 * <code>
 *    $request = $gateway->purchase();
 *
 *    $request->setAmount('20.00');
 *    $request->setTransactionId('TEST-TRANSACTION');
 *    $request->setDescription('Test transaction');
 *
 *    $request->setCurrency('EUR');
 *    $request->setType('redirect');
 *
 *    $response = $request->send();
 *    var_dump($response->getData());
 * </code>
 *
 * ### Initialize a "direct" payment.
 *
 * The merchant website need to collect the payment details, so
 * the user can stay at the merchant website.
 *
 * <code>
 *    $request = $gateway->purchase();
 *
 *    $request->setAmount('20.00');
 *    $request->setTransactionId('TEST-TRANSACTION');
 *    $request->setDescription('Test transaction');
 *
 *    $request->setCurrency('EUR');
 *    $request->setType('direct');
 *    $request->setCard($card);
 *
 *    $request->setGateway('IDEAL');
 *    $request->setIssuer('ISSUER-ID'); // This ID can be found, with the RestFetchIssuersRequest.
 *
 *    $response = $request->send();
 *    var_dump($response->getData());
 * </code>
 */
class RestPurchaseResponse extends RestAbstractResponse implements RedirectResponseInterface
{
    /**
     * {@inheritdoc}
     */
    public function isRedirect()
    {
        return isset($this->data['data']['payment_url']);
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectUrl()
    {
        if (! $this->isRedirect()) {
            return null;
        }

        return $this->data['data']['payment_url'];
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectMethod()
    {
        return 'GET';
    }

    /**
     * {@inheritdoc}
     */
    public function getRedirectData()
    {
        return null;
    }
}
