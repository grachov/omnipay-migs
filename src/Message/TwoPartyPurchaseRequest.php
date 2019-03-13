<?php

namespace Omnipay\Migs\Message;

/**
 * Migs Purchase Request
 */
class TwoPartyPurchaseRequest extends AbstractRequest
{
    protected $action = 'pay';

    public function getData()
    {
        $this->validate('amount', 'transactionId', 'card');

        $this->getCard()->validate();

        $data = array_merge($this->getBaseData(), $this->getCardData());
        $data['vpc_SecureHash']  = $this->calculateHash($data);
        $data['vpc_SecureHashType']  = 'SHA256';

        return $data;
    }

    public function sendData($data)
    {
        $headers = [
          'Content-Type'  => 'application/x-www-form-urlencoded',
        ];

        $httpResponse = $this->httpClient->request('POST', $this->getEndpoint(), $headers, http_build_query($data));

        return $this->response = new Response($this, $httpResponse->getBody()->getContents());
    }

    public function getEndpoint()
    {
        return $this->endpoint.'vpcdps';
    }
}
