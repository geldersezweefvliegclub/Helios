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
    Beste %s,
</p>
<p>
    Je hebt vandaag %s de dienst %s gedaan. Namens alle vliegers, bedankt voor je inzet.   
</p>

<p>
    Om de dag helemaal af te sluiten zou het fijn zijn als je nog even tijd kunt vrij maken voor het schrijven van een dagrapport. 
    Klik <a href='https://mijn.gezc.org/daginfo'>hier</a> om direct te beginnen.
</p>

<p> 
    Mocht je al een dagrapport geschreven hebben, dan mag je deze mail weggooien.  
</p>

<p> 
    Fijne avond en met vriendelijke groet,
</p>
<p>
    Het CI-MT
</p>
</body></html>";

$datum = Date('Y-m-d');
$url_args = "DATUM=$datum";

heliosInit("Rooster/GetObject?" . $url_args);

$result      = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    emailError($result);
    die;
}
$rooster = json_decode($body, true);
if (!$rooster['CLUB_BEDRIJF'] && !$rooster['DDWV'])
    die;        // het is geen clubdag en geen DDWV dag

heliosInit("Diensten/GetObjects?" . $url_args);

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
    $diensten = json_decode($body, true);
    if (count($diensten['dataset']) == 0)
    {
        // er zijn geen diensten ingevoerd
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

    foreach ($diensten['dataset'] as $dienst)
    {
        if ($rooster['CLUB_BEDRIJF'])
        {
            if (($dienst['TYPE_DIENST_ID'] != 1800) &&  // 1800 = Ochtend DDI
                ($dienst['TYPE_DIENST_ID'] != 1801) &&  // 1801 = Ochtend instructeur
                ($dienst['TYPE_DIENST_ID'] != 1805) &&  // 1805 = Middag DDI
                ($dienst['TYPE_DIENST_ID'] != 1806))    // 1806 = Middag instructeur
            {
                continue;
            }
        }
        else if ($rooster['DDWV'])
        {
            if ($dienst['TYPE_DIENST_ID'] != 1800)  // 1804 = Ochtend Startleider
                continue;
        }

        $url_args = "ID=" . $dienst['LID_ID'];
        heliosInit("Leden/GetObject?" . $url_args);

        $result      = curl_exec($curl_session);
        $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
        list($header, $body) = returnHeaderBody($result);

        $lid = json_decode($body, true);

        $mail = emailInit();
        $body = sprintf($htmlContent, $lid['VOORNAAM'], $datumString, $dienst['TYPE_DIENST']);

        $mail->Subject = 'Je dienst voor ' . $datumString;
        $mail->isHTML(true);                                  		//Set email format to HTML
        $mail->Body    = $body;

        $mail->addAddress($lid['EMAIL'], $lid['NAAM']);

        $mail->addReplyTo($smtp_settings['from'], $smtp_settings['name']);
        $mail->SetFrom($smtp_settings['from'], $smtp_settings['name']);

        echo $lid['EMAIL'] . "<br>";

        if($mail->Send()) {
            echo sprintf("%s: %s [%s]\n", $dienst['TYPE_DIENST'], $lid['NAAM'], $lid['EMAIL']);
        }
        else  {
            print_r($mail);
        }

    }
}
