<?php

namespace Omnipay\Pin\Message;

use Omnipay\Tests\TestCase;

class CreateCardRequestTest extends TestCase
{
    public function setUp()
    {
        $this->request = new CreateCardRequest($this->getHttpClient(), $this->getHttpRequest());
        $this->request->initialize(
            array(
                'card' => $this->getValidCard(),
            )
        );
    }

    public function testDataWithCard()
    {
        $card = $this->getValidCard();
        $this->request->setCard($card);
        $data = $this->request->getData();

        $this->assertSame($card['number'], $data['number']);
    }

    public function testSendSuccess()
    {
        $this->setMockHttpResponse('CardSuccess.txt');

        $response = $this->request->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertEquals('card_8LmnNMTYWG4zQZ4YnYQhBg', $response->getCardReference());
        $this->assertTrue($response->getMessage());
    }

    public function testSendError()
    {
        $this->setMockHttpResponse('CardFailure.txt');
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getCardReference());
        $this->assertSame('One or more parameters were missing or invalid', $response->getMessage());
    }
}
