<?php

namespace DigiWallet\Methods;

use DigiWallet\Transaction;

/**
 * DigiWallet transaction SDK - PaysafeCard
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
 * @property $version integer Latest version of this method
 */
class Paypal extends Transaction
{
    protected $name = 'PayPal';
    protected $method = 'PYP';
    protected $startApi = 'https://transaction.digiwallet.nl/paypal/start';
    protected $checkApi = 'https://transaction.digiwallet.nl/paypal/check';
    protected $minimumAmount = 84;
    protected $maximumAmount = 1000000;
    protected $currencies = ['EUR'];
    protected $languages = ['nl'];
    protected $version = 2;
}

