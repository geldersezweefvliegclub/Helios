<?php

require __DIR__ . '/../ext/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

include "config.php";
include "functions.php";

$htmlContent = "
<html>
<body style='font-family: Arial, Helvetica, sans-serif; font-size:12px;'>

<p>
    Beste DDWV beheerder,
</p>
<p>
    We hebben voor vandaag %s, een aantal DDWV transacties geregisteerd. Deze registraties zijn:
</p>

<table style='white-space: nowrap; margin-left: 10px; margin-right: 10px;font-family: Arial, Helvetica, sans-serif; font-size:12px;'>
    <thead>
        <tr>
            <th> Naam </th>
            <th> Omschrijving</th>
            <th> Bedrag </th>
            <th> Referentie </th>
            <th> Saldo voor </th>
            <th> Strippen </th>
            <th> Saldo na </th>
            <th> Ingevoerd door </th>
        </tr>
    </thead>
    %s
</table>

<p> 
    Met vriendelijke groet,
</p>
<p> 
    De startadministratie
</p>
</body></html>";

$datum = date('Y-m-d');
$url_args = "BEGIN_DATUM=$datum&EIND_DATUM=$datum";
heliosInit("Transacties/GetObjects?" . $url_args);

$result      = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    // email naar beheerder
    $mail = emailInit();

    $mail->Subject = "Helios API call mislukt: $status_code";
    $mail->Body    = "Startlijst/GetObjects?" . $url_args . "\n";
    $mail->Body   .= "HEADER :\n";
    $mail->Body   .= print_r($header, true);
    $mail->Body   .= "\n";
    $mail->Body   .= "BODY :\n" . $body;

    $mail->addAddress($smtp_settings['from'], $smtp_settings['name']);
    $mail->addReplyTo($smtp_settings['from'], $smtp_settings['name']);
    if(!$mail->Send()) {
        print_r($mail);
    }
}
else
{
    $transacties = json_decode($body, true);
    if (count($transacties['dataset']) == 0)
    {
        // er zijn geen starts ingevoerd
        die;
    }

    switch (date('w')) {
        case 0; $datumString = "zondag "; break;
        case 1; $datumString = "maandag "; break;
        case 2; $datumString = "dinsdag "; break;
        case 3; $datumString = "woensdag "; break;
        case 4; $datumString = "donderdag "; break;
        case 5; $datumString = "vrijdag "; break;
        case 6; $datumString = "zaterdag "; break;
    }

    $datumString .= date('d-m-Y');
    $transactieRegels = "";

    foreach ($transacties['dataset'] as $transactie)
    {
        $transactieRegels .= sprintf("
            <tr>
                <td>%s</td>
                <td>%s, %s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
            </tr>",

            $transactie['NAAM'],
            $transactie['TYPE'], $transactie['OMSCHRIJVING'],
            $transactie['BEDRAG'],
            $transactie['SALDO_VOOR'],
            $transactie['EENHEDEN'],
            $transactie['SALDO_NA'],
            $transactie['INGEVOERD']);
    }

    $mail = emailInit();
    $mail->addAddress($ddwv['EMAIL'], $ddwv['NAAM']);
    $body = sprintf($htmlContent, $datumString, $transactieRegels);

    $mail->Subject = 'Transacties ' . $datumString;
    $mail->isHTML(true);                                  		//Set email format to HTML
    $mail->Body    = $body;

    $mail->addReplyTo($smtp_settings['from'], $smtp_settings['name']);
    $mail->SetFrom($smtp_settings['from'], $smtp_settings['name']);

    if(!$mail->Send()) {
        print_r($mail);
    }
}
