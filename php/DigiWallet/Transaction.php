<?php

namespace DigiWallet;
use DigiWallet\Methods\Afterpay;
use DigiWallet\Methods\Bancontact;
use DigiWallet\Methods\Creditcard;
use DigiWallet\Methods\Ideal;
use DigiWallet\Methods\Paypal;
use DigiWallet\Methods\Paysafecard;
use DigiWallet\Methods\Sofort;
use function is_bool;

/**
 *  DigiWallet transaction SDK - Abstract base class
 *
 * @author DigiWallet B.V.
 * @ver 1.0
 *
 * @property $salt string Private secret for use in encryption on various APIs
 * @property $name string Official name
 * @property $method string Payment method identifier
 * @property $startApi string Start API URL
 * @property $checkApi string Check API URL
 * @property $minimumAmount integer Minimum transaction amount in cents
 * @property $maximumAmount integer Maximum transaction amount in cents
 * @property $currencies array Currencies available, first is default
 * @property $languages array Languages available, first is default
 * @property $outletId integer DigiWallet Outlet identifier
 * @property $language string Language, will be set to the default for the payment method if not explicitly defined
 * @property $currency string Currency, will be set to the default for the payment method if not explicitly defined
 * @property $appId string Generic identifier to let DigiWallet know who's making a request
 * @property $amount integer Amount of the transaction in cents
 * @property $description string Description of the transaction for on bank statements
 * @property $returnUrl string URL location where to return to after processing the transaction
 * @property $cancelUrl string URL location where to return to after cancelling the transaction
 * @property $reportUrl string URL location where to send server-to-server callbacks about the transaction statuses
 * @property $transactionId string The identifier of the transaction
 * @property $version integer API version
 * @property $test bool Whether to use the DigiWallet Test Panel or not
 */
abstract class Transaction
{
    protected $salt = '932kvm8937*#&1nj_aa9873j0a0987';
    protected $name;
    protected $method;
    protected $startApi;
    protected $checkApi;
    protected $minimumAmount = 84;
    protected $maximumAmount = 1000000;
    protected $currencies = ['EUR'];
    protected $languages = ['NL'];
    protected $outletId;
    protected $language;
    protected $currency;
    protected $appId = 'dw_example_sdk_1.0';
    protected $amount = 0;
    protected $description;
    protected $returnUrl;
    protected $cancelUrl;
    protected $reportUrl;
    protected $transactionId;
    protected $version;
    protected $test;

    /**
     * Called before start call so additional parameters can be added to the request
     * May be implemented by specific payment method where needed
     * @param $request
     */
    public function beforeStart($request)
    {
    }

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

