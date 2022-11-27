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
class Paysafecard extends Transaction
{
    protected $name = 'PaysafeCard';
    protected $method = 'PSC';
    protected $startApi = 'https://transaction.digiwallet.nl/paysafecard/start';
    protected $checkApi = 'https://transaction.digiwallet.nl/paysafecard/check';
    protected $minimumAmount = 10;
    protected $maximumAmount = 15000;
    protected $currencies = ['EUR'];
    protected $languages = ['nl'];
    protected $version = 2;
}

