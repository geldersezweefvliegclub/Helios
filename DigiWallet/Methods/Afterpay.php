<?php

namespace DigiWallet\Methods;

use DigiWallet\CheckResponse;
use DigiWallet\Request;
use DigiWallet\StartResponse;
use DigiWallet\Transaction;
use function json_encode;

/**
 * DigiWallet transaction SDK - AfterPay
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
 * @property array $invoiceLines Array of various invoice lines of this invoice
 *  This parameter is sent as a JSON-encoded array and it must contain elements of the following format:
 *  [
 *      0 => [
 *          'productCode' => '0001-TEST',
 *          'productDescription' => 'Test Object 1',
 *          'quantity' => 1,
 *          'price' => 10.00,
 *          'taxCategory' => 1,
 *      ],
 *      1 => [
 *          'productCode' => '0002-TEST',
 *          'productDescription' => 'Test Object 2',
 *          'quantity' => 2,
 *          'price' => 15.00,
 *          'taxCategory' => 1,
 *      ],
 *  ]
 *  See the detailed documentation for more information:
 *      https://www.digiwallet.nl/nl/documentation/paymethods/afterpay#invoicelines
 * @property string $billingStreet The street name of the person paying the invoice
 * @property string $billingHouseNumber The house number of the person paying the invoice
 * @property string $billingPostalCode The postal code of the person paying the invoice
 * @property string $billingCity The city of the person paying the invoice
 * @property string $billingPersonEmail The email address of the person paying the invoice
 * @property string $billingPersonInitials The initials of the person paying the invoice
 * @property string $billingPersonGender The gender of the person paying the invoice, M or F
 * @property string $billingPersonSurname The surname of the person paying the invoice
 * @property string $billingCountryCode The country code of the person paying the invoice, 3 characters (e.g. NLD)
 * @property string $billingPersonLanguageCode The language of the person paying the invoice, 3 characters (e.g. NLD)
 * @property string $billingPersonBirthDate The birth date of the person paying the invoice (YYYY-MM-DD format)
 * @property string $billingPersonPhoneNumber The phone number of the person paying the invoice
 * @property string $shippingStreet The street name of the person receiving the product
 * @property string $shippingHouseNumber The house number of the person receiving the product
 * @property string $shippingPostalCode The postal code of the person receiving the product
 * @property string $shippingCity The city of the person receiving the product
 * @property string $shippingPersonEmail The email address of the person receiving the product
 * @property string $shippingPersonInitials The initials of the person receiving the product
 * @property string $shippingPersonGender The gender of the person receiving the product
 * @property string $shippingPersonSurname The surname of the person receiving the product
 * @property string $shippingCountryCode The country code of the person receiving the product, 3 characters (e.g. NLD)
 * @property string $shippingPersonLanguageCode The language of the person receiving the product, 3 characters (e.g. NLD)
 * @property string $shippingPersonBirthDate The birth date of the person receiving the product (YYYY-MM-DD format)
 * @property string $shippingPersonPhoneNumber The phone number of the person receiving the product
 * @property $version integer Latest version of this method
 */
class Afterpay extends Transaction
{
    protected $name = 'AfterPay';
    protected $method = 'AFP';
    protected $startApi = 'https://transaction.digiwallet.nl/afterpay/start';
    protected $checkApi = 'https://transaction.digiwallet.nl/afterpay/check';
    protected $minimumAmount = 100;
    protected $maximumAmount = 10000;
    protected $currencies = ['EUR'];
    protected $languages = ['nl', 'en'];
    protected $invoiceLines;
    protected $billingStreet;
    protected $billingHouseNumber;
    protected $billingPostalCode;
    protected $billingCity;
    protected $billingPersonEmail;
    protected $billingPersonInitials;
    protected $billingPersonGender;
    protected $billingPersonSurname;
    protected $billingCountryCode;
    protected $billingPersonLanguageCode;
    protected $billingPersonBirthDate;
    protected $billingPersonPhoneNumber;
    protected $shippingStreet;
    protected $shippingHouseNumber;
    protected $shippingPostalCode;
    protected $shippingCity;
    protected $shippingPersonEmail;
    protected $shippingPersonInitials;
    protected $shippingPersonGender;
    protected $shippingPersonSurname;
    protected $shippingCountryCode;
    protected $shippingPersonLanguageCode;
    protected $shippingPersonBirthDate;
    protected $shippingPersonPhoneNumber;
    protected $version = 1;

    /**
     * Called after start to process http request to a response
     * @param $httpResponse string
     * @return StartResponse
     */
    public function parseStartResponse($httpResponse)
    {
        if (strpos($httpResponse, '000000') === 0) {
            $httpResponse = explode('|', substr($httpResponse, 7));
            $this->transactionId = $httpResponse[0]; // For immediate reuse of the object

            return new StartResponse(['status' => true, 'transactionId' => $httpResponse[0], 'url' => $httpResponse[2]]);
        }

        return new StartResponse(['status' => false, 'error' => $httpResponse]);
    }

