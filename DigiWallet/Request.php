<?php

namespace DigiWallet;

/**
 * DigiWallet transaction SDK - HTTP request
 *
 * @author DigiWallet B.V.
 *
 * @property $debug bool Debug mode, enable to see exact requests
 * @property $url string Location to make the request to
 * @property $method string HTTP Method (GET, POST, etc...)
 * @property $parameters array Query or post string params
 */
class Request
{

    protected $debug = false;
    protected $url = null;
    protected $method = "GET";
    protected $parameters = [];

    /**
     * Constructor, set URL and method
     * @param string $url URL to call
     * @param string $method Method to use, GET or POST
     */
    public function __construct($url, $method = "GET")
    {
        $this->url = $url;
        $this->method = $method;
    }

    /**
     * Bind parameter or a array of parameters
     *
     * @param mixed $fields Fieldname or array
     * @param mixed $value Its new value (may be omitted in use of array)
     * @return $this
     */
    public function bind($fields, $value = null)
    {
        if (is_array($fields)) {
            foreach ($fields as $field => $value) {
                $this->parameters[$field] = $value;
            }
        } else {
            $this->parameters[$fields] = $value;
        }
        return $this;
    }

    /**
     *  Prepare query string
     * @param string $url URL to append to
     * @return string URL with parameters attached
     */
    private function addQueryString($url)
    {
        if (!$this->parameters) {
            return $url;
        }

        $queryString = "";
        foreach ($this->parameters as $key => $value) {
            $queryString .= "&" . $key . "=" . urlencode($value);
        }

        if ($this->debug) {
            var_dump($this->parameters);
        }

        return $url . "?" . substr($queryString, 1);
    }

    /**
     * Call the URL
     * @return string Raw HTTP response
     */
    public function execute()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->addQueryString($this->url));
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($this->method == "POST") {
            curl_setopt($ch, CURLOPT_POST, 1);
        }

        $httpResult = curl_exec($ch);
        curl_close($ch);

        return $httpResult;
    }
}

