<?php
/**
 * First Data Webservice Abstract Request
 */

namespace Omnipay\FirstData\Message;

use Omnipay\Common\Message\AbstractRequest;

/**
 * First Data Webservice Abstract Request
 */
abstract class WebserviceAbstractRequest extends AbstractRequest
{
    /** @var string Live WSDL URL */
    protected $liveWsdl = "https://ws.firstdataglobalgateway.com/fdggwsapi/services/order.wsdl";

    /** @var string Test WSDL URL */
    protected $testWsdl = "https://ws.merchanttest.firstdataglobalgateway.com/fdggwsapi/services/order.wsdl";

    /** @var string Live endpoint for direct posting XML data */
    protected $liveEndpoint = 'https://ws.firstdataglobalgateway.com:443/fdggwsapi/services';

    /** @var string Test endpoint for direct posting XML data */
    protected $testEndpoint = 'https://ws.merchanttest.firstdataglobalgateway.com:443/fdggwsapi/services';

    /** @var  resource cURL handle */
    protected $curl;

    /** @var string SOAP template */
    protected $soapTemplate ='
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">
    <SOAP-ENV:Header />
    <SOAP-ENV:Body>
%xmlBody%
    </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
';

    /** @var string XML template for the purchase request */
    protected $xmlTemplate ='';

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
     * @return WebserviceAbstractRequest provides a fluent interface.
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
     * @return WebserviceAbstractRequest provides a fluent interface.
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
     * Data Webservice Gateway Web Service API is encrypted and that both parties can
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
     * Data Webservice Gateway Web Service API is encrypted and that both parties can
     * be sure they are communicating with each other and no one else.
     *
     * The Web Service API requires an SSL connection with client and server exchanging
     * certificates to guarantee this level of security. The client and server certificates
     * each uniquely identify the party.
     *
     * @param string $value
     * @return WebserviceAbstractRequest provides a fluent interface.
     */
    public function setSslKeyPassword($value)
    {
        return $this->setParameter('sslKeyPassword', $value);
    }

    /**
     * Get Username
     *
     * Calls to the Webservice Gateway API are secured with a username and
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
     * Calls to the Webservice Gateway API are secured with a username and
     * password sent via HTTP Basic Authentication.
     *
     * @param string $value
     * @return WebserviceAbstractRequest provides a fluent interface.
     */
    public function setUserName($value)
    {
        return $this->setParameter('userName', $value);
    }

    /**
     * Get Password
     *
     * Calls to the Webservice Gateway API are secured with a username and
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
     * Calls to the Webservice Gateway API are secured with a username and
     * password sent via HTTP Basic Authentication.
     *
     * @param string $value
     * @return WebserviceAbstractRequest provides a fluent interface.
     */
    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }

    /**
     * Get the base transaction data.
     *
     * @return array
     */
    protected function getBaseData()
    {
        $data = array();

        return $data;
    }

    /**
     * Get the transaction headers.
     *
     * @return array
     */
    protected function getHeaders()
    {
        return array(
            'Content-Type: text/xml'
        );
    }

    public function getData()
    {
        $data = $this->getBaseData();
        return $data;
    }

    /**
     * Build the cURL client.
     *
     * @return resource
     */
    public function buildCurlClient()
    {
        //
        // Use PHP Native cURL because the various Soap clients (BeSimple,
        // PHP native SoapClient) can't handle the WSDL file.
        //
        $this->curl = curl_init($this->getEndPoint());

        $sslCertificate = $this->getSslCertificate();
        $sslKey         = $this->getSslKey();
        $sslKeyPassword = $this->getSslKeyPassword();
        $userName       = $this->getUsername();
        $password       = $this->getPassword();
        $headers        = $this->getHeaders();

        // configuring cURL not to verify the server certificate
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, 0);

        // setting the path where cURL can find the client certificate
        curl_setopt($this->curl, CURLOPT_SSLCERT, $sslCertificate);

        // setting the path where cURL can find the client certificate’s
        // private key
        curl_setopt($this->curl, CURLOPT_SSLKEY, $sslKey);

        // setting the key password
        curl_setopt($this->curl, CURLOPT_SSLKEYPASSWD, $sslKeyPassword);

        // setting the request type to POST
        curl_setopt($this->curl, CURLOPT_POST, 1);

        // setting the content type
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);

        // setting the authorization method to BASIC
        curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

        // supplying your credentials
        curl_setopt($this->curl, CURLOPT_USERPWD, $userName . ':' . $password);

        // telling cURL to return the HTTP response body as operation result
        // value when calling curl_exec
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);

        return $this->curl;
    }

    public function sendData($data)
    {
        //
        // Do it with PHP Native Curl
        //
        $curl = $this->buildCurlClient();

        // Create the XML body
        $xmlBody = $this->xmlTemplate;
        foreach ($data as $key => $value) {
            $xmlBody = str_replace('%' . $key . '%', $value, $xmlBody);
        }

        // Substitute the XML body into the template
        $soapBody = str_replace('%xmlBody%', $xmlBody, $this->soapTemplate);

        // fill the request body with the SOAP message
        curl_setopt($curl, CURLOPT_POSTFIELDS, $soapBody);

        // echo "SOAP Body\n";
        // echo $soapBody;
        // echo "\nEND SOAP Body\n";

        // Send the request
        $result = curl_exec($curl);

        // close cURL
        curl_close($curl);

        // echo "SOAP Response\n";
        // echo $result;
        // echo "\nEND SOAP Response\n";

        // Create and return a response object
        return $this->createResponse($result);
    }

    /**
     * Get the endpoint URL for the request.
     *
     * @return string
     */
    public function getEndpoint()
    {
        return $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
    }

    /**
     * Create the response object.
     *
     * Create a structure by parsing the XML data and returning a simplified
     * array that our WebserviceResponse class can easily pull data from.
     *
     * @param string $data  XML
     * @return WebserviceResponse
     */
    public function createResponse($data)
    {
        // echo "XML Data = $data\n";

        // Parse the XML
        $parser = xml_parser_create_ns();
        $intermediate_data = array();
        $parsed_data = array();
        xml_parse_into_struct($parser, $data, $intermediate_data);

        // echo "Intermediate data =\n";
        // print_r($intermediate_data);
        // echo "\nEnd intermediate data\n";

        // Invert the parsed array from this type of structure:
        /*
        [4] => Array
            (
                [tag] => HTTP://SECURE.LINKPT.NET/FDGGWSAPI/SCHEMAS_US/FDGGWSAPI:COMMERCIALSERVICEPROVIDER
                [type] => complete
                [level] => 4
                [value] => CSI
            )
        */
        // To this, which we want for our internal purposes
        // [COMMERCIALSERVICEPROVIDER] => CSI
        // We basically just strip out everything before the final ':' of the
        // tag and attach any value to that, ignoring any fields that come through
        // from the XML parser without any values.

        foreach ($intermediate_data as $item) {
            if (! empty($item['value'])) {
                $parsed_tag = explode(':', $item['tag']);
                $tag = array_pop($parsed_tag);
                $parsed_data[$tag] = $item['value'];
            }
        }

        return $this->response = new WebserviceResponse($this, $parsed_data);
    }
}
