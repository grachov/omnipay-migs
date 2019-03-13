<?php

namespace Omnipay\Migs\Message;

/**
 * Migs Purchase Request
 */
class ThreePartyPurchaseRequest extends AbstractRequest
{
    protected $action = 'pay';

    public function getData()
    {
        $this->validate('amount', 'returnUrl', 'transactionId');

        $card = $this->getCard();

        if ($card) {
            $this->getCard()->validate();
        }

        $data = $this->getBaseData();
        if ($card) {
            $data = array_merge($data, $this->getCardData());
        }
        $data['vpc_SecureHash']  = $this->calculateHash($data);
        $data['vpc_SecureHashType']  = 'SHA256';

        return $data;
    }

    public function sendData($data)
    {
        $redirectUrl = $this->getEndpoint().'?'.http_build_query($data);

        return $this->response = new ThreePartyPurchaseResponse($this, $data, $redirectUrl);
    }

    public function getEndpoint()
    {
        if ($this->getParameter('testMode')) {
            return $this->endpointTEST.'vpcpay';
        } else {
            return $this->endpoint.'vpcpay';
        }
    }
}
