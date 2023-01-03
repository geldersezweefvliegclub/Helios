<?php

require __DIR__ . '/../ext/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

include "config.php";
include "functions.php";

$datumYMD = date('Y-m-d');
$url_args = "DATUM=$datumYMD&VELDEN=ID,DDWV";
heliosInit("Rooster/GetObject?" . $url_args);

$result = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    emailError($result);
    die;
}
else {
    $rooster = json_decode($body, true);
    if ($rooster['DDWV'] == false) {
        die;        // het is geen DDWV dag, dus stoppen we hier
    }
    $hash = sha1($body);

    $url_args = "DATUM=$datumYMD&HASH=$hash";
    heliosInit("DDWV/UitbetalenCrew?" . $url_args);

    $result = curl_exec($curl_session);
    $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
    list($header, $body) = returnHeaderBody($result);

    if ($status_code != 200) // We verwachten een status code van 200
    {
        emailError($result);
        die;
    }
}

