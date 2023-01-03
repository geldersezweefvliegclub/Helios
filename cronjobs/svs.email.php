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
    Beste SVS beheerder,
</p>
<p>
In onze startadministratie staan op <b>%s</b> onderstaande sleepvluchten genoteerd. U wordt verzocht om onderstaande vluchten te controleren op juistheid en volledigheid. Via de startadmin@gezc.org of ddwv@gezc.org kunt meer informatie verkrijgen.
</p>

<table style='white-space: nowrap; margin-left: 10px; margin-right: 10px;font-family: Arial, Helvetica, sans-serif; font-size:12px;'>
    <thead>
        <tr>
            <th> Datum </th>
            <th> Vliegtuig </th>
            <th> Vliegveld </th>
            <th> Start methode </th>
            <th> Sleep vliegtuig </th>
            <th> Sleep hoogte </th>
            <th> Vlieger </th>
            <th> Inzittende </th>
            <th> Starttijd </th>
            <th> Opmerkingen </th>
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
heliosInit("Startlijst/GetObjects?" . $url_args);

$result      = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    emailError($result);
    die;
}
else
{
    $startlijst = json_decode($body, true);
   
    if (count($startlijst['dataset']) == 0)
    {
        // er zijn geen starts ingevoerd
        die;
    }

    $startRegels = "";
    foreach ($startlijst['dataset'] as $start)
    {
        if ($start['STARTMETHODE_ID'] != 501)
        {
            // Het is geen sleepstart
            continue;
        }
        
        if ($start['VELD_ID'] != 901)
        {
            // Er is niet gestart vanaf Terlet
            continue;
        }

        $d = explode("-", $start['DATUM']);
        $startRegels .= sprintf("
            <tr>
                <td>%02d-%02d-%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
            </tr>",
            $d[2]*1, $d[1]*1, $d[0],
            $start['REG_CALL'],
            $start['VELD'],
            $start['STARTMETHODE'],
            $start['SLEEPKIST'],
            $start['SLEEP_HOOGTE'],
            $start['VLIEGERNAAM_LID'],
            $start['INZITTENDENAAM_LID'],
            $start['STARTTIJD'],
            $start['OPMERKINGEN']);
    }

    if ($startRegels == "")
    {
        // Er zijn geen starts
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
    
    $body = sprintf($htmlContent, $datumString, $startRegels);
        
    // email 
    $mail = emailInit();

    $mail->Subject = 'Startlijst ' . $datumString;
    $mail->isHTML(true);                                  		//Set email format to HTML
    $mail->Body    = $body;

    $mail->addAddress($slepen['EMAIL'], $slepen['NAAM']);
    $mail->addReplyTo($smtp_settings['from'], $smtp_settings['name']);
    $mail->SetFrom($smtp_settings['from'], $smtp_settings['name']);

    if(!$mail->Send()) {
        print_r($mail);
    }
}

?>
