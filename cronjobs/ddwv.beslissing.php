<?php

require __DIR__ . '/../ext/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

include "config.php";
include "functions.php";

$htmlContentSlepen = "
<html>
<body style='font-family: Arial, Helvetica, sans-serif; font-size:12px;'>

<p>
    Beste %s,
</p>
<p>
Je hebt jezelf ingeschreven voor de vliegdag van morgen, %s. Er zijn onvoldoende inschrijvingen voor een lierbedrijf. Bij een klein vliegbedrijf gaan we over op een <b>sleepbedrijf</b>.
</p>

<p>
Mocht je onverhoopt niet gaan vliegen, dan hoef je je inschrijving niet te annuleren. Je zult wel een rekening ontvangen om de kosten van de veldleider te dekken.
</p>

<p>
Wij wensen je een fijne en veilige vliegdag toe..
</p>

<p> 
    Met vriendelijke groet,
</p>
<p> 
    De DDWV beheerder
</p>
</body></html>";

$htmlContentLieren = "
<html>
<body style='font-family: Arial, Helvetica, sans-serif; font-size:12px;'>

<p>
    Beste %s,
</p>
<p>
Je hebt jezelf ingeschreven voor de vliegdag van morgen, %s. Er zijn voldoende inschrijvingen voor een <b>lierbedrijf</b>.
</p>

<p>
Mocht je onverhoopt niet gaan vliegen, dan hoef je je inschrijving niet te annuleren. Je zult wel een rekening ontvangen om de kosten van de veldleider te dekken.
</p>

<p>
Wij wensen je een fijne en veilige vliegdag toe..
</p>

<p> 
    Met vriendelijke groet,
</p>
<p> 
    De DDWV beheerder
</p>
</body></html>";

$htmlContentAnnuleren = "
<html>
<body style='font-family: Arial, Helvetica, sans-serif; font-size:12px;'>

<p>
    Beste %s,
</p>
<p>
Je hebt jezelf ingeschreven voor de vliegdag van morgen, %s. Er zijn helaas <b>onvoldoende</b> inschrijvingen om de vliegdag te laten doorgaan.
</p>

<p>
Wij hopen dat we op een later moment alsnog een vliegdag voor je te kunnen organiseren
</p>

<p> 
    Met vriendelijke groet,
</p>
<p> 
    De DDWV beheerder
</p>
</body></html>";

$htmlContentClub = "
<html>
<body style='font-family: Arial, Helvetica, sans-serif; font-size:12px;'>

<p>
    Beste %s,
</p>
<p>
Je hebt jezelf ingeschreven voor de vliegdag van morgen, %s. De vliegdag voor mogen is een gecombineerde dag van DDWV en het GeZC clubbedrijf. 
</p>

<p> 
    Met vriendelijke groet,
</p>
<p> 
    De DDWV beheerder
</p>
</body></html>";

$htmlContentCrew = "
<html>
<body style='font-family: Arial, Helvetica, sans-serif; font-size:12px;'>

<p>
    Beste %s,
</p>
<p>
Je staat voor morgen %s op het DDWV rooster. %s 
</p>

<p> 
    Met vriendelijke groet,
</p>
<p> 
    De DDWV beheerder
</p>
</body></html>";

$morgen = new DateTime('tomorrow');
$morgenYMD = $morgen->format('Y-m-d');
$url_args = "DATUM=$morgenYMD&VELDEN=ID,DDWV";
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

    $url_args = "VELDEN=VOORNAAM,NAAM,EMAIL,LIDTYPE_ID,LIDTYPE&BEGIN_DATUM=$morgenYMD&EIND_DATUM=$morgenYMD";
    heliosInit("AanwezigLeden/GetObjects?" . $url_args);

    $result = curl_exec($curl_session);
    $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
    list($header, $body) = returnHeaderBody($result);

    if ($status_code != 200) // We verwachten een status code van 200
    {
        emailError($result);
        die;
    }
    else {
        $aanmeldingen = json_decode($body, true);

        $url_args = "DATUM=$morgenYMD&HASH=$hash";
        heliosInit("DDWV/ToetsingDDWV?" . $url_args);

        $result = curl_exec($curl_session);
        $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code

        if ($status_code != 200) // We verwachten een status code van 200
        {
            emailError(json_decode($result));
            die;
        }
        list($header, $typeBedrijf) = returnHeaderBody($result);

        echo "Type bedrijf :" . $typeBedrijf . "<br>";
        emailVliegers(json_decode($typeBedrijf), $aanmeldingen['dataset']);
        emailCrew(json_decode($typeBedrijf));
    }
}