    /**
     * Called after check to process http request to a response
     * @param $httpResponse
     * @return CheckResponse
     */
    public function parseCheckResponse($httpResponse)
    {
        if (strpos($httpResponse, '000000') === 0) {
            $response = explode('|', substr($httpResponse, 7));
            if ($response[2] === 'Captured') {
                return new CheckResponse(['status' => true]);
            }

            return new CheckResponse(['status' => false, 'error' => $httpResponse]);
        }

        return new CheckResponse(['status' => false, 'error' => $httpResponse]);
    }

    public function invoiceLines($invoicesLines)
    {
        $this->invoiceLines = $invoicesLines;

        return $this;
    }

    /**
     * @param string $billingStreet
     * @return $this
     */
    public function billingStreet($billingStreet)
    {
        $this->billingStreet = $billingStreet;

        return $this;
    }

    /**
     * @param string $billingHouseNumber
     * @return $this
     */
    public function billingHouseNumber($billingHouseNumber)
    {
        $this->billingHouseNumber = $billingHouseNumber;

        return $this;
    }

    /**
     * @param string $billingPostalCode
     * @return $this
     */
    public function billingPostalCode($billingPostalCode)
    {
        $this->billingPostalCode = $billingPostalCode;

        return $this;
    }

    /**
     * @param string $billingCity
     * @return $this
     */
    public function billingCity($billingCity)
    {
        $this->billingCity = $billingCity;

        return $this;
    }

    /**
     * @param string $billingPersonEmail
     * @return $this
     */
    public function billingPersonEmail($billingPersonEmail)
    {
        $this->billingPersonEmail = $billingPersonEmail;

        return $this;
    }

    /**
     * @param string $billingPersonInitials
     * @return $this
     */
    public function billingPersonInitials($billingPersonInitials)
    {
        $this->billingPersonInitials = $billingPersonInitials;

        return $this;
    }

    /**
     * @param string $billingPersonGender
     * @return $this
     */
    public function billingPersonGender($billingPersonGender)
    {
        $this->billingPersonGender = $billingPersonGender;

        return $this;
    }

    /**
     * @param string $billingPersonSurname
     * @return $this
     */
    public function billingPersonSurname($billingPersonSurname)
    {
        $this->billingPersonSurname = $billingPersonSurname;

        return $this;
    }

    /**
     * @param string $billingCountryCode
     * @return $this
     */
    public function billingCountryCode($billingCountryCode)
    {
        $this->billingCountryCode = $billingCountryCode;

        return $this;
    }

    /**
     * @param string $billingPersonLanguageCode
     * @return $this
     */
    public function billingPersonLanguageCode($billingPersonLanguageCode)
    {
        $this->billingPersonLanguageCode = $billingPersonLanguageCode;

        return $this;
    }

    /**
     * @param string $billingPersonBirthDate
     * @return $this
     */
    public function billingPersonBirthDate($billingPersonBirthDate)
    {
        $this->billingPersonBirthDate = $billingPersonBirthDate;

        return $this;
    }

    /**
     * @param string $billingPersonPhoneNumber
     * @return $this
     */
    public function billingPersonPhoneNumber($billingPersonPhoneNumber)
    {
        $this->billingPersonPhoneNumber = $billingPersonPhoneNumber;

        return $this;
    }

    /**
     * @param string $shippingStreet
     * @return $this
     */
    public function shippingStreet($shippingStreet)
    {
        $this->shippingStreet = $shippingStreet;

        return $this;
    }

    /**
     * @param string $shippingHouseNumber
     * @return $this
     */
    public function shippingHouseNumber($shippingHouseNumber)
    {
        $this->shippingHouseNumber = $shippingHouseNumber;

        return $this;
    }

    /**
     * @param string $shippingPostalCode
     * @return $this
     */
    public function shippingPostalCode($shippingPostalCode)
    {
        $this->shippingPostalCode = $shippingPostalCode;

        return $this;
    }

    /**
     * @param string $shippingCity
     * @return $this
     */
    public function shippingCity($shippingCity)
    {
        $this->shippingCity = $shippingCity;

        return $this;
    }

    /**
     * @param string $shippingPersonEmail
     * @return $this
     */
    public function shippingPersonEmail($shippingPersonEmail)
    {
        $this->shippingPersonEmail = $shippingPersonEmail;

        return $this;
    }

    /**
     * @param string $shippingPersonInitials
     * @return $this
     */
    public function shippingPersonInitials($shippingPersonInitials)
    {
        $this->shippingPersonInitials = $shippingPersonInitials;

        return $this;
    }

    /**
     * @param string $shippingPersonGender
     * @return $this
     */
    public function shippingPersonGender($shippingPersonGender)
    {
        $this->shippingPersonGender = $shippingPersonGender;

        return $this;
    }

