<?php
/**
 * MultiSafepay Rest Api Abstract Request class.
 */

namespace Omnipay\MultiSafepay\Message;

use Omnipay\Common\Message\AbstractRequest;
use Guzzle\Common\Event;

/**
 * MultiSafepay Rest API Abstract Request class.
 *
 * All Request classes prefixed by the Rest keyword
 * inheritance from this class.
 */
abstract class RestAbstractRequest extends AbstractRequest
{
    /**
     * User Agent.
     *
     * This user agent will be sent with each API request.
     *
     * @var string
     */
    protected $userAgent = 'Omnipay';

    /**
     * Live API endpoint.
     *
     * This endpoint will be used when the test mode is disabled.
     *
     * @var string
     */
    protected $liveEndpoint = 'https://api.multisafepay.com/v1/json';

    /**
     * Test API endpoint.
     *
     * This endpoint will be used when the test mode is enabled.
     *
     * @var string
     */
    protected $testEndpoint = 'https://testapi.multisafepay.com/v1/json';

    /**
     * Get the locale.
     *
     * Optional ISO 639-1 language code which is used to specify a
     * a language used to display gateway information and other
     * messages in the responses.
     *
     * The default language is English.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->getParameter('locale');
    }

    /**
     * Set the locale.
     *
     * Optional ISO 639-1 language code which is used to specify a
     * a language used to display gateway information and other
     * messages in the responses.
     *
     * The default language is English.
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setLocale($value)
    {
        return $this->setParameter('locale', $value);
    }

    /**
     * Get the gateway API Key
     *
     * Authentication is by means of a single secret API key set as
     * the apiKey parameter when creating the gateway object.
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->getParameter('apiKey');
    }

    /**
     * Set the gateway API Key
     *
     * Authentication is by means of a single secret API key set as
     * the apiKey parameter when creating the gateway object.
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setApiKey($value)
    {
        return $this->setParameter('apiKey', $value);
    }

    /**
     * Get endpoint
     *
     * @return string
     */
    public function getEndpoint()
    {
        if ($this->getTestMode()) {
            return $this->testEndpoint;
        }

        return $this->liveEndpoint;
    }

    /**
     * Get the raw data array for this message. The format of this varies from gateway to
     * gateway, but will usually be either an associative array, or a SimpleXMLElement.
     *
     * @return mixed
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        $this->validate('apiKey');
    }

    /**
     * Get headers.
     *
     * @return array
     */
    protected function getHeaders()
    {
        return array(
            'api_key' => $this->getApiKey(),
            'User-Agent' => $this->userAgent,
        );
    }

    /**
     * Execute the Guzzle request.
     *
     * @param $method
     * @param $endpoint
     * @param null $query
     * @param null $data
     * @return \Guzzle\Http\Message\Response
     */
    protected function sendRequest($method, $endpoint, $query = null, $data = null)
    {
        $this->httpClient->getEventDispatcher()->addListener('request.error', function (Event $event) {
            $response = $event['response'];
            if ($response->isError()) {
                $event->stopPropagation();
            }
        });

        $httpRequest = $this->httpClient->createRequest(
            $method,
            $this->getEndpoint() . $endpoint,
            $this->getHeaders(),
            $data
        );

        // Add query parameters
        if (is_array($query) && ! empty($query)) {
            foreach ($query as $itemKey => $itemValue) {
                $httpRequest->getQuery()->add($itemKey, $itemValue);
            }
        }

        return $httpRequest->send();
    }
}
