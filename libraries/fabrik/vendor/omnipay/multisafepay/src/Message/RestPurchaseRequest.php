<?php
/**
 * MultiSafepay Rest Api Purchase Request.
 */

namespace Omnipay\MultiSafepay\Message;

/**
 * MultiSafepay Rest Api Purchase Request.
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
class RestPurchaseRequest extends RestAbstractRequest
{
    /**
     * Get payment type.
     *
     * Specifies the payment flow for the checkout process.
     *
     * @return string
     */
    public function getType()
    {
        return $this->getParameter('type');
    }

    /**
     * Set payment type.
     *
     * Specifies the payment flow for the checkout process.
     * Possible values are 'redirect', 'direct'
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setType($value)
    {
        return $this->setParameter('type', $value);
    }

    /**
     * Get recurring Payment Id
     *
     * A previously stored identifier referring to a
     * payment method to be charged again.
     *
     * @return int|null
     */
    public function getRecurringId()
    {
        return $this->getParameter('recurring_id');
    }

    /**
     * Set recurring Payment Id
     *
     * A previously stored identifier referring to a
     * payment method to be charged again.
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setRecurringId($value)
    {
        return $this->setParameter('recurring_id', $value);
    }

    /**
     * Get the gateway.
     *
     * The unique gateway id to immediately direct the customer to the payment method.
     * You retrieve these gateways using a gateway request.
     *
     * @return mixed
     */
    public function getGateway()
    {
        return $this->getParameter('gateway');
    }

    /**
     * Set the gateway.
     *
     * The unique gateway id to immediately direct the customer to the payment method.
     * You retrieve these gateways using a gateway request.
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setGateway($value)
    {
        return $this->setParameter('gateway', $value);
    }

    /**
     * Get value of var1.
     *
     * A free variable for custom data to be stored and persisted.
     *
     * @return string|null
     */
    public function getVar1()
    {
        return $this->getParameter('var1');
    }

    /**
     * Set var1.
     *
     * A free optional variable for custom data to be stored and persisted.
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setVar1($value)
    {
        return $this->setParameter('var1', $value);
    }

    /**
     * Get var2.
     *
     * A free variable for custom data to be stored and persisted.
     *
     * @return string|null
     */
    public function getVar2()
    {
        return $this->getParameter('var2');
    }

    /**
     * Set var2.
     *
     * A free variable for custom data to be stored and persisted.
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setVar2($value)
    {
        return $this->setParameter('var2', $value);
    }

    /**
     * Get var3.
     *
     * A free variable for custom data to be stored and persisted.
     *
     * @return string|null
     */
    public function getVar3()
    {
        return $this->getParameter('var3');
    }

    /**
     * Set var3.
     *
     * A free variable for custom data to be stored and persisted.
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setVar3($value)
    {
        return $this->setParameter('var3', $value);
    }

    /**
     * Get manual.
     *
     * If true this forces a credit card transaction to require manual
     * acceptance regardless of the outcome from fraud checks.
     * It is possible that a high risk transaction is still declined.
     *
     * @return boolean|null
     */
    public function getManual()
    {
        return $this->getParameter('manual');
    }

    /**
     * Set manual.
     *
     * If true this forces a credit card transaction to require manual
     * acceptance regardless of the outcome from fraud checks.
     * It is possible that a high risk transaction is still declined.
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setManual($value)
    {
        return $this->setParameter('manual', $value);
    }

    /**
     * Get days active.
     *
     * The number of days the payment link will be active for.
     * When not specified the default will be 30 days.
     *
     * @return int|null
     */
    public function getDaysActive()
    {
        return $this->getParameter('days_active');
    }

    /**
     * Set days active.
     *
     * The number of days the payment link will be active for.
     * When not specified the default will be 30 days.
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setDaysActive($value)
    {
        return $this->setParameter('days_active', $value);
    }

    /**
     * Get close window.
     *
     * Set to true if you will display the MultiSafepay payment
     * page in a new window and want it to close automatically
     * after the payment process has been completed.
     *
     * @return boolean|null
     */
    public function getCloseWindow()
    {
        return $this->getParameter('close_window');
    }

    /**
     * Set close window.
     *
     * Set to true if you will display the MultiSafepay payment
     * page in a new window and want it to close automatically
     * after the payment process has been completed.
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setCloseWindow($value)
    {
        return $this->setParameter('close_window', $value);
    }

    /**
     * Send mail.
     *
     * True if you will send your own bank transfer payment instructions to
     * consumers and do not want MultiSafepay to do this.
     *
     * @return boolean
     */
    public function getSendMail()
    {
        return $this->getParameter('disable_send_mail');
    }

    /**
     * Send mail.
     *
     * True if you will send your own bank transfer payment instructions to
     * consumers and do not want MultiSafepay to do this.
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setSendMail($value)
    {
        return $this->setParameter('disable_send_mail', $value);
    }

    /**
     * Google analytics code.
     *
     * Your Google Analytics Site Id.
     * This will be injected into the payment pages
     * so you can trigger custom events and track payment metrics.
     *
     * @return string|null
     */
    public function getGoogleAnalyticsCode()
    {
        return $this->getParameter('google_analytics');
    }

    /**
     * Google analytics code.
     *
     * Your Google Analytics Site Id.
     * This will be injected into the payment pages
     * so you can trigger custom events and track payment metrics.
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setGoogleAnalyticsCode($value)
    {
        return $this->setParameter('google_analytics', $value);
    }

    /**
     * Get the payment options.
     *
     * @return array
     */
    protected function getPaymentData()
    {
        $data = array(
            'cancel_url'   => $this->getCancelUrl(),
            'close_window' => $this->getCloseWindow(),
            'notify_url'   => $this->getNotifyUrl(),
            'return_url'   => $this->getReturnUrl(),
        );

        return array_filter($data);
    }

    /**
     * Customer information.
     *
     * This function returns all the provided
     * client related parameters.
     *
     * @return array
     */
    public function getCustomerData()
    {
        $data = array(
            'disable_send_mail' => $this->getSendMail(),
            'locale'            => $this->getLocale(),
        );

        if (is_null($this->getCard())) {
            return array_filter($data);
        }

        $cardData = array(
            'address_1'         => $this->getCard()->getAddress1(),
            'address_2'         => $this->getCard()->getAddress2(),
            'city'              => $this->getCard()->getCity(),
            'country'           => $this->getCard()->getCountry(),
            'email'             => $this->getCard()->getEmail(),
            'first_name'        => $this->getCard()->getFirstName(),
            'house_number'      => $this->getCard()->getNumber(),
            'last_name'         => $this->getCard()->getLastName(),
            'phone'             => $this->getCard()->getPhone(),
            'state'             => $this->getCard()->getState(),
            'zip_code'          => $this->getCard()->getPostcode(),
        );

        return array_filter(
            array_merge($data, $cardData)
        );
    }

    /**
     * Get gateway data.
     *
     * @return array
     */
    protected function getGatewayData()
    {
        $data = array(
            'issuer_id' => $this->getIssuer(),
        );

        return array_filter($data);
    }

    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return array
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        parent::getData();

        $this->validate(
            'amount',
            'currency',
            'description',
            'transactionId',
            'type'
        );

        // Direct order.
        if ($this->getType() === 'direct') {
            $this->validate('gateway');
        }

        // When the gateway is set to IDEAL,
        // the issuer parameter is required.
        if (
            $this->getType() == 'direct' &&
            $this->getGateway() == 'IDEAL'
        ) {
            $this->validate('issuer');
        }

        $data = array(
            'amount'           => $this->getAmountInteger(),
            'currency'         => $this->getCurrency(),
            'days_active'      => $this->getDaysActive(),
            'description'      => $this->getDescription(),
            'gateway'          => $this->getGateway(),
            'google_analytics' => $this->getGoogleAnalyticsCode(),
            'items'            => $this->getItems(),
            'manual'           => $this->getManual(),
            'order_id'         => $this->getTransactionId(),
            'recurring_id'     => $this->getRecurringId(),
            'type'             => $this->getType(),
            'var1'             => $this->getVar1(),
            'var2'             => $this->getVar2(),
            'var3'             => $this->getVar3(),
        );

        $paymentData = $this->getPaymentData();

        if (! empty($paymentData)) {
            $data['payment_options'] = $paymentData;
        }

        $customerData = $this->getCustomerData();

        if (! empty($customerData)) {
            $data['customer'] = $customerData;
        }

        $gatewayData = $this->getGatewayData();

        if (! empty($gatewayData)) {
            $data['gateway_info'] = $gatewayData;
        }

        return array_filter($data);
    }

    /**
     * Send the request with specified data
     *
     * @param mixed $data
     * @return RestPurchaseResponse
     */
    public function sendData($data)
    {
        $httpResponse = $this->sendRequest('POST', '/orders', null, $data);

        $this->response = new RestPurchaseResponse(
            $this,
            $httpResponse->json()
        );

        return $this->response;
    }
}