function emailVliegers($typeBedrijf, $leden)
{
    global $smtp_settings;
    global $htmlContentClub;
    global $htmlContentSlepen;
    global $htmlContentLieren;
    global $htmlContentAnnuleren;

    $morgen = new DateTime('tomorrow');
    $datumString = dagVanDeWeek($morgen) . " " . $morgen->format('d-m-Y');

    foreach ($leden as $lid) {
        $mail = emailInit();
        $mail->addAddress($lid['EMAIL'], $lid['NAAM']);

        $body = "";
        switch ($typeBedrijf) {
            case "club": $body = sprintf($htmlContentClub, $lid['VOORNAAM'], $datumString); break;
            case "lieren": $body = sprintf($htmlContentLieren, $lid['VOORNAAM'], $datumString); break;
            case "slepen": $body = sprintf($htmlContentSlepen, $lid['VOORNAAM'], $datumString); break;
            case "annuleren":$body = sprintf($htmlContentAnnuleren, $lid['VOORNAAM'], $datumString); break;
        }

        if (($typeBedrijf == 'club') && ($lid['LIDTYPE'] != 625)) // leden hoeven op een clubdag geen mail te ontvangen
            continue;

        $mail->Subject = 'DDWV Vliegdag ' . $datumString;
        $mail->isHTML(true);                                  		//Set email format to HTML
        $mail->Body    = $body;

        $mail->addReplyTo($smtp_settings['from'], $smtp_settings['name']);
        $mail->SetFrom($smtp_settings['from'], $smtp_settings['name']);

        if(!$mail->Send()) {
            print_r($mail);
        }
    }
}

function emailCrew($typeBedrijf)
{
    global $curl_session;
    global $smtp_settings;
    global $htmlContentCrew;

    if ($typeBedrijf == "club") {
        return;     // op clubdagen hoeven de DDWV crew geen mail te sturen
    }
    $morgen = new DateTime('tomorrow');
    $url_args = "DATUM=" . $morgen->format('Y-m-d');

    heliosInit("Diensten/GetObjects?" . $url_args);

    $result      = curl_exec($curl_session);
    $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
    list($header, $body) = returnHeaderBody($result);

    if ($status_code != 200) // We verwachten een status code van 200
    {
        emailError($result);
        return;
    }
    else {
        $diensten = json_decode($body, true);
        if (count($diensten['dataset']) == 0) {
            // er zijn geen diensten ingevoerd
            return;
        }

        $datumString = dagVanDeWeek($morgen) . " " . $morgen->format('d-m-Y');

        $bericht = "";
        switch ($typeBedrijf) {
            case 'lieren':
                $bericht = "Op basis van het aantal aanmeldingen gaan we een <b>lierbedrijf</b> opzetten.";
                break;
            case 'slepen':
                $bericht = "Op basis van het aantal aanmeldingen beperken we de DDWV dag tot een <b>sleepbedrijf</b>.";
                break;
            case 'annuleren':
                $bericht = "Helaas zijn er onvoldoende aanmeldingen en zijn we genoodzaakt de DDWV dag te <b>annuleren</b>.";
                break;
            default:
                echo $typeBedrijf . " is onbekend <br>";
        }

        foreach ($diensten['dataset'] as $dienst) {
            $url_args = "ID=" . $dienst['LID_ID'];
            heliosInit("Leden/GetObject?" . $url_args);

            $result = curl_exec($curl_session);
            $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code

            if ($status_code != 200) // We verwachten een status code van 200
            {
                emailError($result);
                continue;
            }
            list($header, $body) = returnHeaderBody($result);

            $lid = json_decode($body, true);
            $emailBody = sprintf($htmlContentCrew, $lid['VOORNAAM'], $datumString, $bericht);

            $mail = emailInit();
            $mail->Subject = 'Je dienst voor ' . $datumString;
            $mail->isHTML(true);                                        //Set email format to HTML
            $mail->Body = $emailBody;

            $mail->addAddress($lid['EMAIL'], $lid['NAAM']);

            $mail->addReplyTo($smtp_settings['from'], $smtp_settings['name']);
            $mail->SetFrom($smtp_settings['from'], $smtp_settings['name']);

            echo "Crew:" . $lid['EMAIL'] . "<br>";

            if ($mail->Send()) {
                echo sprintf("%s: %s [%s]\n", $dienst['TYPE_DIENST'], $lid['NAAM'], $lid['EMAIL']);
            } else {
                print_r($mail);
            }
        }
    }
}

function dagVanDeWeek($datum)
{
    switch ($datum->format('w')) {
        case 0;
            return "zondag ";
        case 1;
            return "maandag ";
        case 2;
            return "dinsdag ";
        case 3;
            return "woensdag ";
        case 4;
            return "donderdag ";
        case 5;
            return "vrijdag ";
        case 6;
            return "zaterdag ";
    }
    return "?";
}