    /**
     * @param string $shippingPersonSurname
     * @return $this
     */
    public function shippingPersonSurname($shippingPersonSurname)
    {
        $this->shippingPersonSurname = $shippingPersonSurname;

        return $this;
    }

    /**
     * @param string $shippingCountryCode
     * @return $this
     */
    public function shippingCountryCode($shippingCountryCode)
    {
        $this->shippingCountryCode = $shippingCountryCode;

        return $this;
    }

    /**
     * @param string $shippingPersonLanguageCode
     * @return $this
     */
    public function shippingPersonLanguageCode($shippingPersonLanguageCode)
    {
        $this->shippingPersonLanguageCode = $shippingPersonLanguageCode;

        return $this;
    }

    /**
     * @param string $shippingPersonBirthDate
     * @return $this
     */
    public function shippingPersonBirthDate($shippingPersonBirthDate)
    {
        $this->shippingPersonBirthDate = $shippingPersonBirthDate;

        return $this;
    }

    /**
     * @param string $shippingPersonPhoneNumber
     * @return $this
     */
    public function shippingPersonPhoneNumber($shippingPersonPhoneNumber)
    {
        $this->shippingPersonPhoneNumber = $shippingPersonPhoneNumber;

        return $this;
    }

    /**
     * Add paymethod specific parameters to start request
     * @param $request Request
     */
    public function beforeStart($request)
    {
        if (!empty($this->invoiceLines)) {
            $request->bind('invoicelines', json_encode($this->invoiceLines));
        }
        if (!empty($this->billingStreet)) {
            $request->bind('billingstreet', $this->billingStreet);
        }
        if (!empty($this->billingHouseNumber)) {
            $request->bind('billinghousenumber', $this->billingHouseNumber);
        }
        if (!empty($this->billingPostalCode)) {
            $request->bind('billingpostalcode', $this->billingPostalCode);
        }
        if (!empty($this->billingCity)) {
            $request->bind('billingcity', $this->billingCity);
        }
        if (!empty($this->billingPersonEmail)) {
            $request->bind('billingpersonemail', $this->billingPersonEmail);
        }
        if (!empty($this->billingPersonInitials)) {
            $request->bind('billingpersoninitials', $this->billingPersonInitials);
        }
        if (!empty($this->billingPersonGender)) {
            $request->bind('billingpersongender', $this->billingPersonGender);
        }
        if (!empty($this->billingPersonSurname)) {
            $request->bind('billingpersonsurname', $this->billingPersonSurname);
        }
        if (!empty($this->billingCountryCode)) {
            $request->bind('billingcountrycode', $this->billingCountryCode);
        }
        if (!empty($this->billingPersonLanguageCode)) {
            $request->bind('billingpersonlanguagecode', $this->billingPersonLanguageCode);
        }
        if (!empty($this->billingPersonBirthDate)) {
            $request->bind('billingpersonbirthdate', $this->billingPersonBirthDate);
        }
        if (!empty($this->billingPersonPhoneNumber)) {
            $request->bind('billingpersonphonenumber', $this->billingPersonPhoneNumber);
        }
        if (!empty($this->shippingStreet)) {
            $request->bind('shippingstreet', $this->shippingStreet);
        }
        if (!empty($this->shippingHouseNumber)) {
            $request->bind('shippinghousenumber', $this->shippingHouseNumber);
        }
        if (!empty($this->shippingPostalCode)) {
            $request->bind('shippingpostalcode', $this->shippingPostalCode);
        }
        if (!empty($this->shippingCity)) {
            $request->bind('shippingcity', $this->shippingCity);
        }
        if (!empty($this->shippingPersonEmail)) {
            $request->bind('shippingpersonemail', $this->shippingPersonEmail);
        }
        if (!empty($this->shippingPersonInitials)) {
            $request->bind('shippingpersoninitials', $this->shippingPersonInitials);
        }
        if (!empty($this->shippingPersonGender)) {
            $request->bind('shippingpersongender', $this->shippingPersonGender);
        }
        if (!empty($this->shippingPersonSurname)) {
            $request->bind('shippingpersonsurname', $this->shippingPersonSurname);
        }
        if (!empty($this->shippingCountryCode)) {
            $request->bind('shippingcountrycode', $this->shippingCountryCode);
        }
        if (!empty($this->shippingPersonLanguageCode)) {
            $request->bind('shippingpersonlanguagecode', $this->shippingPersonLanguageCode);
        }
        if (!empty($this->shippingPersonBirthDate)) {
            $request->bind('shippingpersonbirthdate', $this->shippingPersonBirthDate);
        }
        if (!empty($this->shippingPersonPhoneNumber)) {
            $request->bind('shippingpersonphonenumber', $this->shippingPersonPhoneNumber);
        }
    }
}

