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
    In onze startadministratie staan op <b>%s</b> voor jou onderstaande vluchten genoteerd. Deze vluchten staan ook in je digitale logboek.

    We vragen aan je om te controleren of deze informatie correct en volledig is. Mocht dat niet het geval zijn, dan kan je via de website de vluchten aanpassen. Doe dit tijdig, na verloop van tijd is het niet meer mogelijk om zelf wijzigingen door te voeren.  
</p>

<table style='white-space: nowrap; margin-left: 10px; margin-right: 10px;font-family: Arial, Helvetica, sans-serif; font-size:12px;'>
    <thead>
        <tr>
            <th> Datum </th>
            <th> Vliegtuig </th>
            <th> Vliegveld </th>
            <th> Start methode </th>
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
    Het correct bijhouden van het logboek is een verantwoordelijkheid van de vlieger. Aanpassingen die je zelf invoert worden ter controle opgeslagen en kunnen gebruikt worden voor audits.
    </p>
    <p> 
    Indien je vragen hebt, mag je contact opnemen met startadmin@gezc.org. Doe dit door te reageren op deze email. We hebben dan meteen de juiste informatie beschikbaar. 
</p>
<p> 
    Met vriendelijke groet,
</p>
<p> 
    De startadministratie
</p>
</body></html>";


$datum = date('Y-m-d');
$url_args = "SORT=VLIEGER_ID,STARTTIJD&BEGIN_DATUM=$datum&EIND_DATUM=$datum&VELDEN=VLIEGER_ID,INZITTENDE_ID,STARTTIJD";
heliosInit("Startlijst/GetObjects?" . $url_args);

$result      = curl_exec($curl_session);
$status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
list($header, $body) = returnHeaderBody($result);

if ($status_code != 200) // We verwachten een status code van 200
{
    // email naar beheerder
    $mail = emailInit();

    $mail->Subject = 'Helios API call mislukt';
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
    $startlijst = json_decode($body, true);
    if (count($startlijst['dataset']) == 0)
    {
        // er zijn geen starts ingevoerd
        die;
    }
    
    $aanwezig = array();

    foreach ($startlijst['dataset'] as $start)
    {
        if ($start['STARTTIJD'] == null) continue;
        if ($start['VLIEGER_ID'] == null) continue;

        if (!in_array($start['VLIEGER_ID'], $aanwezig))
            array_push($aanwezig, $start['VLIEGER_ID']);
    
        if (($start['INZITTENDE_ID'] != null) && (!in_array($start['INZITTENDE_ID'], $aanwezig)))
            array_push($aanwezig, $start['INZITTENDE_ID']);
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

    foreach ($aanwezig as $lid)
    {
        $url_args = "ID=$lid";
        heliosInit("Leden/GetObject?" . $url_args);

        $result      = curl_exec($curl_session);
        $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
        list($header, $body) = returnHeaderBody($result);

        $lidData = json_decode($body, true);
        // email 
        $mail = emailInit();
        
        switch ($lidData['LIDTYPE_ID'])
        {
            case 600: $mail->addAddress($penningmeester['EMAIL'], $penningmeester['NAAM']); break; // Diverse (Bijvoorbeeld bedrijven- of jongerendag)
            case 601: $mail->addAddress($lidData['EMAIL'], $lidData['NAAM']);               break; // Erelid
            case 602: $mail->addAddress($lidData['EMAIL'], $lidData['NAAM']);               break; // Lid
            case 603: $mail->addAddress($lidData['EMAIL'], $lidData['NAAM']);               break; // Jeugdlid
            case 606: $mail->addAddress($lidData['EMAIL'], $lidData['NAAM']);               break; // Donateur
            case 607: $mail->addAddress($lidData['EMAIL'], $lidData['NAAM']);                      // Zusterclub
                      $mail->addAddress($penningmeester['EMAIL'], $penningmeester['NAAM']); 
                      break; 
            case 608: $mail->addAddress($lidData['EMAIL'], $lidData['NAAM']);                      // 5-rittenkaarthouder
                      $mail->addAddress($penningmeester['EMAIL'], $penningmeester['NAAM']); 
                      break; 
            case 609: $mail->addAddress($startadmin['EMAIL'], $startadmin['NAAM']);  break; // Nieuw lid, nog niet verwerkt in ledenadministratie
            case 610: $mail->addAddress($penningmeester['EMAIL'], $penningmeester['NAAM']); break; // Oprotkabel
            case 611: $mail->addAddress($lidData['EMAIL'], $lidData['NAAM']);               break; // Cursist
            case 612: $mail->addAddress($penningmeester['EMAIL'], $penningmeester['NAAM']); break; // Penningmeester
            case 613: $mail->addAddress($penningmeester['EMAIL'], $penningmeester['NAAM']); break; // Systeem account
            case 625: $mail->addAddress($lidData['EMAIL'], $lidData['NAAM']);               break; // DDWV vlieger
        }

        $url_args = "LID_ID=$lid&BEGIN_DATUM=$datum&EIND_DATUM=$datum&SORT=starttijd";
        heliosInit("Startlijst/GetLogboek?" . $url_args);

        $result      = curl_exec($curl_session);
        $status_code = curl_getinfo($curl_session, CURLINFO_HTTP_CODE); //get status code
        list($header, $body) = returnHeaderBody($result);

        $vluchten = json_decode($body, true);

        $logboekRegels = "";
        foreach ($vluchten['dataset'] as $vlucht)
        {
            $d = explode("-", $vlucht['DATUM']);

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
                </tr>",
                $d[2]*1, $d[1]*1, $d[0],
                $vlucht['REG_CALL'],
                $vlucht['VELD'],
                $vlucht['STARTMETHODE'],
                $vlucht['VLIEGERNAAM'],
                $vlucht['INZITTENDENAAM'],
                $vlucht['STARTTIJD'],
                $vlucht['LANDINGSTIJD'],
                $vlucht['DUUR'],
                $vlucht['OPMERKINGEN']);
        }

        $body = sprintf($htmlContent, $lidData['VOORNAAM'], $datumString, $logboekRegels);
    
        $mail->Subject = 'Logboek ' . $datumString;
        $mail->isHTML(true);                                  		//Set email format to HTML
        $mail->Body    = $body;
    
        $mail->addReplyTo($smtp_settings['from'], $smtp_settings['name']);
        $mail->SetFrom($smtp_settings['from'], $smtp_settings['name']);

        if(!$mail->Send()) {
            print_r($mail);
        }
    }
}