            return new StartResponse(['status' => true, 'transactionId' => $httpResponse[0], 'url' => $httpResponse[1]]);
        }

        return new StartResponse(['status' => false, 'error' => $httpResponse]);
    }

    /**
     *  Start transaction at TargetPay
     */

    public function start()
    {
        if (!$this->amount) {
            throw new Exception('No amount given');
        }
        if ($this->amount < $this->minimumAmount) {
            throw new Exception ('Amount is too low: minimum=' . $this->minimumAmount);
        }
        if ($this->amount > $this->maximumAmount) {
            throw new Exception ('Amount is too high: maximum=' . $this->maximumAmount);
        }

        // Create request object
        $request = new Request($this->startApi);
        $request->bind([
            'rtlo' => $this->outletId,
            'amount' => $this->amount,
            'description' => $this->description,
            'reporturl' => $this->reportUrl,
            'returnurl' => $this->returnUrl,
            'cancelurl' => $this->cancelUrl,
            'app_id' => $this->appId,
            'language' => $this->language ? $this->language : $this->languages[0],
            'lang' => $this->language ? $this->language : $this->languages[0],
            'currency' => $this->currency ? $this->currency : $this->currencies[0],
            'userip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
            'domain' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'http://www.lepel.nl',
            'salt' => $this->salt,
            'ver' => $this->version,
            'test' => (int)$this->test
        ]);

        // Invoke on before start event
        $this->beforeStart($request);

        // Do http call
        $httpResponse = $request->execute();

        // Make start response object and return it
        return $this->parseStartResponse($httpResponse);
    }

    /**
     * Called after check to process http request to a response
     * @param $httpResponse
     * @return CheckResponse
     */
    public function parseCheckResponse($httpResponse)
    {
        if (strpos($httpResponse, '000000') === 0) {
            return new CheckResponse(['status' => true]);
        }

        return new CheckResponse(['status' => false, 'error' => $httpResponse]);
    }

    /**
     *  Check transaction with DigiWallet
     */
    public function check()
    {
        // Create request object
        $request = new Request($this->checkApi);

        // Fill it up
        $request->bind([
            'rtlo' => $this->outletId,
            'trxid' => $this->transactionId,
            'checksum' => md5($this->transactionId . $this->outletId . $this->salt),
            'test' => (int)$this->test
        ]);

        // Run check
        $httpResponse = $request->execute();

        // Make check response object and return it
        return $this->parseCheckResponse($httpResponse);
    }

    /**
     * Get method code
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set the amount, as of now it is always in cents [start]
     * @param int $amount
     * @return $this
     */
    public function amount($amount)
    {
        $this->amount = round($amount);

        return $this;
    }

    /**
     * Set the app ID [start, check]
     * @param string $appId
     * @return $this
     */
    public function appId($appId)
    {
        $this->appId = strtolower(preg_replace('/[^a-z\d_]/i', '', $appId));

        return $this;
    }

    /**
     * Set the currency. See documentation for available currencies [start]
     * @param string $currency
     * @return $this
     */
    public function currency($currency)
    {
        if (in_array($currency, $this->currencies)) {
            $this->currency = $currency;
        }

        return $this;
    }

    /**
     * Set description for on the banking statement [start]
     * @param string $description
     * @return $this
     */
    public function description($description)
    {
        $this->description = substr($description, 0, 32);

        return $this;
    }

    /**
     * Set the language [start]
     * @param string $language
     * @return $this
     */
    public function language($language)
    {
        if (in_array($language, $this->languages, true)) {
            $this->language = $language;
        }

        return $this;
    }

    /**
     * Set the report URL [start]
     * @param string $reportUrl
     * @return $this
     */
    public function reportUrl($reportUrl)
    {
        if (preg_match('|(\w+)://([^/:]+)(:\d+)?(.*)|', $reportUrl)) {
            $this->reportUrl = $reportUrl;
        }

        return $this;
    }

    /**
     * Set the return URL [start]
     * @param string $returnUrl
     * @return $this
     */
    public function returnUrl($returnUrl)
    {
        if (preg_match('|(\w+)://([^/:]+)(:\d+)?(.*)|', $returnUrl)) {
            $this->returnUrl = $returnUrl;
        }

        return $this;
    }

    /**
     * Set the cancel URL [start]
     * @param string $cancelUrl
     * @return $this
     */
    public function cancelUrl($cancelUrl)
    {
        if (preg_match('|(\w+)://([^/:]+)(:\d+)?(.*)|', $cancelUrl)) {
            $this->cancelUrl = $cancelUrl;
        }

        return $this;
    }

    /**
     * Set the outletId (layoutcode, rtlo) [start, check]
     * @param int $outletId
     * @return $this
     */
    public function outletId($outletId)
    {
        $this->outletId = $outletId;

        return $this;
    }

    /**
     * Set transaction ID [check]
     * @param string $transactionId
     * @return $this
     */
    public function transactionId($transactionId)
    {
        $this->transactionId = substr($transactionId, 0, 32);

        return $this;
    }

    /**
     * Set test-mode [start, check]
     * @param $test
     * @return $this
     */
    public function test($test) {
        if (is_bool($test)) {
            $this->test = $test;
        }

        return $this;
    }

    /**
     * Provide static instance of a payment model based on its class name
     * @param string $method Class name of the method, e.g. Ideal
     * @return Bancontact|Creditcard|Ideal|Paysafecard|Sofort|Paypal|Afterpay
     */
    public static function model($method)
    {
        $class = '\\DigiWallet\\Methods\\' . $method;

        return new $class;
    }
}