<?php

require __DIR__ . '/../ext/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

include "config.php";
include "functions.php";

$ddwvIntro = "Het betreft een DDWV dag, vandaar dat u dit overzicht krijgt.";
$combiIntro = "Het beftreft een gecombineerde DDWV dag, met gelijktijdig een club bedrijf. In onderstaand overzicht ziet u alleen de vluchten die door DDWV leden zijn gemaakt";

$htmlContent = "
<html>
<body style='font-family: Arial, Helvetica, sans-serif; font-size:12px;'>

<p>
    Beste DDWV beheerder,
</p>
<p>
In onze startadministratie staan op <b>%s</b> onderstaande vluchten genoteerd. %s. U wordt verzocht om onderstaande vluchten te controleren op juistheid en volledigheid. Via de start administratie kunt u de informatie aanpassen.
</p>

<table style='white-space: nowrap; margin-left: 10px; margin-right: 10px;font-family: Arial, Helvetica, sans-serif; font-size:12px;'>
    <thead>
        <tr>
            <th> Datum </th>
            <th> Vliegtuig </th>
            <th> Vliegveld </th>
            <th> Start methode </th>
            <th> Sleep hoogte </th>
            <th> Vlieger </th>
            <th> Inzittende </th>
            <th> Starttijd </th>
            <th> Landingstijd </th>
            <th> Duur </th>
            <th> Opmerkingen </th>
        </tr>
    </thead>
    %s
</table>
<a href='https://mijn.gezc.org/' 
    style='padding: 8px 12px;
        border: 1px solid #d6c84d;
        border-radius: 2px;
        font-family: Helvetica, Arial, sans-serif;
        font-size: 14px;
        color: black; 
        text-decoration: none;
        font-weight: bold;
        display: inline-block;'>Open website</a>

<p> 
    Met vriendelijke groet,
</p>
<p> 
    De startadministratie
</p>
</body></html>";

$datum = date('Y-m-d');
$url_args = "DATUM=$datum&VELDEN=DDWV,CLUB_BEDRIJF";
heliosInit("Daginfo/GetObject?" . $url_args);

$result      = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    // email naar beheerder
    $mail = emailInit();

    $mail->Subject = 'Helios API call mislukt';
    $mail->Body    = "DagInfo/GetObjects?" . $url_args . "\n";
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
    $daginfo = json_decode($body, true);
    if ($daginfo['DDWV'] == false) 
    {
        die;        // het is geen DDWV dag, dus stoppen we hier
    }

    $url_args = "BEGIN_DATUM=$datum&EIND_DATUM=$datum";
    heliosInit("Startlijst/GetObjects?" . $url_args);

    $result      = curl_exec($curl_session);
    $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
    list($header, $body) = returnHeaderBody($result);

    $startlijst = json_decode($body, true);
   
    if (count($startlijst['dataset']) == 0)
    {
        // er zijn geen starts ingevoerd
        die;
    }

    $logboekRegels = "";
    foreach ($startlijst['dataset'] as $start)
    {
        if ($daginfo['CLUB_BEDRIJF'] == true)
        {
            if ($start['VLIEGER_LIDTYPE_ID'] != 625)
            {
                // De vlieger is geen DDWV'er, dus niet opnemen omdat het een gecombineerd bedrijf is.
                continue;
            }
        }

        $d = explode("-", $start['DATUM']);
        $logboekRegels .= sprintf("
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
                <td>%s</td>
            </tr>",
            $d[2]*1, $d[1]*1, $d[0],
            $start['REG_CALL'],
            $start['VELD'],
            $start['STARTMETHODE'],
            $start['SLEEP_HOOGTE'],
            $start['VLIEGERNAAM_LID'],
            $start['INZITTENDENAAM_LID'],
            $start['STARTTIJD'],
            $start['LANDINGSTIJD'],
            $start['DUUR'],
            $start['OPMERKINGEN']);
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
    
    $intro = ($daginfo['CLUB_BEDRIJF'] == true)  ? $combiIntro : $ddwvIntro;
    $body = sprintf($htmlContent, $datumString, $intro, $logboekRegels);
        
    // email 
    $mail = emailInit();

    $mail->Subject = 'Startlijst ' . $datumString;
    $mail->isHTML(true);                                  		//Set email format to HTML
    $mail->Body    = $body;

    $mail->addAddress($ddwv['EMAIL'], $ddwv['NAAM']);
    $mail->addReplyTo($smtp_settings['from'], $smtp_settings['name']);
    $mail->SetFrom($smtp_settings['from'], $smtp_settings['name']);

    if(!$mail->Send()) {
        print_r($mail);
    }
}

?>