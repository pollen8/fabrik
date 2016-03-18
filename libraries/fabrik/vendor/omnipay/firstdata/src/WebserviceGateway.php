<?php
/**
 * First Data Webservice Gateway
 */

namespace Omnipay\FirstData;

use Omnipay\Common\AbstractGateway;

/**
 * First Data Webservice Gateway
 *
 * The Webservice Gateway was originally called the LinkPoint Gateway but since First Data's
 * acquisition of LinkPoint it is now known as the First Data Global Gateway Web Service API.
 * As of this writing the Global Gateway Web Service API version 9.0 is supported. It is
 * referred to here as the "First Data Webservice" gateway.
 *
 * ### Quirks
 *
 * #### WSDL
 *
 * There is currently a roadblock with the implementation of this gateway in that the
 * WSDL is not recognised as valid by PHP's SOAP client.
 *
 * See this bug: https://bugs.php.net/bug.php?id=43868
 *
 * PHP Fatal error:  SOAP-ERROR: Parsing Schema: element
 * 'http://secure.linkpt.net/fdggwsapi/schemas_us/fdggwsapi:FDGGWSApiOrderRequest'
 * already defined in omnipay-firstdata/src/Soap/BaseSoapClient.php on line 121
 *
 * In this gateway code we have ignored the SOAP thing completely and just constructed XML
 * messages to send to the gateway POST URL directly, using templates and lots of string
 * substitution.
 *
 * https://ws.firstdataglobalgateway.com:443/fdggwsapi/services
 *
 * Neither the native PHP SOAP client nor the BeSimple SOAP client work at all dealing
 * with the WSDL.
 *
 * In addition, sending a properly formed SOAP message with an XML header (of
 * the type <?xml ... ?>) throws an error message.  The XML header must be
 * omitted.
 *
 * To be honest I have better things to spend my time on than dealing with such
 * nonsense.  If people can't drag themselves out of the 19th century and use
 * REST APIs instead of SOAP then they can at least have the good grace and
 * common courtesy to get their XML handling and formatting correct.
 *
 * #### Transaction ID
 *
 * A user supplied transaction ID (transactionId parameter) must be supplied for each
 * purchase() or authorize() request.  This is known as "OrderId" in the Webservice Gateway.
 *
 * The First Data Webservice Gateway Web Service API only accepts ASCII characters. The Order
 * ID cannot contain the following characters: &, %, /, or exceed 100 characters in length.
 * The Order ID will be restricted in such a way so that it can only accepts alpha numeric
 * (a-z, A-Z, 0-9) and some special characters for merchants convenience.
 *
 * #### Voids and Refunds
 *
 * Voids and Refunds require both the transactionId and the TDATE parameter returned
 * from the purchase (or capture) call. To make things cleaner for calling applicatons,
 * the transactionReference returned by getTransactionReference() after the purhcase
 * or capture call is the transactionId and the TDATE concatenated together and
 * separated by '::'.
 *
 * Refunds require an amount parameter.  The amount parameter is ignored by the void
 * call.
 *
 * ### Authentication
 *
 * Within the SSL connection (see below), authentication is done via HTTP Basic Auth.
 *
 * First Data should provide you, as part of the connection package, a username and a
 * password.  The username will be of the form WS*******._.1 and the password will be
 * a string containing letters and numbers.  Provide these as the userName and password
 * parameters when initializing the gateway.
 *
 * ### SSL Connection Security
 *
 * The Web Service API requires an SSL connection with client and server exchanging
 * certificates to guarantee this level of security. The client and server certificates
 * each uniquely identify the party.
 *
 * First Data should provide you, as part of the connection package, at least the
 * following files:
 *
 * * WS____.pem (certificate file)
 * * WS____.key (key file)
 * * WS____.key.pw.txt (text file containing the password for the key)
 *
 * ... where WS___ is your username also provided by First Data (see above).
 *
 * You need to store the certificate file and the key file on disk somewhere and
 * pass the following parameters when initializing the gateway:
 *
 * * sslCertificate -- on disk location of the .pem certificate file.
 * * sslKey -- on disk location of the .key file.
 * * sslKeyPassword -- the password for the key (not the file name)
 *
 * In case First Data provide you with a .p12 (PKCS-12) file for the certificate
 * instead of a PEM file, you will need to convert the .p12 file to a .pem file
 * using this command:
 *
 * ```
 * openssl pkcs12 -in WS____.p12 -out WS____.1.pem -clcerts -nokeys
 * ```
 *
 * ### Test Accounts
 *
 * To obtain a test account, use this form:
 * http://www.firstdata.com/gg/apply_test_account.htm
 *
 * ### Example
 *
 * This is an example of a purchase request.
 *
 * <code>
 * // Create a gateway for the First Data Webservice Gateway
 * // (routes to GatewayFactory::create)
 * $gateway = Omnipay::create('FirstData_Webservice');
 *
 * // Initialise the gateway
 * $gateway->initialize(array(
 *     'sslCertificate'    => 'WS9999999._.1.pem',
 *     'sslKey'            => 'WS9999999._.1.key',
 *     'sslKeyPassword'    => 'sslKEYpassWORD',
 *     'userName'          => 'WS9999999._.1',
 *     'password'          => 'passWORD',
 *     'testMode'          => true,
 * ));
 *
 * // Create a credit card object
 * $card = new CreditCard(array(
 *     'firstName'            => 'Example',
 *     'lastName'             => 'Customer',
 *     'number'               => '4222222222222222',
 *     'expiryMonth'          => '01',
 *     'expiryYear'           => '2020',
 *     'cvv'                  => '123',
 *     'email'                => 'customer@example.com',
 *     'billingAddress1'      => '1 Scrubby Creek Road',
 *     'billingCountry'       => 'AU',
 *     'billingCity'          => 'Scrubby Creek',
 *     'billingPostcode'      => '4999',
 *     'billingState'         => 'QLD',
 * ));
 *
 * // Do a purchase transaction on the gateway
 * $transaction = $gateway->purchase(array(
 *     'accountId'                 => '12345',
 *     'amount'                    => '10.00',
 *     'currency'                  => 'USD',
 *     'description'               => 'Super Deluxe Excellent Discount Package',
 *     'transactionId'             => 12345,
 *     'clientIp'                  => $_SERVER['REMOTE_ADDR'],
 *     'card'                      => $card,
 * ));
 * $response = $transaction->send();
 * if ($response->isSuccessful()) {
 *     echo "Purchase transaction was successful!\n";
 *     $sale_id = $response->getTransactionReference();
 *     echo "Transaction reference = " . $sale_id . "\n";
 * }
 * </code>
 *
 * @link https://www.firstdata.com/downloads/pdf/FDGG_Web_Service_API_v9.0.pdf
 */
