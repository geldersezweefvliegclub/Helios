<?php

require __DIR__ . '/../ext/vendor/autoload.php';

include "config.php";
include "functions.php";

$GLOBALS['DBCONFIG_PHP_INCLUDED'] = true;
include "../include/config.php";


$smsContent = "Beste %s,

Morgen %s staat voor jouw de dienst %s ingeroosterd. 
Deze SMS sturen we als herinnering.

Tot morgen";

$datum = Date('Y-m-d', strtotime('+1 days'));
$url_args = "DATUM=" . $datum;


heliosInit("Rooster/GetObject?" . $url_args);

$result      = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    if ($status_code == 404) 
        die;        // er is geen rooster

    emailError($result);
    die;
}
$rooster = json_decode($body, true);

// We sturen alleen SMS als het een clubdag is
if (!$rooster['CLUB_BEDRIJF']) 
    die;

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
    
    switch (date('w', strtotime('+1 days'))) {
        case 0; $datumString = "zondag "; break;
        case 1; $datumString = "maandag "; break;
        case 2; $datumString = "dinsdag "; break;
        case 3; $datumString = "woensdag "; break;
        case 4; $datumString = "donderdag "; break;
        case 5; $datumString = "vrijdag "; break;
        case 6; $datumString = "zaterdag "; break;
    }

    $datumString .= date('d-m-Y', strtotime('+1 days'));

    echo "Herinnering SMS gestuurd voor " . $datumString . "\n";

    foreach ($diensten['dataset'] as $dienst)
    {
        $url_args = "ID=" . $dienst['LID_ID'];
        heliosInit("Leden/GetObject?" . $url_args);

        $result      = curl_exec($curl_session);
        $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
        list($header, $body) = returnHeaderBody($result);

        $lid = json_decode($body, true);

        $MessageBird = new \MessageBird\Client($app_settings['ApiKeySMS']);
        $Message = new \MessageBird\Objects\Message();
        $Message->originator = $app_settings['Vereniging'];
        $Message->recipients = array($lid['MOBIEL']);
        $Message->body = sprintf($smsContent, $lid['VOORNAAM'], $datumString, $dienst['TYPE_DIENST']);
        
        $reponse = $MessageBird->messages->create($Message);
        echo sprintf("%s: %s [%s]\n", $dienst['TYPE_DIENST'], $lid['NAAM'], $lid['MOBIEL']);
    }
}
