<?php

namespace DigiWallet\Methods;

use DigiWallet\Exception;
use DigiWallet\Request;
use DigiWallet\Transaction;

/**
 * DigiWallet transaction SDK - Sofort
 *
 * @author DigiWallet B.V.
 *
 * @property $name string Name of the payment method
 * @property $code string 3-character payment method identifier code
 * @property $startApi string URL location of this method's start API
 * @property $checkApi string URL location of this method's check API
 * @property $minimumAmount integer Minimum amount of this method in cents
 * @property $maximumAmount integer Maximum amount of this method in cents
 * @property $currencies array Available currencies for this method
 * @property $languages array Available languages for this method
 * @property $country string 2-letter country code
 * @property $type integer Product type code
 * @property $version integer Latest version of this method
 */
class Sofort extends Transaction
{
    protected $name = 'Sofort Banking';
    protected $method = 'SOF';
    protected $startApi = 'https://transaction.digiwallet.nl/directebanking/start';
    protected $checkApi = 'https://transaction.digiwallet.nl/directebanking/check';
    protected $minimumAmount = 10;
    protected $maximumAmount = 500000;
    protected $languages = ['de', 'en', 'nl'];
    protected $country;
    protected $type = 1;
    protected $version = 2;

    /**
     * Set country ID: AT=Austria, BE=Belgium, CH=Switzerland, DE=Germany, IT=Italy, NL=Netherlands
     * @param string $country
     * @return $this
     */
    public function country($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Set type ID: 1=physical product, 2=digital product, 3=digital adult product
     * @param integer $type
     * @return $this
     */
    public function type($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Add country ID to start request
     * @param Request $request
     * @throws Exception
     */
    public function beforeStart($request)
    {
        if (!$this->country) {
            throw new Exception('No country selected for Sofort Banking');
        }
        $request->bind('country', $this->country);
        $request->bind('type', $this->type);
    }
}

