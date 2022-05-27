<?php

include "config.php";
require __DIR__ . '/../ext/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

include('../include/GoogleAuthenticator.php');


$curl_session;

//======================================================================================
// Algemene functies 

function heliosInit($url, $http_method = "GET")
{
    global $curl_session;
    global $helios_settings;

    if (isset($curl_session))
    {
        curl_setopt($curl_session, CURLOPT_USERPWD, null);  // basic auth niet meer nodig, gebruik vanaf nu php session cookie
    }
    else
    {
        // inloggen
        $cookieFile = uniqid();

        // init curl sessie
        $curl_session = curl_init();
        
        curl_setopt($curl_session, CURLOPT_TIMEOUT, 10);  
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl_session, CURLOPT_HEADER, true);      // curl response bevat header info
    
        curl_setopt ($curl_session, CURLOPT_COOKIEJAR, "/tmp/$cookieFile"); 
        curl_setopt ($curl_session, CURLOPT_COOKIEFILE, "/tmp/$cookieFile"); 

        curl_setopt($curl_session, CURLOPT_USERPWD, $helios_settings['username'] . ":" . $helios_settings['password']);  // basic auth

        if (isset($helios_settings['secret'])) 
        {
            $ga = new PHPGangsta_GoogleAuthenticator();
            $urlToken = $ga->getCode($helios_settings['secret']);

            $loginUrl = $helios_settings['url'] . "/Login/Login?token=" . $urlToken;
        }
        else 
        {
            $loginUrl = $helios_settings['url'] . "/Login/Login";
        }
        curl_setopt($curl_session, CURLOPT_URL, $loginUrl);
        curl_setopt($curl_session , CURLOPT_CUSTOMREQUEST, $http_method); 
        $result = curl_exec($curl_session);
        $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
        list($header, $body) = returnHeaderBody($result);

        if ($status_code == 200)
        {
            heliosInit($url, $http_method);
        }
    }
    $full_url = sprintf("%s/%s", $helios_settings['url'], $url);

    curl_setopt($curl_session, CURLOPT_URL, $full_url);
    curl_setopt($curl_session , CURLOPT_CUSTOMREQUEST, $http_method); 
}

function emailInit() 
{
    global $smtp_settings;

    $mail = new PHPMailer(true);

    try {
        //Server settings
  //      $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = $smtp_settings['smtphost'];             //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = $smtp_settings['smtpuser'];             //SMTP username
        $mail->Password   = $smtp_settings['smtppass'];             //SMTP password
        $mail->SMTPSecure = $smtp_settings['smtpsecure'];         	//Enable implicit TLS encryption
        $mail->Port       = $smtp_settings['smtpport'];             //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
    
        return $mail;
    } catch (Exception $e) {
        echo $mail->ErrorInfo;
    }
    return null;
}

function returnHeaderBody($response)
{
    global $curl_session;

    // extract header
    $headerSize = curl_getinfo($curl_session, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $headerSize);
    $header = getHeaders($header);

    // extract body
    $body = substr($response, $headerSize);    
    return [$header, $body];    
}

// Zet de headers in een array
function getHeaders($respHeaders) {
    global $cookies;

    $headers = array();
    $headerText = substr($respHeaders, 0, strpos($respHeaders, "\r\n\r\n"));

    foreach (explode("\r\n", $headerText) as $i => $line) {
        if ($i === 0) {
            $headers['http_code'] = $line;
        } else {
            list ($key, $value) = explode(': ', $line);

            $headers[$key] = $value;
        }
    }
    return $headers;
}