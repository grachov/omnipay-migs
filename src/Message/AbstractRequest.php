<?php

namespace Omnipay\Migs\Message;

use Omnipay\Common\CreditCard;
use Omnipay\Common\Exception\InvalidCreditCardException;

/**
 * GoCardless Abstract Request
 */
abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    private static $brands = [
        CreditCard::BRAND_AMEX => 'Amex',
        CreditCard::BRAND_DINERS_CLUB => 'Dinersclub',
        CreditCard::BRAND_JCB => 'JCB',
        CreditCard::BRAND_MAESTRO => 'Maestro',
        CreditCard::BRAND_MASTERCARD => 'Mastercard',
        CreditCard::BRAND_VISA => 'Visa',
    ];
    protected $endpoint = 'https://migs.mastercard.com.au/';
    protected $endpointTEST = 'https://migs-mtf.mastercard.com.au/';

    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    public function getMerchantAccessCode()
    {
        return $this->getParameter('merchantAccessCode');
    }

    public function setMerchantAccessCode($value)
    {
        return $this->setParameter('merchantAccessCode', $value);
    }

    public function getSecureHash()
    {
        return $this->getParameter('secureHash');
    }

    public function setSecureHash($value)
    {
        return $this->setParameter('secureHash', $value);
    }

    public function getLocaleCode()
    {
        return $this->getParameter('localeCode');
    }

    public function setLocaleCode($value)
    {
        return $this->setParameter('localeCode', $value);
    }

    public function getTransactionNo()
    {
        return $this->getParameter('transactionNo');
    }

    public function setTransactionNo($value)
    {
        return $this->setParameter('transactionNo', $value);
    }

    public function getUser()
    {
        return $this->getParameter('user');
    }

    public function setUser($value)
    {
        return $this->setParameter('user', $value);
    }

    public function getPassword()
    {
        return $this->getParameter('password');
    }

    public function setPassword($value)
    {
        return $this->setParameter('password', $value);
    }

    protected function getBaseData()
    {
        $data = array();
        $data['vpc_Merchant']   = $this->getMerchantId();
        $data['vpc_AccessCode'] = $this->getMerchantAccessCode();
        $data['vpc_Version']    = '1';
        $data['vpc_Command']    = $this->action;
        $data['vpc_Amount']      = $this->getAmountInteger();
        $data['vpc_MerchTxnRef'] = $this->getTransactionId();
        
        if ($this->action != 'refund') {
            $data['vpc_Locale']      = $this->getLocaleCode() != null ? $this->getLocaleCode() : 'en';
            $data['vpc_OrderInfo']   = $this->getDescription();
            $data['vpc_ReturnURL']   = $this->getReturnUrl();
            $data['vpc_Currency']   = $this->getCurrency();
        }

        return $data;
    }

    protected function getCardData()
    {
        $card = $this->getCard();

        $brand = $card->getBrand();
        if (!isset(self::$brands[$brand])) {
            throw new InvalidCreditCardException('Card brand is invalid');
        }

        return [
            'vpc_card' => self::$brands[$brand],
            'vpc_Gateway' => 'ssl',
            'vpc_CardNum' => $card->getNumber(),
            'vpc_CardExp' => $card->getExpiryDate('ym'),
            'vpc_CardSecurityCode' => $card->getCvv(),
        ];
    }

    public function getEndpoint()
    {
        return $this->endpoint;
    }

    public function calculateHash($data)
    {
        ksort($data);

        $hash = null;
        foreach ($data as $k => $v) {
            // Skip vpc_ keys that are not included in the hash calculation
            if (in_array($k, array('vpc_SecureHash', 'vpc_SecureHashType'))) {
                continue;
            }

            if ((strlen($v) > 0) && ((substr($k, 0, 4)=="vpc_") || (substr($k, 0, 5) =="user_"))) {
                $hash .= $k . "=" . $v . "&";
            }
        }
        $hash = rtrim($hash, "&");

        return strtoupper(hash_hmac('SHA256', $hash, pack('H*', $this->getSecureHash())));
    }
}
