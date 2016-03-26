<?php
/**
 * First Data Connect Gateway
 */

namespace Omnipay\FirstData;

use Omnipay\Common\AbstractGateway;

/**
 * First Data Connect Gateway
 *
 * The First Data Global Gateway Connect 2.0 is a simple payment solution for connecting an
 * online store to the First Data Global Gateway.  It is referred to here as the "First Data
 * Connect" gateway, currently at version 2.0.
 *
 * First Data Connect supports both a redirect method of payment and a direct post
 * method of payment. So far only the direct post method of payment is supported by
 * this gateway code.
 *
 * ### Form Hosting
 *
 * First Data Global Gateway Connect 2.0 allows two ways for collecting payment:
 *
 * * A redirect mode which uses the ready-made form pages for the payment process that
 *   First Data provides. With this option, you forward your customers to First Data
 *   for payment. They enter the cardholder data on First Data’s  payment page.
 *   Afterwards, Connect 2.0 redirects the customer back to your website and notifies
 *   your website of the payment result.
 *
 * * You can create your own payment forms and host them on your server. Although your
 *   server hosts the forms, your website sends the cardholder data directly from the
 *   customer to the First Data Global Gateway
 *
 * ### Payment Modes
 *
 * First Data Global Gateway Connect 2.0 supports three different payment modes.
 *
 * * In PayOnly mode, First Data Global Gateway Connect 2.0 collects the minimum
 *   information needed to process a transaction.
 *
 * * In PayPlus mode, First Data Global Gateway Connect 2.0 collects the same payment
 *   information as in PayOnly mode plus a full set of billing information.
 *
 * * In FullPay mode, First Data Global Gateway Connect 2.0 collects the same payment
 *   and billing information collected in PayPlus mode plus shipping information.
 *
 * ### Test Accounts
 *
 * You can apply for a test account at this URL:
 *
 * http://www.firstdata.com/product_solutions/ecommerce/global_gateway/index.htm
 *
 * There are some issues with obtaining shared secrets for testing Connect 2.0.
 *
 * ### Example
 *
 * <code>
 * // Create a gateway for the First Data Connect Gateway
 * // (routes to GatewayFactory::create)
 * $gateway = Omnipay::create('FirstData_Connect');
 *
 * // Initialise the gateway
 * $gateway->initialize(array(
 *     'storeId'        => '12341234',
 *     'sharedSecret'   => 'IcantTELLyouITSaSECRET',
 *     'testMode'       => true, // Or false when you are ready for live transactions
 * ));
 *
 * // Do a purchase transaction on the gateway
 * $transaction = $gateway->purchase(array(
 *     'description'              => 'Your order for widgets',
 *     'amount'                   => '10.00',
 *     'transactionId'            => 12345,
 * ));
 * $response = $transaction->send();
 * if ($response->isSuccessful()) {
 *     echo "Purchase transaction was successful!\n";
 *     $sale_id = $response->getTransactionReference();
 *     echo "Transaction reference = " . $sale_id . "\n";
 * }
 * </code>
 *
 * @link https://www.firstdata.com/downloads/pdf/FDGG_Connect_2.0_Integration_Manual_v2.0.pdf
 */
class ConnectGateway extends AbstractGateway
{
    public function getName()
    {
        return 'First Data Connect';
    }

    public function getDefaultParameters()
    {
        return array(
            'storeId'      => '',
            'sharedSecret' => '',
            'testMode'     => false,
        );
    }

    /**
     * Set Store ID
     *
     * Calls to the Connect Gateway API are secured with a store ID and
     * shared secret.
     *
     * @return ConnectGateway provides a fluent interface
     */
    public function setStoreId($value)
    {
        return $this->setParameter('storeId', $value);
    }

    /**
     * Get Store ID
     *
     * Calls to the Connect Gateway API are secured with a store ID and
     * shared secret.
     *
     * @return string
     */
    public function getStoreId()
    {
        return $this->getParameter('storeId');
    }

    /**
     * Set Shared Secret
     *
     * Calls to the Connect Gateway API are secured with a store ID and
     * shared secret.
     *
     * @return ConnectGateway provides a fluent interface
     */
    public function setSharedSecret($value)
    {
        return $this->setParameter('sharedSecret', $value);
    }

    /**
     * Get Shared Secret
     *
     * Calls to the Connect Gateway API are secured with a store ID and
     * shared secret.
     *
     * @return string
     */
    public function getSharedSecret()
    {
        return $this->getParameter('sharedSecret');
    }

    /**
     * Create a purchase request.
     *
     * @param array $parameters
     * @return \Omnipay\FirstData\Message\PurchaseRequest
     */
    public function purchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\FirstData\Message\PurchaseRequest', $parameters);
    }

    /**
     * Create a complete purchase request.
     *
     * @param array $parameters
     * @return \Omnipay\FirstData\Message\CompletePurchaseRequest
     */
    public function completePurchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\FirstData\Message\CompletePurchaseRequest', $parameters);
    }
}