class WebserviceGateway extends AbstractGateway
{
    public function getName()
    {
        return 'First Data Webservice';
    }

    public function getDefaultParameters()
    {
        return array(
            'sslCertificate'    => '',
            'sslKey'            => '',
            'sslKeyPassword'    => '',
            'userName'          => '',
            'password'          => '',
            'testMode'          => false,
        );
    }

    /**
     * Get SSL Certificate file name
     *
     * You must establish a secure communication channel to send the HTTP request.
     * This ensures that the data sent between your client application and the First
     * Data Webservice Gateway Web Service API is encrypted and that both parties can
     * be sure they are communicating with each other and no one else.
     *
     * The Web Service API requires an SSL connection with client and server exchanging
     * certificates to guarantee this level of security. The client and server certificates
     * each uniquely identify the party.
     *
     * @return string
     */
    public function getSslCertificate()
    {
        return $this->getParameter('sslCertificate');
    }

    /**
     * Set SSL Certificate file name
     *
     * You must establish a secure communication channel to send the HTTP request.
     * This ensures that the data sent between your client application and the First
     * Data Webservice Gateway Web Service API is encrypted and that both parties can
     * be sure they are communicating with each other and no one else.
     *
     * The Web Service API requires an SSL connection with client and server exchanging
     * certificates to guarantee this level of security. The client and server certificates
     * each uniquely identify the party.
     *
     * @param string $value
     * @return WebserviceGateway provides a fluent interface.
     */
    public function setSslCertificate($value)
    {
        return $this->setParameter('sslCertificate', $value);
    }

