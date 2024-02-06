<?php

namespace DigiWallet;

/**
 * DigiWallet transaction SDK - Response of a start payment
 *
 * @author DigiWallet B.V.
 * @release 26-10-2014
 *
 * @property $status bool Whether the request was successful or not
 * @property $url string URL for redirect if successful AND applicable => either URL or payinfo are filled
 * @property $payinfo string Payment info to display => either URL or payinfo are filled
 * @property $error string Error message, if unsuccessful
 */
class StartResponse
{
    public $status;
    public $url;
    public $payinfo;
    public $error;

    /**
     *  Constructor, fill object based on array
     * @param array|null $values
     */
    public function __construct($values = null)
    {
        if (isset($values) && is_array($values)) {
            foreach ($values as $property => $value) {
                $this->$property = $value;
            }
        }
    }
}

