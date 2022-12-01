<?php

namespace DigiWallet\Methods;

use DigiWallet\Transaction;
use DigiWallet\Request;
use SimpleXMLElement;

/**
 * DigiWallet transaction SDK - iDEAL
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
 * @property $bank string Optional 8 character SEPA issuer code of the chosen bank
 * @property $version integer Latest version of this method
 */
class Ideal extends Transaction
{
    protected $name = 'iDEAL';
    protected $method = 'IDE';
    protected $startApi = 'https://transaction.digiwallet.nl/ideal/start';
    protected $checkApi = 'https://transaction.digiwallet.nl/ideal/check';
    protected $minimumAmount = 84;
    protected $maximumAmount = 1000000;
    protected $currencies = ['EUR'];
    protected $languages = ['nl'];
    protected $bank;
    protected $version = 4;

    /**
     *  Set bank ID
     * @param string $bank 8 character SEPA issuer code of the chosen bank, obtained by Ideal::bankList()
     * @return $this
     */
    public function bank($bank)
    {
        $this->bank = substr($bank, 0, 8);

        return $this;
    }

    /**
     * Get list with bank codes
     * @return array List with SEPA issuer codes as key and and bank names as values
     */
    public function bankList()
    {
        $issuers = [];

        $request = new Request('https://transaction.digiwallet.nl/ideal/getissuers?ver=' . urlencode($this->version) . '&format=xml');
        $xml = $request->execute();
        if (!$xml) {
            $issuers['IDE0001'] = 'Bankenlijst kon niet opgehaald worden bij DigiWallet, controleer of cURL werkt!';
            $issuers['IDE0002'] = '  ';
        }
        else {
            $issuersObj = new SimpleXMLElement($xml);

            foreach ($issuersObj->issuer as $issuer) {
                $id = (string)$issuer->attributes()['id'];
                $value = (string)$issuer;
                array_push($issuers, (object)array( 'ID' => $id, 'NAAM' => $value));
            }
        }
        return $issuers;
    }

    /**
     * Add paymethod specific parameters to start request
     * @param $request Request
     */
    public function beforeStart($request)
    {
        if (!empty($this->bank)) {
            $request->bind('bank', $this->bank);
        }
    }
}

