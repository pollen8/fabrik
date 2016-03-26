<?php
/**
 * MultiSafepay XML Api Purchase Request.
 */

namespace Omnipay\MultiSafepay\Message;

use Omnipay\Common\CreditCard;
use SimpleXMLElement;

/**
 * MultiSafepay XML Api Purchase Request.
 *
 * @deprecated This API is deprecated and will be removed in
 * an upcoming version of this package. Please switch to the Rest API.
 */
class PurchaseRequest extends AbstractRequest
{
    /**
     * Get the language.
     *
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->getParameter('language');
    }

    /**
     * Set the language.
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setLanguage($value)
    {
        return $this->setParameter('language', $value);
    }

    /**
     * Get the gateway.
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
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setGateway($value)
    {
        return $this->setParameter('gateway', $value);
    }

    /**
     * Get Issuer.
     *
     * @return mixed
     */
    public function getIssuer()
    {
        return $this->getParameter('issuer');
    }

    /**
     * Set issuer.
     *
     * @param string $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setIssuer($value)
    {
        return $this->setParameter('issuer', $value);
    }

    /**
     * Get the Google analytics code.
     *
     * @return mixed
     */
    public function getGoogleAnalyticsCode()
    {
        return $this->getParameter('googleAnalyticsCode');
    }

    /**
     * Set the Google analytics code.
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setGoogleAnalyticsCode($value)
    {
        return $this->setParameter('googleAnalyticsCode', $value);
    }

    /**
     * Get the value of "extradata1"
     *
     * @return mixed
     */
    public function getExtraData1()
    {
        return $this->getParameter('extraData1');
    }

    /**
     * Set the value of "extradata1"
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setExtraData1($value)
    {
        return $this->setParameter('extraData1', $value);
    }

    /**
     * Get the value of "extradata2"
     *
     * @return mixed
     */
    public function getExtraData2()
    {
        return $this->getParameter('extraData2');
    }

    /**
     * Set the value of "extraData2
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setExtraData2($value)
    {
        return $this->setParameter('extraData2', $value);
    }

    /**
     * Get the value of "extraData3"
     *
     * @return mixed
     */
    public function getExtraData3()
    {
        return $this->getParameter('extraData3');
    }

    /**
     * Set the value of "extraData3"
     *
     * @param $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setExtraData3($value)
    {
        return $this->setParameter('extraData3', $value);
    }

    /**
     * Get the items.
     *
     * @return mixed
     */
    public function getItems()
    {
        return $this->getParameter('items');
    }

    /**
     * Set the items.
     *
     * @param array|\Omnipay\Common\ItemBag $value
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function setItems($value)
    {
        return $this->setParameter('items', $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $this->validate('transactionId', 'amount', 'currency', 'description', 'clientIp', 'card');

        if ('IDEAL' === $this->getGateway() && $this->getIssuer()) {
            $data = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><directtransaction/>');
        } else {
            $data = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><redirecttransaction/>');
        }

        $data->addAttribute('ua', $this->userAgent);

        $merchant = $data->addChild('merchant');
        $merchant->addChild('account', $this->getAccountId());
        $merchant->addChild('site_id', $this->getSiteId());
        $merchant->addChild('site_secure_code', $this->getSiteCode());
        $merchant->addChild('notification_url', htmlspecialchars($this->getNotifyUrl()));
        $merchant->addChild('cancel_url', htmlspecialchars($this->getCancelUrl()));
        $merchant->addChild('redirect_url', htmlspecialchars($this->getReturnUrl()));

        /** @var CreditCard $card */
        $card = $this->getCard();
        $customer = $data->addChild('customer');
        $customer->addChild('ipaddress', $this->getClientIp());
        $customer->addChild('locale', $this->getLanguage());
        $customer->addChild('email', $card->getEmail());
        $customer->addChild('firstname', $card->getFirstName());
        $customer->addChild('lastname', $card->getLastName());
        $customer->addChild('address1', $card->getAddress1());
        $customer->addChild('address2', $card->getAddress2());
        $customer->addChild('zipcode', $card->getPostcode());
        $customer->addChild('city', $card->getCity());
        $customer->addChild('country', $card->getCountry());
        $customer->addChild('phone', $card->getPhone());

        $data->addChild('google_analytics', $this->getGoogleAnalyticsCode());

        $transaction = $data->addChild('transaction');
        $transaction->addChild('id', $this->getTransactionId());
        $transaction->addChild('currency', $this->getCurrency());
        $transaction->addChild('amount', $this->getAmountInteger());
        $transaction->addChild('description', $this->getDescription());
        $transaction->addChild('var1', $this->getExtraData1());
        $transaction->addChild('var2', $this->getExtraData2());
        $transaction->addChild('var3', $this->getExtraData3());
        $transaction->addChild('gateway', $this->getGateway());

        if ($items = $this->getItems()) {
            $itemsHtml = '<ul>';
            foreach ($items as $item) {
                $itemsHtml .= "<li>{$item['quantity']} x {$item['name']}</li>";
            }
            $itemsHtml .= '</ul>';
            $transaction->addChild('items', htmlspecialchars($itemsHtml));
        }

        if ('IDEAL' === $this->getGateway() && $this->getIssuer()) {
            $gatewayInfo = $data->addChild('gatewayinfo');
            $gatewayInfo->addChild('issuerid', $this->getIssuer());
        }

        $data->addChild('signature', $this->generateSignature());

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function sendData($data)
    {
        $httpResponse = $this->httpClient->post(
            $this->getEndpoint(),
            $this->getHeaders(),
            $data->asXML()
        )->send();

        $this->response =  new PurchaseResponse(
            $this,
            $httpResponse->xml()
        );

        return $this->response;
    }

    /**
     * Generate signature.
     *
     * @return string
     */
    protected function generateSignature()
    {
        return md5(
            $this->getAmountInteger().
            $this->getCurrency().
            $this->getAccountId().
            $this->getSiteId().
            $this->getTransactionId()
        );
    }
}
