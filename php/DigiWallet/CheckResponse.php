<?php

namespace DigiWallet;

/**
 * DigiWallet transaction SDK - Response of a payment check
 *
 * @author DigiWallet B.V.
 *
 * @property $status boolean Whether the transaction succeeded or not
 * @property $error string In case of unsuccessful transaction, contains the error message
 */
class CheckResponse
{
    public $status;
    public $error;

    /**
     * Constructor, fill object based on array
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        foreach ($values as $property => $value) {
            $this->$property = $value;
        }
    }
}