    /**
     * Get SSL Key file name
     *
     * You must establish a secure communication channel to send the HTTP request.
     * This ensures that the data sent between your client application and the First
     * Data Webservice Gateway Web Service API is encrypted and that both parties can
     * be sure they are communicating with each other and no one else.
     *
     * The Web Service API requires an SSL connection with client and server exchanging
     * certificates to guarantee this level of security. The client and server certificates
     * each uniquely identify the party.
     *
     * @return string
     */
    public function getSslKey()
    {
        return $this->getParameter('sslKey');
    }

    /**
     * Set SSL Key file name
     *
     * You must establish a secure communication channel to send the HTTP request.
     * This ensures that the data sent between your client application and the First
     * Data Webservice Gateway Web Service API is encrypted and that both parties can
     * be sure they are communicating with each other and no one else.
     *
     * The Web Service API requires an SSL connection with client and server exchanging
     * certificates to guarantee this level of security. The client and server certificates
     * each uniquely identify the party.
     *
     * @param string $value
     * @return WebserviceGateway provides a fluent interface.
     */
    public function setSslKey($value)
    {
        return $this->setParameter('sslKey', $value);
    }

    /**
     * Get SSL Key password
     *
     * You must establish a secure communication channel to send the HTTP request.
     * This ensures that the data sent between your client application and the First
     * Data Global Gateway Web Service API is encrypted and that both parties can
     * be sure they are communicating with each other and no one else.
     *
     * The Web Service API requires an SSL connection with client and server exchanging
     * certificates to guarantee this level of security. The client and server certificates
     * each uniquely identify the party.
     *
     * @return string
     */
    public function getSslKeyPassword()
    {
        return $this->getParameter('sslKeyPassword');
    }

    /**
     * Set SSL Key password
     *
     * You must establish a secure communication channel to send the HTTP request.
     * This ensures that the data sent between your client application and the First
     * Data Global Gateway Web Service API is encrypted and that both parties can
     * be sure they are communicating with each other and no one else.
     *
     * The Web Service API requires an SSL connection with client and server exchanging
     * certificates to guarantee this level of security. The client and server certificates
     * each uniquely identify the party.
     *
     * @param string $value
     * @return WebserviceGateway provides a fluent interface.
     */
    public function setSslKeyPassword($value)
    {
        return $this->setParameter('sslKeyPassword', $value);
    }

    /**
     * Get Username
     *
     * Calls to the Global Gateway API are secured with a username and
     * password sent via HTTP Basic Authentication.
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->getParameter('userName');
    }

    /**
     * Set Username
     *
     * Calls to the Global Gateway API are secured with a username and
     * password sent via HTTP Basic Authentication.
     *
     * @param string $value
     * @return WebserviceGateway provides a fluent interface.
     */
    public function setUserName($value)
    {
        return $this->setParameter('userName', $value);
    }

    /**
     * Get Password
     *
     * Calls to the Global Gateway API are secured with a username and
     * password sent via HTTP Basic Authentication.
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->getParameter('password');
    }

    /**
     * Set Password
     *
     * Calls to the Global Gateway API are secured with a username and
     * password sent via HTTP Basic Authentication.
     *
     * @param string $value
     * @return WebserviceGateway provides a fluent interface.
     */
    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }

    /**
     * Create a purchase request.
     *
     * @param array $parameters
     * @return \Omnipay\FirstData\Message\WebservicePurchaseRequest
     */
    public function purchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\FirstData\Message\WebservicePurchaseRequest', $parameters);
    }

    /**
     * Create an authorize request.
     *
     * @param array $parameters
     * @return \Omnipay\FirstData\Message\WebserviceAuthorizeRequest
     */
    public function authorize(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\FirstData\Message\WebserviceAuthorizeRequest', $parameters);
    }

    /**
     * Create a capture request.
     *
     * @param array $parameters
     * @return \Omnipay\FirstData\Message\WebserviceCaptureRequest
     */
    public function capture(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\FirstData\Message\WebserviceCaptureRequest', $parameters);
    }

    /**
     * Create a void request.
     *
     * @param array $parameters
     * @return \Omnipay\FirstData\Message\WebserviceVoidRequest
     */
    public function void(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\FirstData\Message\WebserviceVoidRequest', $parameters);
    }

    /**
     * Create a refund request.
     *
     * @param array $parameters
     * @return \Omnipay\FirstData\Message\WebserviceRefundRequest
     */
    public function refund(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\FirstData\Message\WebserviceRefundRequest', $parameters);
    }
}
