<?php namespace Omnipay\MultiSafepay\Message;

use Omnipay\Tests\TestCase;

class RestFetchPaymentMethodsRequestTest extends TestCase
{
    /**
     * @var FetchPaymentMethodsRequest
     */
    private $request;

    protected function setUp()
    {
        $this->request = new RestFetchPaymentMethodsRequest(
            $this->getHttpClient(),
            $this->getHttpRequest()
        );

        $this->request->initialize(
            array(
                'api_key' => '123456789',
                'country' => 'NL'
            )
        );
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('RestFetchPaymentMethodsSuccess.txt');

        $response = $this->request->send();

        $paymentMethods = $response->getPaymentMethods();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());

        $this->assertInternalType('array', $paymentMethods);
        $this->assertContainsOnlyInstancesOf('Omnipay\Common\PaymentMethod', $paymentMethods);
    }
}
