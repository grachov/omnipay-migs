<?php

namespace Omnipay\Migs\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\Common\Message\RedirectResponseInterface;

/**
 * Migs Purchase Response
 */
class ThreePartyPurchaseResponse extends AbstractResponse implements RedirectResponseInterface
{
    protected $endpointUrl;
    protected $hasCard;

    public function __construct(RequestInterface $request, $data, $endpointUrl, $hasCard = false)
    {
        parent::__construct($request, $data);
        $this->endpointUrl = $endpointUrl;
        $this->hasCard = $hasCard;
    }

    public function isSuccessful()
    {
        return false;
    }

    public function isRedirect()
    {
        return true;
    }

    public function getRedirectUrl()
    {
        return $this->endpointUrl . ($this->hasCard ? '' : '?'.http_build_query($this->getData()));
    }

    public function getRedirectMethod()
    {
        return $this->hasCard ? 'POST' : 'GET';
    }

    public function getRedirectData()
    {
        return $this->hasCard ? $this->getData() : [];
    }
}
