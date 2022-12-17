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
    // email naar beheerder
    $mail = emailInit();

    $mail->Subject = "Helios API call mislukt: $status_code";
    $mail->Body = "Rooster/GetObjects?" . $url_args . "\n";
    $mail->Body .= "HEADER :\n";
    $mail->Body .= print_r($header, true);
    $mail->Body .= "\n";
    $mail->Body .= "BODY :\n" . $body;

    $mail->addAddress($smtp_settings['from'], $smtp_settings['name']);
    $mail->addReplyTo($smtp_settings['from'], $smtp_settings['name']);
    if (!$mail->Send()) {
        print_r($mail);
    }
} else {
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
        // email naar beheerder
        $mail = emailInit();

        $mail->Subject = "Helios API call mislukt: $status_code";
        $mail->Body = "DDWV/UitbetalenCrew?" . $url_args . "\n";
        $mail->Body .= "HEADER :\n";
        $mail->Body .= print_r($header, true);
        $mail->Body .= "\n";
        $mail->Body .= "BODY :\n" . $body;

        $mail->addAddress($smtp_settings['from'], $smtp_settings['name']);
        $mail->addReplyTo($smtp_settings['from'], $smtp_settings['name']);
        if (!$mail->Send()) {
            print_r($mail);
        }
    }
}

