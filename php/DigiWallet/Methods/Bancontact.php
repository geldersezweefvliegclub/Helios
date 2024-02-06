<?php

namespace DigiWallet\Methods;

use DigiWallet\Transaction;

/**
 * DigiWallet transaction SDK - Bancontact/Mister Cash
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
class Bancontact extends Transaction
{
    protected $name = 'Bancontact/Mister Cash';
    protected $method = 'MRC';
    protected $startApi = 'https://transaction.digiwallet.nl/mrcash/start';
    protected $checkApi = 'https://transaction.digiwallet.nl/mrcash/check';
    protected $minimumAmount = 49;
    protected $maximumAmount = 500000;
    protected $currencies = ['EUR'];
    protected $languages = ['nl', 'fr', 'en'];
    protected $version = 2;
}

